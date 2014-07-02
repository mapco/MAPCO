<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["mode"]) || (isset($_POST["mode"]) && $_POST["mode"]=="") )
	{
		echo '<GartAddKeyword>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bearbeitungsmodus nicht angegeben</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Bearbeitungsmodus angegeben werden. Verfügbare Modi: add || update </longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartAddKeyword>'."\n";
		exit;
	}
	
	if ( !isset($_POST["GART"]) )
	{
		echo '<GartAddKeyword>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>generische Artikelgruppe nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine generische Artikelgruppe ausgewählt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartAddKeyword>'."\n";
		exit;
	}

	if ( !isset($_POST["id_language"]) )
	{
		echo '<GartAddKeyword>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Sprache nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Sprache (id_language) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartAddKeyword>'."\n";
		exit;
	}

	if ( !isset($_POST["keyword"]) )
	{
		echo '<GartAddKeyword>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Synonym nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Synonym (keyword) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartAddKeyword>'."\n";
		exit;
	}

	if ( $_POST["keyword"]=="" )
	{
		echo '<GartAddKeyword>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Synonym leer.</shortMsg>'."\n";
		echo '		<longMsg>Das übergebene Synonym (keyword) darf nicht leer sein.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartAddKeyword>'."\n";
		exit;
	}

	$results=q("SELECT GART FROM t_200 WHERE GART='".$_POST["GART"]."';", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<GartAddKeyword>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>generische Artikelgruppe nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte keine passende generische Artikelgruppe zur Auswahl gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartAddKeyword>'."\n";
		exit;
	}

	if ($_POST["mode"]=="add")
	{
		//Max id_keyword
		$res=q("SELECT MAX(id_keyword) as id_keyword from shop_items_keywords;", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res)==0) $id_keyword=1; 
		else {$row=mysqli_fetch_array($res); $id_keyword=$row["id_keyword"]+1;}
		if ($id_keyword=="") $id_keyword=1; 
		//Max Ordering
		$res=q("SELECT MAX(ordering) as ordering from shop_items_keywords where GART = '".$_POST["GART"]."';", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res)==0) $ordering=1; 
		else {$row=mysqli_fetch_array($res); $ordering=$row["ordering"]+1;}
		if ($ordering=="") $ordering=1; 

		
		while (list($code, $keyword) = each ($keywords))
		{
			if ($keyword!="") $res=q("INSERT INTO shop_items_keywords (id_keyword, GART, language_id, ordering, keyword, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$id_keyword.", ".$_POST["GART"].", '".$code."', ".$ordering.", '".mysqli_real_escape_string($dbshop, $keyword)."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
		}
	} // IF MODE ADD
	
	if ($_POST["mode"]=="update") {
		
		if ( !isset($_POST["id_keyword"]) || (isset($_POST["id_keyword"]) && $_POST["id_keyword"]=="") )
		{
			echo '<GartAddKeyword>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Keyword-ID nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Es muss eine Keyword-ID angegeben werden um ein Update zum Keyword durchzuführen.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</GartAddKeyword>'."\n";
			exit;
		}
		
		while (list($code, $keyword) = each ($keywords))
		{
			//CHECK IF KEYWORD+LANG_ID EXIST
			$res=q("SELECT * FROM shop_items_keywords WHERE id_keyword = ".$_POST["id_keyword"]." AND language_id = '".$code."' ;", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res)>0)
			{				
				//UPDATE KEYWORD/SPRACHE
				if ($keyword=="")
				{
					//LÖSCHE KEYWORD/SPRACHE, wenn Eintrag leer
					$res=q("DELETE FROM shop_items_keywords WHERE id_keyword = ".$_POST["id_keyword"]." AND language_id = '".$code."' ;", $dbshop, __FILE__, __LINE__);
				}
				else 
				{
					$res=q("UPDATE shop_items_keywords SET keyword = '".mysqli_real_escape_string($dbshop, $keyword)."', lastmod = ".time().", lastmod_user = ".$_SESSION["id_user"]." WHERE id_keyword = ".$_POST["id_keyword"]." AND language_id = '".$code."' ;", $dbshop, __FILE__, __LINE__);
				}
			}
			else 
			{
				//ADD KEYWORD/SPRACHE
				//Ordering
				if ($keyword!="")
				{
					$res=q("SELECT ordering as ordering from shop_items_keywords where id_keyword = '".$_POST["id_keyword"]."';", $dbshop, __FILE__, __LINE__);
					if (mysqli_num_rows($res)==0) $ordering=1; 
					else {$row=mysqli_fetch_array($res); $ordering=$row["ordering"];}
					if ($ordering=="") $ordering=1; 
					
					$res=q("INSERT INTO shop_items_keywords (id_keyword, GART, language_id, ordering, keyword, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$id_keyword.", ".$_POST["GART"].", '".$code."', ".$ordering.", '".mysqli_real_escape_string($dbshop, $keyword)."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
				}
			}
		}
	} // IF MODE UPDATE
	
	$error=mysqli_error($dbshop);
	if ($error!="")
	{
		echo '<GartAddKeyword>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Fehler beim Schreiben der Daten</shortMsg>'."\n";
		echo '		<longMsg>MySQL-Fehlermeldung: '.$error.'</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartAddKeyword>'."\n";
		exit;
	}

	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<GartAddKeyword>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</GartAddKeyword>'."\n";

?>