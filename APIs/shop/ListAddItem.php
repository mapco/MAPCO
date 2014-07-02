<?php
	if ( !isset($_POST["list_id"]) )
	{
		echo '<ListAddItemResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Listen-ID nicht gesetzt.</shortMsg>'."\n";
		echo '		<longMsg>Es muss einen Listen-ID übergeben werden, damit der Artikel zugeordnet werden kann.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ListAddItemResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["item_id"]) )
	{
		echo '<ListAddItemResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Artikel-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Artikel-ID übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ListAddItemResponse>'."\n";
		exit;
	}
	
	if(!isset($_POST["amount"]))
		$amount=0;
	else
		$amount=$_POST["amount"];

	//check if item is already in list
	$results=q("SELECT * FROM shop_lists_items WHERE list_id=".$_POST["list_id"]." AND item_id=".$_POST["item_id"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		q("INSERT INTO shop_lists_items (list_id, item_id, amount, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$_POST["list_id"].", ".$_POST["item_id"].", ".$amount.", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
//		$id_list=mysqli_insert_id($dbshop);
	}
	
	//price research
	$results=q("SELECT * FROM shop_lists WHERE id_list=".$_POST["list_id"].";", $dbshop, __FILE__. __LINE__);
	$row=mysqli_fetch_array($results);
	if( $row["listtype_id"]==4 )
	{
		q("UPDATE shop_items SET InPriceResearch=1 WHERE id_item=".$_POST["item_id"].";", $dbshop, __FILE__, __LINE__);
	}

	//return success
	echo '<ListAddItemResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</ListAddItemResponse>'."\n";

?>