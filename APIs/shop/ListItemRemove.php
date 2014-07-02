<?php
	if ( !isset($_POST["id"]) )
	{
		echo '<ListRemoveResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine ID übergeben werden, damit der Service weiß, welcher Artikel gelöscht werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ListRemoveResponse>'."\n";
		exit;
	}
	
	//CHECK FOR LISTTYPE_ID = 4: has pricesuggestion?
		//FIND Item
	$res_item=q("SELECT * FROM shop_lists_items WHERE id=".$_POST["id"].";", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_item)==0)
	{
		echo '<ListRemoveResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Artikel nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte kein ListenItem mit der ID '.$_POST["id"].' gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ListRemoveResponse>'."\n";
		exit;
	}
	$item=mysqli_fetch_array($res_item);
		
		//FIND LIST OF ITEM
	$res_list=q("SELECT * FROM shop_lists WHERE id_list = ".$item["list_id"].";", $dbshop, __FILE__ , __LINE__);
	if (mysqli_num_rows($res_list)==0)
	{
		echo '<ListRemoveResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Liste nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte keine Liste (ListID'.$item["list_id"].') zum ListenItem (ID: '.$_POST["id"].') gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ListRemoveResponse>'."\n";
		exit;
	}
	$list=mysqli_fetch_array($res_list);
	
	if ($list["listtype_id"]==4)
	{
		
		//get PriceSuggestions for Item
		$res_suggestions=q("SELECT * FROM shop_price_suggestions WHERE item_id = ".$item["item_id"]." ORDER BY firstmod DESC LIMIT 1;", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_suggestions)==0)
		{
			echo '<ListRemoveResponse>'."\n";
			echo '	<Ack>Warning</Ack>'."\n";
			echo '	<Warning>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Kein Preisvorschlag gefunden</shortMsg>'."\n";
			echo '		<longMsg>Kein Preisvorschlag gefunden. Listeneintrag kann nur gelöscht werden, wenn ein aktueller Preisvorschlag vorliegt.</longMsg>'."\n";
			echo '	</Warning>'."\n";
			echo '</ListRemoveResponse>'."\n";
			exit;
		}
		//CHECK IF LASTEST SUGGESTION is not older than 3 month
		$row_suggestions=mysqli_fetch_array($res_suggestions);
		if ($row_suggestions["firstmod"]<(time()-90*24*3600))
		{
			echo '<ListRemoveResponse>'."\n";
			echo '	<Ack>Warning</Ack>'."\n";
			echo '	<Warning>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Kein aktuellen Preisvorschlag gefunden</shortMsg>'."\n";
			echo '		<longMsg>Kein aktuellen Preisvorschlag gefunden. Listeneintrag kann nur gelöscht werden, wenn ein aktueller Preisvorschlag vorliegt.</longMsg>'."\n";
			echo '	</Warning>'."\n";
			echo '</ListRemoveResponse>'."\n";
			exit;
		}
		
		//REMOVE FLAG FROM SHOP_ITEMS
		q("UPDATE shop_items SET InPriceResearch = 0 WHERE id_item = ".$item["item_id"].";", $dbshop, __FILE__, __LINE__);

	}
	
	q("DELETE FROM shop_lists_items WHERE id=".$_POST["id"].";", $dbshop, __FILE__, __LINE__);


	//return success
	echo '<ListRemoveResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</ListRemoveResponse>'."\n";

?>