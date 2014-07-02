<?php

	$xml='';

	$results=q("SELECT * FROM shop_carts WHERE session_id='".session_id()."';", $dbshop, __FILE__, __LINE__);
	if(mysqli_num_rows($results)>0)
	{
		$item_cnt=0;
		while($row=mysqli_fetch_array($results))
		{
			$item_cnt+=1;
			$results2=q("SELECT * FROM shop_items_".$_SESSION["lang"]." WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			$xml.='<item>'."\n";
			$xml.='	<amount><![CDATA['.$row["amount"].']]></amount>'."\n";
			$xml.='	<title><![CDATA['.$row2["title"].']]></title>'."\n";
			$xml.='</item>'."\n";
		}
		$xml.='<shop_cart_items>'.$item_cnt.'</shop_cart_items>'."\n";
	}
	else
	{
		$xml.='<shop_cart_items>0</shop_cart_items>'."\n";
	}
	
	echo $xml;

?>