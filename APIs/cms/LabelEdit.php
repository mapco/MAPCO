<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["id_label"]) )
	{
		echo '<LabelEditResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Stichwort-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Stichwort-ID übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</LabelEditResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["label"]) )
	{
		echo '<LabelEditResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Titel nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Titel für das Stichwort übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</LabelEditResponse>'."\n";
		exit;
	}

	if ( $_POST["label"]=="" )
	{
		echo '<LabelEditResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Titel leer.</shortMsg>'."\n";
		echo '		<longMsg>Der Titel für das Stichwort darf nicht leer sein.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</LabelEditResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["site_id"]) )
	{
		echo '<LabelEditResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Seiten-ID fehlt.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Seiten-ID (site_id) übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</LabelEditResponse>'."\n";
		exit;
	}

	q("UPDATE cms_labels
	   SET	label='".mysqli_real_escape_string($dbweb,$_POST["label"])."',
			description='".mysqli_real_escape_string($dbweb,$_POST["description"])."',
			site_id=".$_POST["site_id"].",
			lastmod=".time().",
			lastmod_user=".$_SESSION["id_user"]."
	   WHERE id_label=".$_POST["id_label"].";", $dbweb, __FILE__, __LINE__);
	
	//if site changes move articles too
	$articles=array();
	$results=q("SELECT article_id FROM cms_articles_labels WHERE label_id=".$_POST["id_label"].";", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$articles[$row["article_id"]]=$row["article_id"];
	}
	if( sizeof($articles)>0 )
	{
		$results=q("SELECT id_article, site_id FROM cms_articles WHERE id_article IN (".implode(", ", $articles).");", $dbweb, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			if( $_POST["site_id"]==$row["site_id"] ) unset($articles[$row["id_article"]]);
		}
		if( sizeof($articles)>0 ) q("UPDATE cms_articles SET site_id=".$_POST["site_id"]." WHERE id_article IN (".implode(", ", $articles).");", $dbweb, __FILE__, __LINE__);
	}

	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<LabelEditResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</LabelEditResponse>'."\n";

?>