<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["mode"]) || (isset($_POST["mode"]) && $_POST["mode"]=="") )
	{
		echo '<GartAddImageView>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bearbeitungsmodus nicht angegeben</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Bearbeitungsmodus angegeben werden. Verfügbare Modi: add || update </longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartAddImageView>'."\n";
		exit;
	}
	
	if ( !isset($_POST["title"]) || (isset($_POST["title"]) && $_POST["title"]=="") )
	{
		echo '<GartAddImageView>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Titel nicht vorhanden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Titel zur Artieklansicht eingegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartAddImageView>'."\n";
		exit;
	}

	if ($_POST["mode"]=="add") {
		
		if ( !isset($_POST["GART"]) )
		{
			echo '<GartAddImageView>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>generische Artikelgruppe nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Es muss eine generische Artikelgruppe ausgewählt werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</GartAddImageView>'."\n";
			exit;
		}

		$results=q("SELECT GART FROM t_200 WHERE GART='".$_POST["GART"]."';", $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows($results)==0 )
		{
			echo '<GartAddImageView>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>generische Artikelgruppe nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Es konnte keine passende generische Artikelgruppe zur Auswahl gefunden werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</GartAddImageView>'."\n";
			exit;
		}

		//Max Ordering
		$res=q("SELECT MAX(ordering) as ordering from cms_views_gart where GART = '".$_POST["GART"]."';", $dbweb, __FILE__, __LINE__);
		if (mysqli_num_rows($res)==0) $ordering=1; 
		else {$row=mysqli_fetch_array($res); $ordering=$row["ordering"]+1;}
		if ($ordering=="") $ordering=1; 
		
		//Check if Label id=17 (GART Ansichten) exists
		$res=q("SELECT * FROM cms_labels WHERE id_label = 17;", $dbweb, __FILE__, __LINE__);
		if (mysqli_num_rows($res)==0)
		{
			echo '<GartGetKeywords>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>CMS Label nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Es konnte kein Label zur generischen Artikel-Ansicht (für cms_articles) gefunden werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</GartGetKeywords>'."\n";
			exit;
		}
		
		//Article anlegen
		$sql = "INSERT INTO cms_articles (";
		$sql.= "language_id, article_id, title, introduction, article, published, format, imageprofile_id, ordering, newsletter, firstmod, firstmod_user, lastmod, lastmod_user";
		$sql.= ") VALUES (";
		$sql.= "'1', '0', '".mysqli_real_escape_string($dbweb, $_POST["title"])."', '".mysqli_real_escape_string($dbweb, $_POST["desc"])."', '', '0', '0', '0', '', '0', '".time()."', '".$_SESSION["id_user"]."', '".time()."', '".$_SESSION["id_user"]."')";
		$res=q($sql, $dbweb, __FILE__, __LINE__);
		$articleID=mysqli_insert_id($dbweb);
		//LABEL verknüpfen
		$res=q("INSERT INTO cms_articles_labels (article_id, label_id, ordering) VALUES (".$articleID.", 17, 0);", $dbweb, __FILE__, __LINE__);
		
		$res=q("INSERT INTO cms_views_gart (GART, ordering, article_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$_POST["GART"].", ".$ordering.", '".$articleID."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);

	} // IF MODE ADD
	
	if ($_POST["mode"]=="update") {
		
		if ( !isset($_POST["id_view"]) || (isset($_POST["id_view"]) && $_POST["id_view"]=="") )
		{
			echo '<GartAddImageView>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>ImageView-ID nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Es muss eine ImageView-ID angegeben werden um ein Update zur Artikelansicht durchzuführen.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</GartAddImageView>'."\n";
			exit;
		}
		
		//CHECK IF CMS_Article exists
		$res=q("SELECT * FROM cms_views_gart WHERE id_view='".$_POST["id_view"]."';", $dbweb, __FILE__, __LINE__);
		if (mysqli_num_rows($res)>0)
		{
			$row=mysqli_fetch_array($res);
			q("UPDATE cms_articles SET title = '".mysqli_real_escape_string($dbweb, $_POST["title"])."', introduction = '".mysqli_real_escape_string($dbweb, $_POST["desc"])."' WHERE id_article = ".$row["article_id"].";", $dbweb, __FILE__, __LINE__);
		}
		else
		{
			echo '<GartGetKeywords>'."\n";
			echo '	<Ack>Warning</Ack>'."\n";
			echo '	<Warning>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>CMS Artikel nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Es konnte kein Artikel zur generischen Artikel-Ansicht gefunden werden.</longMsg>'."\n";
			echo '	</Warning>'."\n";
			echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
			echo '</GartGetKeywords>'."\n";
			exit;
		}

	} // IF MODE UPDATE
	
	$error=mysqli_error($dbweb);
	if ($error!="")
	{
		echo '<GartAddImageView>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Fehler beim Schreiben der Daten</shortMsg>'."\n";
		echo '		<longMsg>MySQL-Fehlermeldung: '.$error.'</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartAddImageView>'."\n";
		exit;
	}

	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<GartAddImageView>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</GartAddImageView>'."\n";

?>