<?php

	if ( !isset($_POST["id_account"]) or $_POST["id_account"]=="" )
	{
		echo '<BulkDataExchangeSubmitResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Account-ID übermittelt werden, damit der Service weiß, zu welchem Account der Aufruf gehört.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</BulkDataExchangeSubmitResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["id_job"]) or $_POST["id_job"]=="" )
	{
		echo '<deleteRecurringJob>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Jobtyp nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Jobtyp (JobType) übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</deleteRecurringJob>'."\n";
		exit;
	}

	//request needs to be without <?xml version="1.0" encoding="utf-8"
	$requestXmlBody='
		<deleteRecurringJobRequest xmlns="http://www.ebay.com/marketplace/services">
		  <recurringJobId>'.$_POST["id_job"].'</recurringJobId>
		</deleteRecurringJobRequest>
	';

	echo $responseXml = post(PATH."soa/", array("API" => "ebay_lms", "Action" => "BulkDataExchangeSubmit", "Call" => "deleteRecurringJob", "id_account" => $_POST["id_account"], "request" => $requestXmlBody));

?>