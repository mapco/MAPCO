<?php

	if ( !isset($_POST["id_account"]) )
	{
		echo '<getRecurringJobs>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Account (id_account) übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</getRecurringJobs>'."\n";
		exit;
	}
	$results=q("SELECT * FROM ebay_accounts WHERE id_account='".$_POST["id_account"]."';", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<getRecurringJobs>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der angegebene Account ('.$_POST["id_account"].') konnte nicht gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</getRecurringJobs>'."\n";
		exit;
	}
	$account=mysqli_fetch_array($results);

	//request needs to be without <?xml version="1.0" encoding="utf-8"
	$requestXmlBody='
		<getRecurringJobsRequest xmlns="http://www.ebay.com/marketplace/services">
		</getRecurringJobsRequest>
	';

	echo $responseXml = post(PATH."soa/", array("API" => "ebay_lms", "Action" => "BulkDataExchangeSubmit", "Call" => "getRecurringJobs", "id_account" => $_POST["id_account"], "request" => $requestXmlBody));

?>