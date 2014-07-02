<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["article_id"]) )
	{
		echo '<ArticleShopitemAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Beitrags-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Beitrags-ID übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ArticleShopitemAdd>'."\n";
		exit;
	}

	if ( !isset($_POST["text"]) )
	{
		echo '<ArticleShopitemAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Suchtext nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Suchtext übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ArticleShopitemAdd>'."\n";
		exit;
	}

	$results=q("SELECT * FROM shop_items WHERE MPN='".$_POST["text"]."';", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<ArticleShopitemAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Shopartikel nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte kein passender Shopartikel zum Suchbegriff gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ArticleShopitemAdd>'."\n";
		exit;
	}
	$row=mysqli_fetch_array($results);
	q("INSERT INTO cms_articles_shopitems (article_id, item_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$_POST["article_id"].", ".$row["id_item"].", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
	
	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<ArticleShopitemAdd>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</ArticleShopitemAdd>'."\n";

?>