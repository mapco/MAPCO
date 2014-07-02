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
	if ( !isset($_POST["id_item"]) )
	{
		echo '<ListRemoveResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Item-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Item-ID übergeben werden, damit der Service weiß, welcher Artikel gelöscht werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ListRemoveResponse>'."\n";
		exit;
	}

	q("DELETE FROM shop_lists_items WHERE list_id=".$_POST["id_list"]." and item_id=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);

	//return success
	echo '<ListRemoveResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<ListID>'.$id_list.'</ListID>'."\n";
	echo '	<ItemID>'.$id_item.'</ItemID>'."\n";
	echo '</ListRemoveResponse>'."\n";

?>