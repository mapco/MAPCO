<?php

	if ( !isset($_POST["id_account"]) )
	{
		echo '<downloadFileResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Account-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Account-ID übermittelt werden, damit der Service weiß, zu welchem Account der Aufruf gehört.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</downloadFileResponse>'."\n";
		exit;
	}

	$results=q("SELECT * FROM ebay_accounts WHERE id_account=".$_POST["id_account"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<downloadFileResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Ebay-Account nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Der angegebene Ebay-Account konnte nicht gefunden werden. Die Account-ID scheint es nicht zu geben.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</downloadFileResponse>'."\n";
		exit;
	}
	$account=mysqli_fetch_array($results);

	//request needs to be without <?xml version="1.0" encoding="utf-8"
	$requestXmlBody='
		<downloadFileRequest xmlns="http://www.ebay.com/marketplace/services">
		  <fileReferenceId>'.$_POST["fileReferenceId"].'</fileReferenceId>
		  <taskReferenceId>'.$_POST["taskReferenceId"].'</taskReferenceId>
		</downloadFileRequest>
	';

	$responseXml = post(PATH."soa/", array("API" => "ebay_lms", "Action" => "FileTransferSubmit", "Call" => "downloadFile", "id_account" => $_POST["id_account"], "request" => $requestXmlBody));
	$data=substr($responseXml, strpos($responseXml, "PK"));
	$data=substr($data, 0, strpos($data, "--MIMEBoundary"));

	//create data file
	$fieldset=array();
	$fieldset["API"]="cms";
	$fieldset["Action"]="TempFileAdd";
	$fieldset["extension"]="zip";
	$responseXml = post(PATH."soa/", $fieldset);
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
		echo '		<shortMsg>Temporärdatei anlegen fehlgeschlagen.</shortMsg>'."\n";
		echo '		<longMsg>Beim Anlegen einer temporären Datei ist ein Fehler aufgetreten.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '	<Response>'.$responseXml.'</Response>'."\n";
		echo '</startUploadJobResponse>'."\n";
		exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
	$zipfile=(string)$response->Filename[0];
	file_put_contents($zipfile, $data);

	//unzip file
	$zip = new ZipArchive;
	$res = $zip->open($zipfile);
	if ($res === TRUE)
	{
		$filename1='../temp/'.$zip->getNameIndex(0);
		$filename2=PATH.'temp/'.$zip->getNameIndex(0);
		$zip->extractTo('../temp/');
		$zip->close();
	}
	else
	{
		echo '<downloadFileResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Entpacken fehlgeschlagen.</shortMsg>'."\n";
		echo '		<longMsg>Beim Entpacken einer temporären Datei ist ein Fehler aufgetreten.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '	<Response>'.$responseXml.'</Response>'."\n";
		echo '</startUploadJobResponse>'."\n";
		exit;
	}

	echo '<downloadFileResponse>';	
	echo '	<Ack>Success</Ack>';	
	echo '	<File>'.$filename1.'</File>';	
	echo '	<Path>'.$filename2.'</Path>';	
	echo '</downloadFileResponse>';	

?>