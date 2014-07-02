<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["list"]) )
	{
		echo '<ArticleSortResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Artikel-Liste nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Artikel-Liste übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ArticleSortResponse>'."\n";
		exit;
	}

	for($i=0; $i<sizeof($_POST["list"]); $i++)
	{
		$id_article=str_replace("article", "", $_POST["list"][$i]);
		if ( $_POST["id_label"]>0 )
		{
			q("UPDATE cms_articles_labels SET ordering=".($i+1)." WHERE article_id=".$id_article." AND label_id=".$_POST["id_label"].";", $dbweb, __FILE__, __LINE__);
		}
		else
		{
			q("UPDATE cms_articles SET ordering=".($i+1)." WHERE id_article=".$id_article.";", $dbweb, __FILE__, __LINE__);
		}
	}

	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<ArticleSortResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</ArticleSortResponse>'."\n";

?>