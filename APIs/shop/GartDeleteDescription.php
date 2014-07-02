<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["GART"]) )
	{
		echo '<GartDeleteDescription>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>generische Artikelgruppe nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine generische Artikelgruppe ausgewählt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartDeleteDescription>'."\n";
		exit;
	}

	if ( !isset($_POST["lang_code"]) || (isset($_POST["lang_code"]) && $_POST["lang_code"]=="") )
	{
		echo '<GartDeleteDescription>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Sprache (Code) nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Sprache ausgewählt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartDeleteDescription>'."\n";
		exit;
	}
	
	$res=q("DELETE FROM shop_items_descriptions WHERE GART= '".$_POST["GART"]."' AND language_id = '".$_POST["lang_code"]."';", $dbshop, __FILE__, __LINE__);
	
	$error=mysqli_error($dbshop);
	if ($error!="")
	{
		echo '<GartDeleteDescription>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Fehler beim Löschen der Artikelbeschreibung</shortMsg>'."\n";
		echo '		<longMsg>MySQL-Fehlermeldung: '.$error.'</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartDeleteDescription>'."\n";
		exit;
	}

	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;

	if (mysqli_affected_rows()==0)
	{
		echo '<GartDeleteDescription>'."\n";
		echo '	<Ack>Warning</Ack>'."\n";
		echo '		<Warning>'."\n";
		echo '		<shortMsg>Artikelbeschreibung wurde nicht gelöscht</shortMsg>'."\n";
		echo '		<longMsg>Zur Gart '.$_POST["GART"].' und dem SprachCode '.$_POST["lang_code"].' wurde keine Artikelbeschreibung gefunden</longMsg>'."\n";
		echo '		</Warning>'."\n";
		echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
		echo '</GartDeleteDescription>'."\n";

	}

	else 
	{
		echo '<GartDeleteDescription>'."\n";
		echo '	<Ack>Success</Ack>'."\n";
		echo '	<Succes>'."\n";
		echo '		<shortMsg>Artikelbeschreibung wurde gelöscht</shortMsg>'."\n";
		echo '		<longMsg>Artikelbeschreibung zur Gart '.$_POST["GART"].' und dem SprachCode '.$_POST["lang_code"].' wurde gelöscht</longMsg>'."\n";
		echo '	</Succes>'."\n";
		echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
		echo '</GartDeleteDescription>'."\n";
	}

?>