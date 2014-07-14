<?php
	$starttime=time()+microtime();
	$jobs=array();
	$log="";

	$Call=array("EndItem", "ReviseItem", "AddItem");
	$results=q("SELECT * FROM ebay_accounts_sites WHERE active>0;", $dbshop, __FILE__, __LINE__);
	while( $accountsite=mysqli_fetch_array($results) )
	{
		for($i=0; $i<sizeof($Call); $i++)
		{
			$payloadcount=0;
			//call only if limit is reached
			$skip=false;
			$skipreason="";
			if( $Call[$i]=="EndItem" ) $limit=10000; else $limit=400;
			$ebay_auctions_results=q("SELECT COUNT(id_auction) FROM ebay_auctions WHERE upload=1 AND accountsite_id=".$accountsite[$i]." AND `Call`='".$Call[$i]."';", $dbshop, __FILE__, __LINE__);
			$row=mysqli_fetch_array($ebay_auctions_results);
			$payloadcount=$row["COUNT(id_auction)"];
			if( $row["COUNT(id_auction)"]<$limit )
			{
				$skip=true;
				$skipreason=$row["COUNT(id_auction)"]." < ".$limit;
			}
			//call anyway if limit is not reached but auctions are older than one hour
			if( $row["COUNT(id_auction)"]>0 and $skip)
			{
				$ebay_auctions_results=q("SELECT lastmod FROM ebay_auctions WHERE upload=1 AND accountsite_id=".$accountsite[0]." AND `Call`='AddItem' ORDER BY lastmod;", $dbshop, __FILE__, __LINE__);
				$row=mysqli_fetch_array($ebay_auctions_results);
				if( $row["lastmod"] < (time()-3600) )
				{
					$skip=false;
					$skipreason="";
				}
			}
/*			
			echo $Call[$i];
			echo "\n";
			echo $row["COUNT(id_auction)"];
			echo "\n";
			echo $skip;
			echo "\n";
			echo $skipreason;
			echo "\n";
			exit;
*/
			$upload=array();
			if( !$skip )
			{
				//create payload
				$results2=q("SELECT id_auction FROM ebay_auctions WHERE accountsite_id=".$accountsite["id_accountsite"]." AND upload=1 AND `Call`='".$Call[$i]."' LIMIT ".$limit.";", $dbshop, __FILE__, __LINE__);
				if( mysqli_num_rows($results2)>0 )
				{
					//create data file
					$fieldset=array();
					$fieldset["API"]="cms";
					$fieldset["Action"]="TempFileAdd";
					$responseXml = post(PATH."soa/", $fieldset);
					$use_errors = libxml_use_internal_errors(true);
					try
					{
						$response = new SimpleXMLElement($responseXml);
					}
					catch(Exception $e)
					{
						echo '<EbayAuctionsUploadResponse>'."\n";
						echo '	<Ack>Failure</Ack>'."\n";
						echo '	<Error>'."\n";
						echo '		<Code>'.__LINE__.'</Code>'."\n";
						echo '		<shortMsg>Temporärdatei anlegen fehlgeschlagen.</shortMsg>'."\n";
						echo '		<longMsg>Beim Anlegen einer temporären Datei ist ein Fehler aufgetreten.</longMsg>'."\n";
						echo '	</Error>'."\n";
						echo '	<Response>'.$responseXml.'</Response>'."\n";
						echo '</EbayAuctionsUploadResponse>'."\n";
						exit;
					}
					libxml_clear_errors();
					libxml_use_internal_errors($use_errors);
					$filename1=(string)$response->Filename[0];
	
					$payload  = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
					$payload .= '<BulkDataExchangeRequests xmlns="http://www.ebay.com/marketplace/services">'."\n";
					$payload .= '	<Header>'."\n";
					$payload .= '		<SiteID>'.$accountsite["SiteID"].'</SiteID>'."\n";
					$payload .= '		<Version>'.$accountsite["Version"].'</Version>'."\n";
					$payload .= '	</Header>'."\n";
					file_put_contents($filename1, $payload);
				}
				$upload=array();
				while( $auction=mysqli_fetch_array($results2) )
				{
					$upload[]=$auction["id_auction"];
				}
	
				//submit payload
				if( sizeof($upload)>0 )
				{
					$fieldset["API"]="ebay";
					$fieldset["Action"]=$Call[$i];
					$fieldset["ReturnXml"]="true";
	//				$fieldset["id_accountsite"]=$accountsite["id_accountsite"];
					$fieldset["id_auction"]=implode(", ", $upload);
					$payload .= post(PATH."soa/", $fieldset);
					$payload .= '</BulkDataExchangeRequests>'."\n";
					file_put_contents($filename1, $payload);
	
					//create ZIP file
					$fieldset=array();
					$fieldset["API"]="cms";
					$fieldset["Action"]="TempFileAdd";
					$fieldset["extension"]="zip";
					$responseXml = post(PATH."soa/", $fieldset);
					$use_errors = libxml_use_internal_errors(true);
					try
					{
						$response = new SimpleXMLElement($responseXml);
					}
					catch(Exception $e)
					{
						echo '<EbayAuctionsUploadResponse>'."\n";
						echo '	<Ack>Failure</Ack>'."\n";
						echo '	<Error>'."\n";
						echo '		<Code>'.__LINE__.'</Code>'."\n";
						echo '		<shortMsg>ZIP-Datei anlegen fehlgeschlagen.</shortMsg>'."\n";
						echo '		<longMsg>Beim Anlegen der ZIP-Datei ist ein Fehler aufgetreten.</longMsg>'."\n";
						echo '	</Error>'."\n";
						echo '	<Response>'.$responseXml.'</Response>'."\n";
						echo '</EbayAuctionsUploadResponse>'."\n";
						exit;
					}
					libxml_clear_errors();
					libxml_use_internal_errors($use_errors);
					$filename2=(string)$response->Filename[0];
					
					//add data to ZIP file
					$fieldset=array();
					$fieldset["API"]="cms";
					$fieldset["Action"]="ZipFileAdd";
					$fieldset["zipfile"]=$filename2;
					$fieldset["file"]=substr($filename1, 3);
					$fieldset["filename"]="data.xml";
					$responseXml = post(PATH."soa/", $fieldset);
					$use_errors = libxml_use_internal_errors(true);
					try
					{
						$response = new SimpleXMLElement($responseXml);
					}
					catch(Exception $e)
					{
						echo '<EbayAuctionsUploadResponse>'."\n";
						echo '	<Ack>Failure</Ack>'."\n";
						echo '	<Error>'."\n";
						echo '		<Code>'.__LINE__.'</Code>'."\n";
						echo '		<shortMsg>Packen fehlgeschlagen.</shortMsg>'."\n";
						echo '		<longMsg>Die Datei konnte der ZIP-Datei nicht hinzugefügt werden.</longMsg>'."\n";
						echo '	</Error>'."\n";
						echo '	<Response>'.$responseXml.'</Response>'."\n";
						echo '</EbayAuctionsUploadResponse>'."\n";
						exit;
					}
					libxml_clear_errors();
					libxml_use_internal_errors($use_errors);
					$Ack=(string)$response->Ack[0];
					if( $Ack!="Success" )
					{
						echo '<EbayAuctionsUploadResponse>'."\n";
						echo '	<Ack>Failure</Ack>'."\n";
						echo '	<Error>'."\n";
						echo '		<Code>'.__LINE__.'</Code>'."\n";
						echo '		<shortMsg>Packen fehlgeschlagen.</shortMsg>'."\n";
						echo '		<longMsg>Die Datei konnte der ZIP-Datei nicht hinzugefügt werden.</longMsg>'."\n";
						echo '	</Error>'."\n";
						echo '	<Response>'.$responseXml.'</Response>'."\n";
						echo '</EbayAuctionsUploadResponse>'."\n";
						exit;
					}
	
					$fieldset=array();
					$fieldset["API"]="ebay_lms";
					$fieldset["Action"]="startUploadJob";
					$fieldset["JobType"]=$Call[$i];
					$fieldset["id_accountsite"]=$accountsite["id_accountsite"];
					$fieldset["Filename"]=$filename2;
					$responseXml = post(PATH."soa/", $fieldset);
					if( strpos($responseXml, "<ack>Success</ack>") !== false )
					{
						//uncheck upload flag
						q("UPDATE ebay_auctions SET upload=0, lastupdate=".time()." WHERE id_auction IN (".implode(", ", $upload).");", $dbshop, __FILE__, __LINE__);
						$jobs[]=array("id_accountsite" => $accountsite["id_accountsite"], "Call" => $Call[$i], "payload" => $payloadcount, "response" => "Success");
					}
					else
					{
						if( strpos($responseXml, "<Code>9896</Code>") !== false )
						{
							$jobs[]=array("id_accountsite" => $accountsite["id_accountsite"], "Call" => $Call[$i], "payload" => $payloadcount, "response" => "Skipped (Account-Limit)");
						}
						elseif( strpos($responseXml, "<Code>9897</Code>") !== false )
						{
							$jobs[]=array("id_accountsite" => $accountsite["id_accountsite"], "Call" => $Call[$i], "payload" => $payloadcount, "response" => "Skipped (Jobtyp-Limit)");
						}
						else
						{
							$jobs[]=array("id_accountsite" => $accountsite["id_accountsite"], "Call" => $Call[$i], "payload" => $payloadcount, "response" => "Skipped (Already Running?)");
						}
					}
				} // if( sizeof($upload)>0 )
				if( (time()-$starttime)>60 ) break;
			} // if ( !$skip )
			else $jobs[]=array("id_accountsite" => $accountsite["id_accountsite"], "Call" => $Call[$i], "payload" => $payloadcount, "response" => "Skipped (".$skipreason.")");
		} // for($i=0; $i<sizeof($Call); $i++)
		if( (time()-$starttime)>60 ) break;
	} // while( $accountsite=mysqli_fetch_array($results) )

	echo '<EbayAuctionsUpload>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo $log;
	for($i=0; $i<sizeof($jobs); $i++)
	{
		echo '	<Job id_accountsite="'.$jobs[$i]["id_accountsite"].'" payload="'.$jobs[$i]["payload"].'" Call="'.$jobs[$i]["Call"].'">'.$jobs[$i]["response"].'</Job>'."\n";
	}
//	echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
	echo '</EbayAuctionsUpload>'."\n";
	
?>