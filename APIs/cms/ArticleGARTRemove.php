<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["GART_art_id"]) )
	{
		echo '<ArticleGARTRemove>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>ID der generischen Artikelgruppe nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Die ID der Zuodnung der generischen Artikelgruppe zum Artikel wurde nicht gefunden</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ArticleGARTRemove>'."\n";
		exit;
	}

	$results=q("SELECT id FROM cms_articles_gart WHERE id='".$_POST["GART_art_id"]."';", $dbweb, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<ArticleGARTRemove>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Zuordnung von Artikel und GART nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Es konnte die Zuordnung von generische Artikelgruppe und Artikel nicht gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ArticleGARTRemove>'."\n";
		exit;
	}
	q("DELETE FROM cms_articles_gart WHERE id = '".$_POST["GART_art_id"]."';", $dbweb, __FILE__, __LINE__);
	
	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<ArticleGARTRemove>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</ArticleGARTRemove>'."\n";

?>