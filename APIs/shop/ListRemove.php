<?php
	if ( !isset($_POST["id_list"]) )
	{
		echo '<ListRemoveResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Listen-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Listen-ID übergeben werden, damit der Service weiß, welche Liste gelöscht werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ListRemoveResponse>'."\n";
		exit;
	}
	$results=q("SELECT * FROM shop_lists WHERE id_list=".$_POST["id_list"].";", $dbshop, __FILE__, __LINE__);
	$list=mysqli_fetch_array($results);
	
	//checkin price research items
	if( $list["listtype_id"]==4 )
	{
		$items=array();
		$results=q("SELECT * FROM shop_lists_items WHERE list_id=".$_POST["id_list"].";", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			$items[]=$row["item_id"];
		}
		if (sizeof($items)>0) q("UPDATE shop_items SET InPriceResearch=0 WHERE id_item IN (".implode(", ", $items).");", $dbshop, __FILE__, __LINE__);
	}
	
	q("DELETE FROM shop_lists_items WHERE list_id=".$_POST["id_list"].";", $dbshop, __FILE__, __LINE__);
	q("DELETE FROM shop_lists WHERE id_list=".$_POST["id_list"].";", $dbshop, __FILE__, __LINE__);
	q("DELETE FROM shop_offers WHERE list_id=".$_POST["id_list"].";", $dbshop, __FILE__, __LINE__);

	//return success
	echo '<ListRemoveResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<ListID>'.$id_list.'</ListID>'."\n";
	echo '</ListRemoveResponse>'."\n";

?>