<?php

	if ( !isset($_POST["id_job"]) )
	{
		echo '<getJobsResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Account-ID übermittelt werden, damit der Service weiß, zu welchem Account der Aufruf gehört.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</getJobsResponse>'."\n";
		exit;
	}
	$results=q("SELECT * FROM ebay_jobs WHERE id_job=".$_POST["id_job"].";", $dbshop, __FILE__, __LINE__);
	$job=mysqli_fetch_array($results);

	//request needs to be without <?xml version="1.0" encoding="utf-8"
	$requestXmlBody='
		<getJobStatusRequest xmlns="http://www.ebay.com/marketplace/services">
		  <jobId>'.$job["jobId"].'</jobId>
		</getJobStatusRequest>
	';

	$responseXml = post(PATH."soa/", array("API" => "ebay_lms", "Action" => "BulkDataExchangeSubmit", "Call" => "getJobStatus", "id_account" => $job["account_id"], "request" => $requestXmlBody));
	try
	{
		$response = new SimpleXMLElement($responseXml);
	}
	catch(Exception $e)
	{
		echo '<getJobStatusResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
		echo '		<longMsg>Beim Auswerten der XML-Daten trat ein Fehler auf.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</getJobStatusResponse>'."\n";
		exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);

	for($i=0; $i<count($response->jobProfile); $i++)
	{
		$jobId=(string)$response->jobProfile[$i]->jobId[0];
		$jobType=(string)$response->jobProfile[$i]->jobType[0];
		$jobStatus=(string)$response->jobProfile[$i]->jobStatus[0];
		$creationTime=(string)$response->jobProfile[$i]->creationTime[0];
		$completionTime=(string)$response->jobProfile[$i]->completionTime[0];
		$errorCount=(string)$response->jobProfile[$i]->errorCount[0];
		if( $errorCount=="" ) $errorCount=0;
		$percentComplete=(string)$response->jobProfile[$i]->percentComplete[0];
		$fileReferenceId=(string)$response->jobProfile[$i]->fileReferenceId[0];
		$inputFileReferenceId=(string)$response->jobProfile[$i]->inputFileReferenceId[0];
		if( $fileReferenceId=="" ) $fileReferenceId=0;
		if( $inputFileReferenceId=="" ) $inputFileReferenceId=0;
		
		$results=q("SELECT * FROM ebay_jobs WHERE jobId=".$jobId.";", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)==0 )
		{
			q("INSERT INTO ebay_jobs (account_id, jobId, jobType, jobStatus, creationTime, completionTime, errorCount, percentComplete, fileReferenceId, inputFileReferenceId, processed) VALUES(".$_POST["id_account"].", ".$jobId.", '".$jobType."', '".$jobStatus."', '".$creationTime."', '".$completionTime."', ".$errorCount.", '".$percentComplete."', ".$fileReferenceId.", ".$inputFileReferenceId.", 0);", $dbshop, __FILE__, __LINE__);
		}
		else
		{
			$row=mysqli_fetch_array($results);
			q("	UPDATE ebay_jobs
				SET account_id=".$job["account_id"].",
					jobId=".$jobId.",
					jobType='".$jobType."',
					jobStatus='".$jobStatus."',
					creationTime='".$creationTime."',
					completionTime='".$completionTime."',
					errorCount=".$errorCount.",
					percentComplete='".$percentComplete."',
					fileReferenceId=".$fileReferenceId.",
					inputFileReferenceId=".$inputFileReferenceId."						
				WHERE id_job=".$row["id_job"].";", $dbshop, __FILE__, __LINE__);
		}
	}

	//return success
	echo '<getJobStatusResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
	echo '</getJobStatusResponse>'."\n";
?>