<?php

	$responseXml=post(PATH."soa/", array("API" => "shop", "Action" => "ListGet", "id_list" => $_POST["id_list"] ) );
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXml);
	}
	catch(Exception $e)
	{
		echo '<ItemsExportResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>XML-Fehler.</shortMsg>'."\n";
		echo '		<longMsg>XML-Fehler beim Abrufen der Listendaten.</longMsg>'."\n";
		echo '		<ResponseXml><![CDATA['.$responseXml.']]></ResponseXml>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemsExportResponse>'."\n";
		exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);

	//csv options
	$separator=";";
	
	//create file and header
	$handle=fopen($_POST["file"], "w");
	for($i=0; $i<sizeof($response->Header[0]->ColumnName); $i++)
	{
		$line .= '"'.$response->Header[0]->ColumnName[$i].'"';
		if ( ($i+1)!=sizeof($name) ) $line .= $separator;
	}
	fwrite($handle, $line."\n");

	//build line
	$line="";
	for($i=0; $i<sizeof($response->Item); $i++)
	{
		$j=0;
		$line="";
		foreach($response->Item[$i] as $value)
		{
			$value=(string)$value;
			if (is_numeric($value)) $value=str_replace(".", ",", $value);
			$line.='"'.$value.'"';
			if ( $j<sizeof($response->Item[$i]) ) $line .= $separator;
			$j++;
		}
		fwrite($handle, $line."\n");
	}
	fclose($handle);

	//return success
	echo '<ItemsExportResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</ItemsExportResponse>'."\n";

?>