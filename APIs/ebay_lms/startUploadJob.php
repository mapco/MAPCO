<?php

	if ( !isset($_POST["id_accountsite"]) )
	{
		echo '<startUploadJobResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Accountsite-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Accountsite-ID  (id_accountsite) übermittelt werden, damit der Service weiß, zu welchem Account der Aufruf gehört.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</startUploadJobResponse>'."\n";
		exit;
	}
	$results=q("SELECT * FROM ebay_accounts_sites WHERE id_accountsite=".$_POST["id_accountsite"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<startUploadJobResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Ebay-Accountseite nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der angegebene Ebay-Accountseite konnte nicht gefunden werden. Die Accountsite-ID scheint es nicht zu geben.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</startUploadJobResponse>'."\n";
		exit;
	}
	$accountsite=mysqli_fetch_array($results);

	$results=q("SELECT * FROM ebay_accounts WHERE id_account=".$accountsite["account_id"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<startUploadJobResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Ebay-Account nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der angegebene Ebay-Account konnte nicht gefunden werden. Die Account-ID scheint es nicht zu geben.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</startUploadJobResponse>'."\n";
		exit;
	}
	$account=mysqli_fetch_array($results);

	if ( !isset($_POST["JobType"]) )
	{
		echo '<startUploadJobResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Jobtyp nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Jobtyp (JobType) übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</startUploadJobResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["Filename"]) )
	{
		echo '<startUploadJobResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Datei nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Datei (Filename) übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</startUploadJobResponse>'."\n";
		exit;
	}


	//create job
	q("INSERT INTO ebay_jobs (JobType, account_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".$_POST["JobType"]."', ".$account["id_account"].", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
	$id_job=mysqli_insert_id($dbshop);

	//request needs to be without <?xml version="1.0" encoding="utf-8"
	$requestXmlBody='
		<createUploadJobRequest xmlns="http://www.ebay.com/marketplace/services">
		  <fileType>zip</fileType>
		  <uploadJobType>'.$_POST["JobType"].'</uploadJobType>
		  <UUID>'.$id_job.'</UUID>
		</createUploadJobRequest>
	';

	$responseXml = post(PATH."soa/", array("API" => "ebay_lms", "Action" => "BulkDataExchangeSubmit", "Call" => "createUploadJob", "id_account" => $account["id_account"], "request" => $requestXmlBody));
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXml);
	}
	catch(Exception $e)
	{
		q("DELETE FROM ebay_jobs WHERE id_job=".$id_job.";", $dbshop, __FILE__, __LINE__);
		echo '<startUploadJobResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
		echo '		<longMsg>Beim Auswerten der XML-Daten trat ein Fehler auf.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
		echo '</startUploadJobResponse>'."\n";
		exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
	$ack=(string)$response->ack[0];
	$timestamp=(string)$response->timestamp[0];
	$jobId=(string)$response->jobId[0];
	$fileReferenceId=(string)$response->fileReferenceId[0];
	if( $jobId=="" )
	{
		q("DELETE FROM ebay_jobs WHERE id_job=".$id_job.";", $dbshop, __FILE__, __LINE__);

		$errorId=(integer)$response->errorMessage[0]->error[0]->errorId[0];
		//Maximum of one job per job-type in non-terminated state is allowed
		if( $errorId!=7 )
		{
			echo '<startUploadJobResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>jobId fehlt.</shortMsg>'."\n";
			echo '		<longMsg>jobId konnte nicht gefunden werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<Request><![CDATA['.$requestXmlBody.']]></Request>'."\n";
			echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
			echo '	<AccountId><![CDATA['.$account["id_account"].']]></AccountId>'."\n";
			echo '	<AccountSiteId><![CDATA['.$accountsite["id_accountsite"].']]></AccountSiteId>'."\n";
			echo '</startUploadJobResponse>'."\n";
			exit;
		}

		//update ebay_jobs
		post(PATH."soa/", array("API" => "ebay_lms", "Action" => "getJobs", "id_account" => $account["id_account"]));
	
		//is there a job of this type in Created state?
		$results=q("SELECT * FROM ebay_jobs WHERE account_id=".$account["id_account"]." AND JobType='".$_POST["JobType"]."' AND jobStatus='Created';", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)==0 )
		{
			echo '<startUploadJobResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>jobId fehlt.</shortMsg>'."\n";
			echo '		<longMsg>jobId konnte nicht gefunden werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<Request><![CDATA['.$requestXmlBody.']]></Request>'."\n";
			echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
			echo '</startUploadJobResponse>'."\n";
			exit;
		}
		$row=mysqli_fetch_array($results);
		$jobId=$row["jobId"];
		$fileReferenceId=$row["inputFileReferenceId"];
		$timestamp=$row["creationTime"];

/*
		$requestXmlBody='
			<startUploadJobRequest xmlns="http://www.ebay.com/marketplace/services">
			  <jobId>'.$row["jobId"].'</jobId>
			</startUploadJobRequest>';
		$responseXml = post(PATH."soa/", array("API" => "ebay_lms", "Action" => "BulkDataExchangeSubmit", "Call" => "startUploadJob", "id_account" => $account["id_account"], "request" => $requestXmlBody));
		if( strpos($responseXml, "<ack>Success</ack>") !== false )
		{
			echo '<startUploadJobResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>JobType already created.</shortMsg>'."\n";
			echo '		<longMsg>A job with this Jobtype ('.$_POST["JobType"].') has already been created and has been started now. There can be only one job of this type at a time.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
			echo '</startUploadJobResponse>'."\n";
			exit;
		}
		else
		{
			//abort job if it cant be started
			$responseXml = post(PATH."soa/", array("API" => "ebay_lms", "Action" => "abortJob", "id_job" => $row["id_job"]));
			echo '<startUploadJobResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Job aborted.</shortMsg>'."\n";
			echo '		<longMsg>Already running job of this JobType has been aborted.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</startUploadJobResponse>'."\n";
			exit;
		}
	
		$results=q("SELECT * FROM ebay_jobs WHERE account_id=".$account["id_account"]." AND JobType='".$_POST["JobType"]."' AND (jobStatus='Scheduled' OR jobStatus='InProcess');", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)>0 )
		{
			$row=mysqli_fetch_array($results);
			echo '<startUploadJobResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>JobType already running.</shortMsg>'."\n";
			echo '		<longMsg>A job with this Jobtype ('.$_POST["JobType"].') is already running. There can be only one job of this type at a time.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</startUploadJobResponse>'."\n";
			exit;
		}
*/
	} //if( $jobId=="" )
	else
	{
		q("	UPDATE ebay_jobs
			SET jobId=".$jobId.",
				jobStatus='Send',
				inputFileReferenceId=".$fileReferenceId.",
				creationTime='".$timestamp."'
			WHERE id_job=".$id_job.";", $dbshop, __FILE__, __LINE__);
	}

	//upload data to ebay
	$Data=file_get_contents($_POST["Filename"]);
	$Data=base64_encode($Data);
	$Size=strlen($Data);
	$requestXmlBody='
		<uploadFileRequest xmlns="http://www.ebay.com/marketplace/services">
		  <fileAttachment>
			<Data>'.$Data.'</Data>
			<Size>'.$Size.'</Size>
		  </fileAttachment>
		  <fileFormat>zip</fileFormat>
		  <fileReferenceId>'.$fileReferenceId.'</fileReferenceId>
		  <taskReferenceId>'.$jobId.'</taskReferenceId>
		</uploadFileRequest>	
	';

	$responseXml = post(PATH."soa/", array("API" => "ebay_lms", "Action" => "FileTransferSubmit", "Call" => "uploadFile", "id_account" => $account["id_account"], "request" => $requestXmlBody));
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXml);
	}
	catch(Exception $e)
	{
		echo '<startUploadJobResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>uploadFileRequest ist fehlgeschlagen.</shortMsg>'."\n";
		echo '		<longMsg>Beim Aufrufen von uploadFileRequest trat ein Fehler auf.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '	<Request><![CDATA['.$requestXmlBody.']]></Request>'."\n";
		echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
		echo '</startUploadJobResponse>'."\n";
		exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);

	//abort if job already had a payload as we do not know if the payload is ok
	$errorId=(integer)$response->errorMessage[0]->error[0]->errorId[0];
	if( $errorId==13 )
	{
		$responseXml = post(PATH."soa/", array("API" => "ebay_lms", "Action" => "abortJob", "id_job" => $row["id_job"]));
		echo '<startUploadJobResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Job aborted.</shortMsg>'."\n";
		echo '		<longMsg>Already running job of this JobType has been aborted.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</startUploadJobResponse>'."\n";
		q("UPDATE ebay_jobs SET JobStatus='Aborted' WHERE jobId=".$jobId.";", $dbshop, __FILE__, __LINE__);
		exit;
	}
	$ack=(string)$response->ack[0];	
	if( $ack!="Success" )
	{
		echo '<startUploadJobResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Payload-Datei hochladen fehlgeschlagen.</shortMsg>'."\n";
		echo '		<longMsg>Beim Hochladen der Payload-Datei trat ein Fehler auf.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
		echo '	<Request><![CDATA['.$requestXmlBody.']]></Request>'."\n";
		echo '</startUploadJobResponse>'."\n";
		exit;
	}

	//FINALLY START THE JOB !!!
	$requestXmlBody='
		<startUploadJobRequest xmlns="http://www.ebay.com/marketplace/services">
		  <jobId>'.$jobId.'</jobId>
		</startUploadJobRequest>	';

	$responseXml = post(PATH."soa/", array("API" => "ebay_lms", "Action" => "BulkDataExchangeSubmit", "Call" => "startUploadJob", "id_account" => $account["id_account"], "request" => $requestXmlBody));
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXml);
	}
	catch(Exception $e)
	{
		echo '<startUploadJobResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
		echo '		<longMsg>Beim Auswerten der XML-Daten trat ein Fehler auf.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</startUploadJobResponse>'."\n";
		exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
	$ack=(string)$response->ack[0];
	if( $ack!="Success" )
	{
		echo '<startUploadJobResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Job starten fehlgeschlagen.</shortMsg>'."\n";
		echo '		<longMsg>Beim Starten des Jobs trat ein Fehler auf.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '	<Request><![CDATA['.$requestXmlBody.']]></Request>'."\n";
		echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
		echo '</startUploadJobResponse>'."\n";
		exit;
	}

	echo '<startUploadJobResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
	echo '</startUploadJobResponse>'."\n";
	exit;
?>