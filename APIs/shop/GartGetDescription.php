<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["GART"]) )
	{
		echo '<GartGetDescription>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>generische Artikelgruppe nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine generische Artikelgruppe ausgewählt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartGetDescription>'."\n";
		exit;
	}

	if ( !isset($_POST["lang_code"]) || (isset($_POST["lang_code"]) && $_POST["lang_code"]=="") )
	{
		echo '<GartGetDescription>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Sprache (Code) nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Sprache ausgewählt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartGetDescription>'."\n";
		exit;
	}
	
	$res=q("SELECT * FROM shop_items_descriptions WHERE GART= '".$_POST["GART"]."' AND language_id = '".$_POST["lang_code"]."';", $dbshop, __FILE__, __LINE__);
	

	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;

	if (mysqli_num_rows($res)==0)
	{
		echo '<GartGetDescription>'."\n";
		echo '	<Ack>Warning</Ack>'."\n";
		echo '		<Warning>'."\n";
		echo '		<shortMsg>Anfrage liefert kein Ergebnis</shortMsg>'."\n";
		echo '		<longMsg>Zur Gart '.$_POST["GART"].' und dem SprachCode '.$_POST["lang_code"].' wurde keine Artikelbeschreibung gefunden</longMsg>'."\n";
		echo '		</Warning>'."\n";
		echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
		echo '</GartGetDescription>'."\n";
		exit;
	}

	$row=mysqli_fetch_array($res);
	$keys=array_keys($row);
	
	echo '<GartGetDescription>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	for($i=0; $i<sizeof($keys); $i++)
	{
		if( !is_numeric($keys[$i]) )
			echo '	<'.$keys[$i].'><![CDATA['.$row[$keys[$i]].']]></'.$keys[$i].'>'."\n";
	}
	echo '</GartGetDescription>'."\n";

?>