<?php
	if ( !isset($_POST["id_account"]) )
	{
		echo '<CreateUploadJobRequest>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein gültiger Account (id_account) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CreateUploadJobRequest>'."\n";
		exit;
	}

	if ( !isset($_POST["uploadJobType"]) )
	{
		echo '<CreateUploadJobRequest>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Jobart nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine gültige Jobart (uploadJobType) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CreateUploadJobRequest>'."\n";
		exit;
	}

	if ( !isset($_POST["fileType"]) )
	{
		echo '<CreateUploadJobRequest>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Dateityp nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein gültiger Dateityp (fileType) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CreateUploadJobRequest>'."\n";
		exit;
	}

	//create ebay job
	q("INSERT INTO ebay_jobs (uploadJobType, fileType, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".mysqli_real_escape_string($dbshop, $_POST["uploadJobType"])."', ".$_POST["fileType"].", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbhop, __FILE__, __LINE__);
	$UUID=mysqli_insert_id($dbshop);

	//create job
	if ( $account["production"]==0 ) $token=$account["token_sandbox"]; else $token=$account["token"];
	$requestXmlBody='
		<?xml version='1.0' encoding="UTF-8"?>
		<createUploadJobRequest xmlns="http://www.ebay.com/marketplace/services">
		  <uploadJobType>'.$_POST["uploadJobType"].'</uploadJobType>
		  <UUID>'.$UUID.'</UUID>
		  <fileType>'.$_POST["fileType"].'</fileType>
		</createUploadJobRequest>
	';
	echo $responseXml = post(PATH."soa/", array("API" => "ebay", "Action" => "EbaySubmit", "Call" => "createUploadJob", "id_account" => $_POST["id_account"], "request" => $requestXmlBody));

?>