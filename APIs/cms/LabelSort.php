<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["list"]) )
	{
		echo '<LabelSortResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Liste nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Stichwort-Liste übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</LabelSortResponse>'."\n";
		exit;
	}

	for($i=0; $i<sizeof($_POST["list"]); $i++)
	{
		$id_label=str_replace("label", "", $_POST["list"][$i]);
		q("UPDATE cms_labels SET ordering=".($i+1)." WHERE id_label=".$id_label.";", $dbweb, __FILE__, __LINE__);
	}

	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<LabelSortResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</LabelSortResponse>'."\n";

?>