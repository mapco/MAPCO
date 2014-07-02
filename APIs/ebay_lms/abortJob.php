<?php

	if( !isset($_POST["id_job"]) and isset($_POST["jobId"]) )
	{
		$results=q("SELECT * FROM ebay_jobs WHERE JobId='".$_POST[""]."';", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)>0 )
		{
			$row=mysqli_fetch_array($results);
			$_POST["id_job"]=$row["id_job"];
		}
	}

	if ( !isset($_POST["id_job"]) )
	{
		echo '<abortJobResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Jobtyp nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Jobtyp (JobType) übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</abortJobResponse>'."\n";
		exit;
	}
	$results=q("SELECT * FROM ebay_jobs WHERE id_job='".$_POST["id_job"]."';", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<abortJobResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Job nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der angegebene Job ('.$_POST["id_job"].') konnte nicht gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</abortJobResponse>'."\n";
		exit;
	}
	$job=mysqli_fetch_array($results);

	if( $job["jobStatus"]!="Send" )
	{
		//request needs to be without <?xml version="1.0" encoding="utf-8"
		$requestXmlBody='
			<abortJobRequest xmlns="http://www.ebay.com/marketplace/services">
			  <jobId>'.$job["jobId"].'</jobId>
			</abortJobRequest>
		';
	
		$responseXml = post(PATH."soa/", array("API" => "ebay_lms", "Action" => "BulkDataExchangeSubmit", "Call" => "abortJob", "id_account" => $job["account_id"], "request" => $requestXmlBody));
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXml);
		}
		catch(Exception $e)
		{
			echo '<abortJobResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Bild hochladen fehlgeschlagen.</shortMsg>'."\n";
			echo '		<longMsg>Beim Hochladen eines Bildes ist ein Fehler aufgetreten.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</abortJobResponse>'."\n";
			exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		$ack=(string)$response->ack[0];
		if( $ack!="Success" )
		{
			echo '<abortJobResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Ausführung nicht erfolgreich.</shortMsg>'."\n";
			echo '		<longMsg>Der Job konnte nicht erfolgreich abgebrochen werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<Request><![CDATA['.$requestXmlBody.']]></Request>'."\n";
			echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
			echo '</abortJobResponse>'."\n";
			exit;
		}
	}

	q("UPDATE ebay_jobs SET jobStatus='Aborted' WHERE id_job='".$_POST["id_job"]."';", $dbshop, __FILE__, __LINE__);
	
	echo '<abortJobResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
	echo '</abortJobResponse>'."\n";
	exit;
?>