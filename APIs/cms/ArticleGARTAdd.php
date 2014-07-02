<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["article_id"]) )
	{
		echo '<ArticleGARTAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Beitrags-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Beitrags-ID übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ArticleGARTAdd>'."\n";
		exit;
	}

	if ( !isset($_POST["GART"]) )
	{
		echo '<ArticleGARTAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>generische Artikelgruppe nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine generische Artikelgruppe ausgewählt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ArticleGARTAdd>'."\n";
		exit;
	}

	$results=q("SELECT GART FROM t_200 WHERE GART='".$_POST["GART"]."';", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<ArticleGARTAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>generische Artikelgruppe nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte keine passende generische Artikelgruppe zur Auswahl gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ArticleGARTAdd>'."\n";
		exit;
	}
	q("INSERT INTO cms_articles_gart (article_id, GART_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$_POST["article_id"].", ".$_POST["GART"].", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
	
	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<ArticleGARTAdd>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</ArticleGARTAdd>'."\n";

?>