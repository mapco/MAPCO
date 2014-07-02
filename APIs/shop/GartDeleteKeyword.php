<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["id_keyword"]) )
	{
		echo '<GartDeleteKeyword>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Keyword-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Keyword-ID angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartDeleteKeyword>'."\n";
		exit;
	}
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


	$res=q("DELETE FROM shop_items_keywords WHERE id_keyword= '".$_POST["id_keyword"]."';", $dbshop, __FILE__, __LINE__);
	
	$error=mysqli_error($dbshop);
	if ($error!="")
	{
		echo '<GartDeleteKeyword>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Fehler beim Löschen des Schlüsselwortes</shortMsg>'."\n";
		echo '		<longMsg>MySQL-Fehlermeldung: '.$error.'</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartDeleteKeyword>'."\n";
		exit;
	}

	//ORDERING AKTUALISIEREN
	$res=q("SELECT DISTINCT id_keyword FROM shop_items_keywords WHERE GART = ".$_POST["GART"]." ORDER BY ordering;", $dbshop, __FILE__ , __LINE__);
	$ordercount=0;
	while ($row=mysqli_fetch_array($res))
	{ 
		$ordercount++;
			q("UPDATE shop_items_keywords SET ordering = ".$ordercount." WHERE id_keyword = ".$row["id_keyword"].";", $dbshop, __FILE__, __LINE__);
	}


	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;

	if (mysqli_affected_rows()==0)
	{
		echo '<GartDeleteKeyword>'."\n";
		echo '	<Ack>Warning</Ack>'."\n";
		echo '		<Warning>'."\n";
		echo '		<shortMsg>Schlüsselwort wurde nicht gelöscht</shortMsg>'."\n";
		echo '		<longMsg>Zur Keyword-ID '.$_POST["id_keyword"].' wurde kein Schlüsselwort gefunden</longMsg>'."\n";
		echo '		</Warning>'."\n";
		echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
		echo '</GartDeleteKeyword>'."\n";

	}

	else 
	{
		echo '<GartDeleteKeyword>'."\n";
		echo '	<Ack>Success</Ack>'."\n";
		echo '	<Succes>'."\n";
		echo '		<shortMsg>Schlüsselwort wurde gelöscht</shortMsg>'."\n";
		echo '		<longMsg>Schlüsselwort zum Keyword-ID '.$_POST["id_keyword"].' wurde gelöscht</longMsg>'."\n";
		echo '	</Succes>'."\n";
		echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
		echo '</GartDeleteKeyword>'."\n";
	}

?>