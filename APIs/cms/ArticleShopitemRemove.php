<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["id_item"]) )
	{
		echo '<ArticleShopitemRemove>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>ID der Shopartikelzuordnung nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Die ID der Zuodnung des Shopartikels zum Artikel wurde nicht gefunden</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ArticleShopitemRemove>'."\n";
		exit;
	}

	$results=q("SELECT id FROM cms_articles_shopitems WHERE id='".$_POST["id_item"]."';", $dbweb, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<ArticleShopitemRemove>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Zuordnung von Artikel und Shopartikel nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Es konnte die Zuordnung von Shopartikel und Artikel nicht gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ArticleShopitemRemove>'."\n";
		exit;
	}
	q("DELETE FROM cms_articles_shopitems WHERE id = '".$_POST["id_item"]."';", $dbweb, __FILE__, __LINE__);
	
	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<ArticleShopitemRemove>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</ArticleShopitemRemove>'."\n";

?>