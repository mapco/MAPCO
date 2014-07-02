<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	
	$ebay=array();
	$res=q("SELECT * FROM ebay_orders WHERE firstmod > ".(time()-24*3600).";", $dbshop, __FILE__, __LINE__);
	while ($row=mysql_fetch_array($res))
	{
		$ebay[$row["OrderID"]]=$row["id_order"];
	}
	
	echo sizeof($ebay)."+";

	$shop=array();
	$res2=q("SELECT * FROM shop_orders;", $dbshop, __FILE__, __LINE__);
	while($row2=mysql_fetch_array($res2))
	{
		if ($row2["foreign_OrderID"]!="") $shop[$row2["foreign_OrderID"]]=0;
	}
	echo sizeof($shop)."<br />";
	
	while (list ($key, $val) = each ($ebay))
	{
		if (!isset($shop[$key])) echo $key."<br />";
		
		//echo post(PATH."soa/", array("API" => "crm", "Action" => "repair_shop_order_data", "EbayOrderID" => $val))."<br />";
	}


	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>