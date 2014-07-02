<?php

	if( isset($_POST["id_job"]) and $_POST["id_job"]>0 )
	{
		$results=q("SELECT * FROM ebay_jobs WHERE id_job=".$_POST["id_job"].";", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)==0 )
		{
			echo '<ResponseEvaluateActiveInventoryReportResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Job nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Der angegebene Job konnte nicht gefunden werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<Response>'.$responseXml.'</Response>'."\n";
			echo '</ResponseEvaluateActiveInventoryReportResponse>'."\n";
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
			echo '<ResponseEvaluateActiveInventoryReportResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
			echo '		<longMsg>Beim Auswerten der XML-Daten trat ein Fehler auf.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<PostFields>'.print_r($_POST, true).'</PostFields>'."\n";
			echo '	<Response>'.$responseXml.'</Response>'."\n";
			echo '</ResponseEvaluateActiveInventoryReportResponse>'."\n";
			exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		$filename=(string)$response->File[0];
		$_POST["XML"]=file_get_contents($filename);
	}
	
	if( !isset($_POST["XML"]) or $_POST["XML"]=="" )
	{
		echo '<ResponseEvaluateActiveInventoryReportResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>XML leer.</shortMsg>'."\n";
		echo '		<longMsg>Beim Auswerten der XML-Daten trat ein Fehler auf.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '	<PostFields>'.print_r($_POST, true).'</PostFields>'."\n";
		echo '	<Response>'.$responseXml.'</Response>'."\n";
		echo '</ResponseEvaluateActiveInventoryReportResponse>'."\n";
		exit;
	}

	//evaluate XML
	try
	{
		$xml = new SimpleXMLElement($_POST["XML"]);
	}
	catch(Exception $e)
	{
			echo '<ResponseEvaluateActiveInventoryReportResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
			echo '		<longMsg>Beim Auswerten der XML-Daten trat ein Fehler auf.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<PostFields>'.print_r($_POST, true).'</PostFields>'."\n";
			echo '	<Response>'.$responseXml.'</Response>'."\n";
			echo '</ResponseEvaluateActiveInventoryReportResponse>'."\n";
			exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
	
	//remember new items for return values
	$new=array();

	$max=0;
	$eof=1;
	while( $max!=$eof )
	{
		//get a chunk of the inventory report
		$auction=array();
		$ItemID=array();
		$SKU=array();
		$eof=sizeof($xml->ActiveInventoryReport[0]->SKUDetails);
		$chunk=1000;
		if( ($job["processed_lines"]+$chunk)<$eof ) $max=$job["processed_lines"]+$chunk; else $max=$eof;
		for($i=$job["processed_lines"]; $i<$max; $i++)
		{
			$item=$xml->ActiveInventoryReport[0]->SKUDetails[$i*1];
			$ItemID[]=(int)$item->ItemID[0];
			$SKU[]=(int)$item->SKU[0];
		}
	
		//get known auctions from database
		$auction=array();
	//	$results=q("SELECT ItemID FROM ebay_auctions WHERE account_id=".$job["account_id"].";", $dbshop, __FILE__, __LINE__);
		$results=q("SELECT ItemID FROM ebay_auctions WHERE ItemID IN (".implode(", ", $ItemID).");", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			$auction[$row["ItemID"]]=0;
		}
	
		//compare chunk with database
		for($i=0; $i<sizeof($ItemID); $i++)
		{
			if( !isset($auction[$ItemID[$i]]) )
			{
				echo $ItemID[$i]."\n";
	
				$results=q("SELECT id_item FROM shop_items WHERE MPN='".$SKU[$i]."';", $dbshop, __FILE__, __LINE__);
				if( mysqli_num_rows($results)>0 )
				{
					$row=mysqli_fetch_array($results);
					$data=array();
					$data["shopitem_id"]=$row["id_item"];
					$data["account_id"]=$job["account_id"];
					$data["ItemID"]=$ItemID[$i];
					$data["SKU"]=$SKU[$i];
					q_insert("ebay_auctions", $data, $dbshop, __FILE__, __LINE__);
					$new[]=$ItemID;
	
					//force immediate ItemCreateAuctions
					$data=array();
					$data["lastupdate"]=0;
					q_update("shop_items", $data, "WHERE id_item=".$row["id_item"], $dbshop, __FILE__, __LINE__);
				}
	
			}
		}
		//update ebay_jobs
		$data=array();
		$data["processed_lines"]=$max;
		q_update("ebay_jobs", $data, "WHERE id_job=".$_POST["id_job"].";", $dbshop, __FILE__, __LINE__);
		$job["processed_lines"]+=$chunk;
	}//end of while

	//set evaluated when end of file
	if ( isset($job) )
	{
		q("UPDATE ebay_jobs SET evaluated=1 WHERE id_job=".$job["id_job"].";", $dbshop, __FILE__, __LINE__);
	}

	//return success
	echo '<ResponseEvaluateActiveInventoryReportResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<JobID>'.$job["id_job"].'</JobID>'."\n";
	for($i=0; $i<sizeof($new); $i++)
	{
		echo '	<ItemID>'.$new[$i].'</ItemID>'."\n";
	}
	echo '</ResponseEvaluateActiveInventoryReportResponse>'."\n";

?>