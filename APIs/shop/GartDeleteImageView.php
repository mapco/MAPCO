<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["id_view"])  || (isset($_POST["id_view"]) && $_POST["id_view"]=="")  )
	{
		echo '<GartDeleteImageView>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>AricleView-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine ArticleView-ID angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartDeleteImageView>'."\n";
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

	$res=q("SELECT * FROM cms_views_gart WHERE id_view = ".$_POST["id_view"].";", $dbweb, __FILE__, __LINE__);
	
	if (mysqli_num_rows($res)==0)
	{
		echo '<GartDeleteImageView>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Artikelansicht nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte keine Artikelansicht zur ArticleView-ID '.$_POST["id_view"].' gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartDeleteImageView>'."\n";
		exit;
	}
	else 
	{
		$row=mysqli_fetch_array($res);

		//CMS_ARTICLE löschen
		q("DELETE FROM cms_articles WHERE id_article = ".$row["article_id"].";", $dbweb, __FILE__, __LINE__);
		$error.=mysqli_error($dbweb);
		//LABELVerknüpfung löschen
		q("DELETE FROM cms_articles_labels WHERE label_id = 17 AND article_id=".$row["article_id"].";", $dbweb, __FILE__, __LINE__);
		$error.=mysqli_error($dbweb);
		//IMAGEVIEW löschen
		q("DELETE FROM cms_views_gart WHERE id_view = ".$_POST["id_view"].";", $dbweb, __FILE__, __LINE__);
		$error.=mysqli_error($dbweb);
	
		//ORDERING NEU ERSTELLEN
		$res=q("SELECT * FROM cms_views_gart WHERE GART = ".$_POST["GART"]." ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
		$ordercount=0;
		while ($row=mysqli_fetch_array($res))
		{
			$ordercount++;
			q("UPDATE cms_views_gart SET ordering = ".$ordercount." WHERE id_view = ".$row["id_view"].";", $dbweb, __FILE__, __LINE__);
		}
	
/*		if ($error!="")
		{
			echo '<GartDeleteImageView>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Fehler beim Löschen der ArtikelAnsicht</shortMsg>'."\n";
			echo '		<longMsg>MySQL-Fehlermeldung: '.$error.'</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</GartDeleteImageView>'."\n";
			exit;
		}
*/
		//performance
		$stoptime = time()+microtime();
		$time = $stoptime-$starttime;

/*		if (mysqli_affected_rows()==0)
		{
			echo '<GartDeleteImageView>'."\n";
			echo '	<Ack>Warning</Ack>'."\n";
			echo '		<Warning>'."\n";
			echo '		<shortMsg>Artikelansicht wurde nicht gelöscht</shortMsg>'."\n";
			echo '		<longMsg>Zur ArticleView-ID '.$_POST["id_view"].' wurde keine Artikelansicht gefunden</longMsg>'."\n";
			echo '		</Warning>'."\n";
			echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
			echo '</GartDeleteImageView>'."\n";
		}
		else 
		{
*/
			echo '<GartDeleteImageView>'."\n";
			echo '	<Ack>Success</Ack>'."\n";
			echo '	<Success>'."\n";
			echo '		<shortMsg>Artikelansicht wurde gelöscht</shortMsg>'."\n";
			echo '		<longMsg>Artikelansicht zur ArticleView-ID '.$_POST["id_view"].' wurde gelöscht</longMsg>'."\n";
			echo '	</Success>'."\n";
			echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
			echo '</GartDeleteImageView>'."\n";
	
	//	}
	}
?>