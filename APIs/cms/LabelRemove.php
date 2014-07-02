<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["id_label"]) )
	{
		echo '<LabelRemoveResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Stichwort-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Stichwort-ID übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</LabelRemoveResponse>'."\n";
		exit;
	}

	q("DELETE FROM cms_articles_labels WHERE label_id=".$_POST["id_label"].";", $dbweb, __FILE__, __LINE__);
	q("DELETE FROM cms_labels WHERE id_label=".$_POST["id_label"].";", $dbweb, __FILE__, __LINE__);

	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<LabelRemoveResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</LabelRemoveResponse>'."\n";

?>