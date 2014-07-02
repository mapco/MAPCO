<?php

	if( isset($_POST["id_job"]) and $_POST["id_job"]>0 )
	{
		$results=q("SELECT * FROM ebay_jobs WHERE id_job=".$_POST["id_job"].";", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)==0 )
		{
			echo '<ResponseEvaluateReviseItem>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Job nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Der angegebene Job konnte nicht gefunden werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<Response>'.$responseXml.'</Response>'."\n";
			echo '</ResponseEvaluateReviseItem>'."\n";
			exit;
		}
		$job=mysqli_fetch_array($results);

		//download response file
		$fieldset=array();
		$fieldset["API"]="ebay_lms";
		$fieldset["Action"]="downloadFile";
		$fieldset["id_account"]=$job["account_id"];
		$fieldset["fileReferenceId"]=$job["fileReferenceId"];
		$fieldset["taskReferenceId"]=$job["jobId"];
	
		$responseXml = post(PATH."soa/", $fieldset);
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXml);
		}
		catch(Exception $e)
		{
			echo '<ResponseEvaluateReviseItem>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
			echo '		<longMsg>Beim Auswerten der XML-Daten trat ein Fehler auf.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<PostFields>'.print_r($_POST, true).'</PostFields>'."\n";
			echo '	<Response>'.$responseXml.'</Response>'."\n";
			echo '</ResponseEvaluateReviseItem>'."\n";
			exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		$filename=(string)$response->File[0];
		$_POST["XML"]=file_get_contents($filename);
	}
	
	if( !isset($_POST["XML"]) or $_POST["XML"]=="" )
	{
		echo '<ResponseEvaluateReviseItem>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>XML leer.</shortMsg>'."\n";
		echo '		<longMsg>Beim Auswerten der XML-Daten trat ein Fehler auf.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '	<PostFields>'.print_r($_POST, true).'</PostFields>'."\n";
		echo '	<Response>'.$responseXml.'</Response>'."\n";
		echo '</ResponseEvaluateReviseItem>'."\n";
		exit;
	}

	//single response fix
	if( strpos($_POST["XML"], "<BulkDataExchangeResponses") === false )
	{
		$_POST["XML"] = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '<?xml version="1.0" encoding="UTF-8"?><BulkDataExchangeResponses xmlns="urn:ebay:apis:eBLBaseComponents">', $_POST["XML"]);
		$_POST["XML"] .= '</BulkDataExchangeResponses>';
	}

	//evaluate XML
	try
	{
		$xml = new SimpleXMLElement($_POST["XML"]);
	}
	catch(Exception $e)
	{
			echo '<ResponseEvaluateReviseItem>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
			echo '		<longMsg>Beim Auswerten der XML-Daten trat ein Fehler auf.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<Response>'.$responseXml.'</Response>'."\n";
			echo '</ResponseEvaluateReviseItem>'."\n";
			exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
	$items=array();
	$errors=array();
	
	foreach($xml->ReviseItemResponse as $item)
	{
		$responseXml=$item->asXml();
		$id_auction=$item->CorrelationID[0]*1;
		
		if( $id_auction!="" and is_numeric($id_auction) )
		{
			$errors_found=false;
			foreach($item->Errors as $error)
			{
				//Revise item denied. - You are not the seller of this item. Only sellers allowed to modify the item information.
				if( $error->ErrorCode[0] == 290 )
				{
					$fieldset=array();
					$fieldset["API"]="ebay";
					$fieldset["Action"]="GetItem";
					$fieldset["id_account"]=1;
					$fieldset["id_auction"]=$id_auction;
					$fieldset["DetailLevel"]="ItemReturnAttributes";
					$response=post(PATH."soa/", $fieldset);
					
					$use_errors = libxml_use_internal_errors(true);
					try
					{
						$xml2 = new SimpleXMLElement($response);
					}
					catch(Exception $e)
					{
						echo '<ResponseEvaluateReviseItem>'."\n";
						echo '	<Ack>Failure</Ack>'."\n";
						echo '	<Error>'."\n";
						echo '		<Code>'.__LINE__.'</Code>'."\n";
						echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
						echo '		<longMsg>Beim Auswerten der XML-Daten trat ein Fehler auf.</longMsg>'."\n";
						echo '	</Error>'."\n";
						echo '	<PostFields>'.print_r($_POST, true).'</PostFields>'."\n";
						echo '	<Response>'.$responseXml.'</Response>'."\n";
						echo '</ResponseEvaluateReviseItem>'."\n";
						exit;
					}
					libxml_clear_errors();
					libxml_use_internal_errors($use_errors);

					$UserID=$xml2->Item[0]->Seller[0]->UserID[0];
					$results=q("SELECT * FROM ebay_accounts WHERE UserID='".$UserID."';", $dbshop, __FILE__, __LINE__);
					$row=mysqli_fetch_array($results);
					q("UPDATE ebay_auctions SET account_id=".$row["id_account"].", upload=1 WHERE id_auction=".$id_auction.";", $dbshop, __FILE__, __LINE__);
					$errors[]=$error->ErrorCode[0];
					$errors_found=true;
					//force immediate ItemCreateAuctions
					$results=q("SELECT shopitem_id FROM ebay_auctions WHERE id_auction=".$id_auction.";", $dbshop, __FILE__, __LINE__);
					if( mysqli_num_rows($results)>0 )
					{
						$row=mysqli_fetch_array($results);
						$id_item=$row["shopitem_id"];
						q("UPDATE shop_items SET lastupdate=0 WHERE id_item=".$id_item.";", $dbshop, __FILE__, __LINE__);
					}
				}
				//Auction ended. - You are not allowed to revise ended listings.
				elseif( $error->ErrorCode[0] == 291 )
				{
					//force immediate ItemCreateAuctions
					$results=q("SELECT shopitem_id FROM ebay_auctions WHERE id_auction=".$id_auction.";", $dbshop, __FILE__, __LINE__);
					if( mysqli_num_rows($results)>0 )
					{
						$row=mysqli_fetch_array($results);
						$id_item=$row["shopitem_id"];
						q("UPDATE shop_items SET lastupdate=0 WHERE id_item=".$id_item.";", $dbshop, __FILE__, __LINE__);
					}
					//remove auction from ebay_auctions
					q("DELETE FROM ebay_auctions WHERE id_auction=".$id_auction.";", $dbshop, __FILE__, __LINE__);
					$errors[]=$error->ErrorCode[0];
					$errors_found=true;
				}
			}
			if( !$errors_found )
			{
				q("	UPDATE ebay_auctions
					SET responseXml='".mysqli_real_escape_string($dbshop, $responseXml)."'
					WHERE id_auction=".$id_auction.";", $dbshop, __FILE__, __LINE__);
				$items[]=$id_auction;
			}
		}
		else
		{
			q("	UPDATE ebay_auctions
				SET responseXml='".mysqli_real_escape_string($dbshop, $responseXml)."'
				WHERE id_auction=".$id_auction.";", $dbshop, __FILE__, __LINE__);
			$errors[]=$id_auction;
		}
	}
	
	if( isset($job) )
	{
		q("UPDATE ebay_jobs SET evaluated=1 WHERE id_job=".$job["id_job"].";", $dbshop, __FILE__, __LINE__);
	}

	echo '<ResponseEvaluateReviseItem>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	if( isset($job) ) echo '	<JobID>'.$job["id_job"].'</JobID>'."\n";
	for($i=0; $i<sizeof($errors); $i++)
	{
		echo '	<Error ErrorCode="'.$errors[$i].'">'.$items[$i].'</Error>'."\n";
	}
	for($i=0; $i<sizeof($items); $i++)
	{
		echo '	<ReviseItem>'.$items[$i].'</ReviseItem>'."\n";
	}
	echo '</ResponseEvaluateReviseItem>'."\n";
?>