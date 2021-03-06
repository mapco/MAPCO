<?php

	if ( !isset($_POST["id_account"]) )
	{
		echo '<createRecurringJobResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Account-ID  (id_account) übermittelt werden, damit der Service weiß, zu welchem Account der Aufruf gehört.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</createRecurringJobResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["JobType"]) )
	{
		echo '<createRecurringJobResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Jobtyp nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Jobtyp (JobType) übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</createRecurringJobResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["frequencyInMinutes"]) )
	{
		echo '<createRecurringJobResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Frequenz nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Frequenz (frequencyInMinutes) übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</createRecurringJobResponse>'."\n";
		exit;
	}

	$results=q("SELECT * FROM ebay_accounts WHERE id_account=".$_POST["id_account"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<createRecurringJobResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Ebay-Account nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der angegebene Ebay-Account konnte nicht gefunden werden. Die Account-ID scheint es nicht zu geben.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</createRecurringJobResponse>'."\n";
		exit;
	}
	$account=mysqli_fetch_array($results);

	//create job
//	q("INSERT INTO ebay_jobs (JobType, account_id, jobType, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$_POST["id_account"].", '".$_POST["Call"]."', 'Started', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
	$jobId=mysqli_insert_id($dbshop);

	//request needs to be without <?xml version="1.0" encoding="utf-8"
	$requestXmlBody='
	<createRecurringJobRequest xmlns="http://www.ebay.com/marketplace/services">
	   <downloadJobType>'.$_POST["JobType"].'</downloadJobType>
	   <UUID>'.$jobId.'</UUID>
	   <frequencyInMinutes>'.$_POST["frequencyInMinutes"].'</frequencyInMinutes>
	</createRecurringJobRequest>
	';

	$responseXml = post(PATH."soa/", array("API" => "ebay_lms", "Action" => "BulkDataExchangeSubmit", "Call" => "createRecurringJob", "id_account" => $_POST["id_account"], "request" => $requestXmlBody));
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXml);
	}
	catch(Exception $e)
	{
		echo '<createRecurringJobResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bild hochladen fehlgeschlagen.</shortMsg>'."\n";
		echo '		<longMsg>Beim Hochladen eines Bildes ist ein Fehler aufgetreten.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</createRecurringJobResponse>'."\n";
		exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
	$ack=(string)$response->ack[0];
	$timestamp=(string)$response->timestamp[0];
	$jobId=(string)$response->recurringJobId[0];
	if( $jobId=="" )
	{
		echo '<createRecurringJobResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>jobId fehlt.</shortMsg>'."\n";
		echo '		<longMsg>jobId konnte nicht gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
		echo '</createRecurringJobResponse>'."\n";
		exit;
	}
	
//	q("UPDATE ebay_jobs SET ack='".$ack."', timestamp='".$timestamp."' WHERE id_job=".$jobId.";", $dbshop, __FILE__, __LINE__);
	
	echo '<createRecurringJobResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
	echo '</createRecurringJobResponse>'."\n";
	exit;
?>