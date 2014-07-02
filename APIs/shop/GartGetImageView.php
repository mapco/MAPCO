<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["id_view"])  || (isset($_POST["id_view"]) && $_POST["id_view"]=="")  )
	{
		echo '<GartGetImageView>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>AricleView-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine AricleView-ID angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartGetImageView>'."\n";
		exit;
	}

	$res=q("SELECT * FROM cms_views_gart WHERE id_view= '".$_POST["id_view"]."';", $dbweb, __FILE__, __LINE__);
	if (mysqli_num_rows($res)==0)
	{
		echo '<GartGetImageView>'."\n";
		echo '	<Ack>Warning</Ack>'."\n";
		echo '	<Warning>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Artikelansicht nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte keine Artikelansicht zur AricleView-ID '.$_POST["id_view"].' gefunden werden.</longMsg>'."\n";
		echo '	</Warning>'."\n";
		echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
		echo '</GartGetImageView>'."\n";
		exit;
	}
	else 
	{
		$row=mysqli_fetch_array($res);
		$res=q("SELECT * FROM cms_articles WHERE id_article = '".$row["article_id"]."';", $dbweb, __FILE__, __LINE__);
		
		if (mysqli_num_rows($res)==0)
		{
			echo '<GartGetImageView>'."\n";
			echo '	<Ack>Warning</Ack>'."\n";
			echo '	<Warning>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Keyword nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Es konnte kein Keyword zur Keyword-ID gefunden werden.</longMsg>'."\n";
			echo '	</Warning>'."\n";
			echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
			echo '</GartGetImageView>'."\n";
			exit;
		}
		else 
		{
		
			$row=mysqli_fetch_array($res);
		
			//performance
			$stoptime = time()+microtime();
			$time = $stoptime-$starttime;

			echo '<GartGetImageView>'."\n";
			echo '	<Ack>Success</Ack>'."\n";
			echo '	<Succes>'."\n";
			echo '		<title><![CDATA['.$row["title"].']]></title>'."\n";
			echo '		<desc><![CDATA['.$row["introduction"].']]></desc>'."\n";
			echo '	</Succes>'."\n";
			echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
			echo '</GartGetImageView>'."\n";
		}
	}

?>