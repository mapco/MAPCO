<?php

	if ( !isset($_POST["id_label"]) )
	{
		echo '<LabelGetResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Stichwort-ID fehlt.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Stichwort-ID (id_label) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</LabelGetResponse>'."\n";
		exit;
	}

	if( isset($_POST["id_label"]) )
	{
		$results=q("SELECT * FROM cms_labels WHERE site_id IN (0, ".$_SESSION["id_site"].") AND id_label=".$_POST["id_label"].";", $dbweb, __FILE__, __LINE__);
	}
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<LabelGetResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Stichwort nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Unter der angegebenen Stichwort-ID (id_label) konnte kein Stichwort gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</LabelGetResponse>'."\n";
		exit;
	}
	$row=mysqli_fetch_array($results);
	$keys=array_keys($row);
	
	echo '<LabelGetResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	for($i=0; $i<sizeof($keys); $i++)
	{
		if( !is_numeric($keys[$i]) )
			echo '	<'.$keys[$i].'>'.$row[$keys[$i]].'</'.$keys[$i].'>'."\n";
	}
	echo '</LabelGetResponse>'."\n";

?>