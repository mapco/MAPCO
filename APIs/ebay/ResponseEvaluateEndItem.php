<?php

	if( isset($_POST["id_job"]) and $_POST["id_job"]>0 )
	{
		$results=q("SELECT * FROM ebay_jobs WHERE id_job=".$_POST["id_job"].";", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)==0 )
		{
			echo '<ResponseEvaluateAddItem>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Job nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Der angegebene Job konnte nicht gefunden werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<Response>'.$responseXml.'</Response>'."\n";
			echo '</ResponseEvaluateAddItem>'."\n";
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
			echo '<ResponseEvaluateAddItem>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
			echo '		<longMsg>Beim Auswerten der XML-Daten trat ein Fehler auf.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<PostFields>'.print_r($_POST, true).'</PostFields>'."\n";
			echo '	<Response>'.$responseXml.'</Response>'."\n";
			echo '</ResponseEvaluateAddItem>'."\n";
			exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		$filename=(string)$response->File[0];
		$_POST["XML"]=file_get_contents($filename);
	}
	
	if( !isset($_POST["XML"]) or $_POST["XML"]=="" )
	{
		echo '<ResponseEvaluateAddItem>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>XML leer.</shortMsg>'."\n";
		echo '		<longMsg>Beim Auswerten der XML-Daten trat ein Fehler auf.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '	<PostFields>'.print_r($_POST, true).'</PostFields>'."\n";
		echo '	<Response>'.$responseXml.'</Response>'."\n";
		echo '</ResponseEvaluateAddItem>'."\n";
		exit;
	}

	//evaluate XML
	try
	{
		$xml = new SimpleXMLElement($_POST["XML"]);
	}
	catch(Exception $e)
	{
			echo '<ResponseEvaluateEndItem>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
			echo '		<longMsg>Beim Auswerten der XML-Daten trat ein Fehler auf.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<Response>'.$responseXml.'</Response>'."\n";
			echo '</ResponseEvaluateEndItem>'."\n";
			exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
	$removed=array();
	$errors=array();
	foreach($xml->EndItemResponse as $item)
	{
		$responseXml=$item->asXml();
		$id_auction=$item->CorrelationID[0];
		$ItemID=$item->ItemID[0];
		$Ack=$item->Ack[0];
		if( isset($item->Errors[0]->ErrorCode[0]) ) $ErrorCode=$item->Errors[0]->ErrorCode[0];
		if( $Ack=="Success" or $ErrorCode==1047 )
		{
			$removed[]=$id_auction;
		}
		else
		{
			q("	UPDATE ebay_auctions
				SET responseXml='".mysqli_real_escape_string($dbshop, $responseXml)."'
				WHERE id_auction=".$id_auction.";", $dbshop, __FILE__, __LINE__);
			$errors[]=$id_auction;
		}
	}
	
	//remove succesfully ended auctions
	if( sizeof($removed)>0 )
	{
		q("DELETE FROM ebay_auctions WHERE id_auction IN (".implode(", ", $removed).");", $dbshop, __FILE__, __LINE__);
	}
	
	
	if( isset($job) )
	{
		q("UPDATE ebay_jobs SET evaluated=1 WHERE id_job=".$job["id_job"].";", $dbshop, __FILE__, __LINE__);
	}

	echo '<ResponseEvaluateEndItem>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<JobID>'.$job["id_job"].'</JobID>'."\n";
	for($i=0; $i<sizeof($errors); $i++)
	{
		echo '	<Error>'.$errors[$i].'</Error>'."\n";
	}
	for($i=0; $i<sizeof($removed); $i++)
	{
		echo '	<EndItem>'.$removed[$i].'</EndItem>'."\n";
	}
	echo '</ResponseEvaluateEndItem>'."\n";
?>