<?php
	if ( !isset($_POST["orderxml"]) )
	{
		echo '<Auftragsantwort>';
		echo '	<Status>Fehler</Status>';
		echo '	<StatusNr>17</StatusNr>';
		echo '	<Statustext>Orderxml nicht gefunden. Der Auftrag muss als XML-String übergeben werden.</Statustext>';
		echo '</Auftragsantwort>';
		exit;
	}
	

	//read order
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$xml = new SimpleXMLElement($_POST["orderxml"]);
	}
	catch(Exception $e)
	{
		echo '<Auftragsantwort>'."\n";
		echo '	<Status>Fehler</Status>'."\n";
		echo '	<StatusNr>0</StatusNr>'."\n";
		echo '	<Statustext>XML ungültig.</Statustext>'."\n";
		echo '</Auftragsantwort>';
		exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);


	if ( !isset($xml->Rechnungsanschrift[0]->KUN_NR[0]) )
	{
		echo '<Auftragsantwort>'."\n";
		echo '	<Status>Fehler</Status>'."\n";
		echo '	<StatusNr>10</StatusNr>'."\n";
		echo '	<Statustext>KUN_NR nicht gefunden.</Statustext>'."\n";
		echo '</Auftragsantwort>';
		exit;
	}
	
	if ( $xml->Rechnungsanschrift[0]->KUN_NR[0]=="" )
	{
		echo '<Auftragsantwort>'."\n";
		echo '	<Status>Fehler</Status>'."\n";
		echo '	<StatusNr>10</StatusNr>'."\n";
		echo '	<Statustext>KUN_NR darf nicht leer sein.</Statustext>'."\n";
		echo '</Auftragsantwort>';
		exit;
	}
	
	echo '<Auftragsantwort>'."\n";
	echo '	<Status>Erfolg</Status>'."\n";
	echo '	<StatusNr>1</StatusNr>'."\n";
	echo '	<AufID>123456789</AufID>'."\n";
	echo '	<Statustext>Auftrag erfolgreich in IDIMS übernommen.</Statustext>'."\n";
	echo '</Auftragsantwort>';
	
?>