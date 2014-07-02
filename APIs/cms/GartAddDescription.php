<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["GART"]) )
	{
		echo '<GartAddDescription>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>generische Artikelgruppe nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine generische Artikelgruppe ausgewählt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartAddDescription>'."\n";
		exit;
	}

	$results=q("SELECT GART FROM t_200 WHERE GART='".$_POST["GART"]."';", $dbshop, __FILE__, __LINE__);
	if ( mysql_num_rows($results)==0 )
	{
		echo '<GartAddDescription>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>generische Artikelgruppe nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte keine passende generische Artikelgruppe zur Auswahl gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartAddDescription>'."\n";
		exit;
	}
	
	if ( !isset($_POST["lang_code"]) || (isset($_POST["lang_code"]) && $_POST["lang_code"]=="") )
	{
		echo '<GartAddDescription>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Sprache (Code) nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Sprache zur Artikelbeschreibung ausgewählt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartAddDescription>'."\n";
		exit;
	}
	
	$res=q("SELECT * FROM cms_languages WHERE code = '".$_POST["lang_code"]."';", $dbsweb, __FILE__, __LINE__);	
	if ( mysql_num_rows($res)==0 )
	{
		echo '<GartAddDescription>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Sprache (Code) nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Die ausgewählte Sprache ist im System nicht hinterlegt.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartAddDescription>'."\n";
		exit;
	}

	if ( !isset($_POST["description"]) || (isset($_POST["description"]) && $_POST["description"]=="") )
	{
		echo '<GartAddDescription>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Artikelbeschreibung nicht vorhanden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Artikelbeschreibung eingegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartAddDescription>'."\n";
		exit;
	}
	
	if ( !isset($_POST["mode"]) || (isset($_POST["mode"]) && $_POST["mode"]=="") )
	{
		echo '<GartAddDescription>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bearbeitungsmodus nicht angegeben</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Bearbeitungsmodus angegeben werden. Verfügbare Modi: add || update </longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartAddDescription>'."\n";
		exit;
	}


	if ($_POST["mode"]=="add") {
		q("INSERT INTO shop_items_descriptions (GART, language_id, description, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$_POST["GART"].", '".$_POST["lang_code"]."', '".mysql_real_escape_string($_POST["description"], $dbshop)."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
	}
	
	if ($_POST["mode"]=="upadate") {
		q("UPDATE shop_items_descriptions SET description = '".mysql_real_escape_string($_POST["description"], $dbshop)."', lastmode = ".time().", lastmod_user = ".$_SESSION["id_user"]." WHERE GART = ".$_POST["GART"]." AND language_id = '".$_POST["lang_code"]."' ;",  $db_shop, __FILE__, __LINE__);
	}
	
	$error=mysql_error();
	if ($error!="")
	{
		echo '<GartAddDescription>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Fehler beim Schreiben der Daten</shortMsg>'."\n";
		echo '		<longMsg>MySQL-Fehlermeldung: '.$error.'</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartAddDescription>'."\n";
		exit;
	}

	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<GartAddDescription>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</GartAddDescription>'."\n";

?>