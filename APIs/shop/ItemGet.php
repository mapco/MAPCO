<?php
	include("../functions/cms_t.php");

	if ( !isset($_POST["MPN"]) and !isset($_POST["id_item"]) )
	{
		echo '<ItemGetResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Artikelnummer nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine gültige Shopartikel-ID (id_item) oder eine Hersteller-Artikelnummer (MPN) angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemGetResponse>'."\n";
		exit;
	}
	
	if( isset($_POST["id_item"]) )
	{
		$results=q("SELECT * FROM shop_items WHERE id_item=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
	}
	elseif( isset($_POST["MPN"]) )
	{
		$results=q("SELECT * FROM shop_items WHERE MPN='".$_POST["MPN"]."';", $dbshop, __FILE__, __LINE__);
	}
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<ItemGetResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Artikel nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es scheint keinen Artikel mit der angegebenen Nummer zu existieren.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemGetResponse>'."\n";
		exit;
	}
	$row=mysqli_fetch_array($results);
	$keys=array_keys($row);
	
	echo '<ItemGetResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	for($i=0; $i<sizeof($keys); $i++)
	{
		if( !is_numeric($keys[$i]) )
			echo '	<'.$keys[$i].'>'.$row[$keys[$i]].'</'.$keys[$i].'>'."\n";
	}
	echo '</ItemGetResponse>'."\n";

?>