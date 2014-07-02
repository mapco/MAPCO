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
	if ( mysqli_num_rows($results)==0 )
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
		echo '		<longMsg>Es muss eine Sprache zu den Meta-Daten ausgewählt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartAddDescription>'."\n";
		exit;
	}
	
	$res=q("SELECT * FROM cms_languages WHERE id_language=".$_POST["lang_code"].";", $dbweb, __FILE__, __LINE__);	
	if ( mysqli_num_rows($res)==0 )
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

	if ( !isset($_POST["description"]) )
	{
		echo '<GartAddDescription>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Meta-Daten nicht vorhanden.</shortMsg>'."\n";
		echo '		<longMsg>Es müssen Meta-Daten eingegeben werden.</longMsg>'."\n";
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
		$res=q("INSERT INTO shop_items_descriptions (GART, language_id, keywords, description, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$_POST["GART"].", '".$_POST["lang_code"]."', '".mysqli_real_escape_string($dbshop, $_POST["keywords"])."', '".mysqli_real_escape_string($dbshop, $_POST["description"])."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
	}
	
	if ($_POST["mode"]=="update") {
		$res=q("UPDATE shop_items_descriptions SET keywords='".mysqli_real_escape_string($dbshop, $_POST["keywords"])."', description = '".mysqli_real_escape_string($dbshop, $_POST["description"])."', lastmod = ".time().", lastmod_user = ".$_SESSION["id_user"]." WHERE GART = ".$_POST["GART"]." AND language_id = '".$_POST["lang_code"]."' ;", $dbshop, __FILE__, __LINE__);
	}
	
	$error=mysqli_error($dbshop);
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