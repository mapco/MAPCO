<?php
	if( !isset($_POST["PrinterName"]) )
	{
		echo '<PrintPDFResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Druckername nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Druckername (PrinterName) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</PrintPDFResponse>'."\n";
		exit;
	}

	if( !isset($_POST["file"]) )
	{
		echo '<PrintPDFResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Dateipfad nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Pfad zu einer PDF-Datei (file) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</PrintPDFResponse>'."\n";
		exit;
	}
	
	$file=file_get_contents("../../mapco_shop_de/".$_POST["file"]);
	if( !$file )
	{
		echo '<PrintPDFResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Datei nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Die angegebene Datei konnte nicht gelesen werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</PrintPDFResponse>'."\n";
		exit;
	}

	echo post("http://217.91.57.179/", array("API" => "print", "Action" => "PrintPDF", "PrinterName" => $_POST["PrinterName"], "file" => $file));
	
?>