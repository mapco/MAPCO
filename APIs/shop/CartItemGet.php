<?php

	check_man_params(array("item_id"	=>	"numeric"));
	
	$xml='';
	$results=q("SELECT * FROM shop_carts WHERE shop_id=".$_SESSION["id_shop"]." AND user_id=".$_SESSION["id_user"]." AND item_id=".$_POST["item_id"].";", $dbshop, __FILE__, __LINE__);
	if(mysqli_num_rows($results)==1)
	{
		$row=mysqli_fetch_array($results);
		$amount=$row["amount"];
	}
	else
		$amount=0;
	
	$xml.='	<amount>'.$amount.'</amount>';
	echo $xml;
		
?>