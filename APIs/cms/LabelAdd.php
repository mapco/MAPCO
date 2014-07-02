<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["label"]) )
	{
		echo '<LabelAddResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Titel nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Titel für das Label übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</LabelAddResponse>'."\n";
		exit;
	}

	if ( $_POST["label"]=="" )
	{
		echo '<LabelAddResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Titel ist leer.</shortMsg>'."\n";
		echo '		<longMsg>Der Titel für das Stichwort darf nicht leer sein.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</LabelAddResponse>'."\n";
		exit;
	}

	$results=q("SELECT * FROM cms_labels WHERE site_id=".$_SESSION["id_site"].";", $dbweb, __FILE__, __LINE__);
	$ordering=mysqli_num_rows($results)+1;
	q("INSERT INTO cms_labels (site_id, label, description, ordering, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$_SESSION["id_site"].", '".mysqli_real_escape_string($dbweb,$_POST["label"])."', '".mysqli_real_escape_string($dbweb,$_POST["description"])."', ".$ordering.", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);

	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<LabelAddResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</LabelAddResponse>'."\n";

?>