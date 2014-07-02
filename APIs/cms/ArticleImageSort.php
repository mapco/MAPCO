<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["list"]) )
	{
		echo '<ArticleImageSortResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bilderliste nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Bilderliste übermittelt werden, damit der Service weiß, wie die Bilder neu angeordnet werden sollen.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ArticleImageSortResponse>'."\n";
		exit;
	}

	//image sort
	for($i=0; $i<sizeof($_POST["list"]); $i++)
	{
		q("UPDATE cms_articles_images SET ordering=".($i+1)." WHERE id=".$_POST["list"][$i].";", $dbweb, __FILE__, __LINE__);
	}

	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<ArticleImageSortResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</ArticleImageSortResponse>'."\n";

?>