<?php
	
	include("../functions/mapco_gewerblich.php");
	
	check_man_params(array("mode" 			=> "text",
						   "time_type"		=> "text",
						   "shop_countries"	=> "numeric"));
	
	if ($_POST["mode"]=="single")
	{
		$required=array("date_from"			=>"numeric", 
						"date_to" 			=>"numeric",
						"shops_selected"	=>"numeric"); 
		
		check_man_params($required);
	}
	
	if ($_POST["mode"]=="comp")
	{
		$required=array("date_from"			=>"numeric", 
						"date_to" 			=>"numeric",
						"date_comp_from" 	=>"numeric",
						"date_comp_to" 		=>"numeric",
						"shops_selected"	=>"numeric"); 
		
		check_man_params($required);
	}
	
	$xml="";
	
	//Order Ids suchen
	
	//ARRAYS INITIALISIEREN
	$shipping_net=array();
	$shipping_net_comp=array();
	for($i=0;$i<count($_POST["shops_selected"]);$i++)
	{
		$shipping_net[$_POST["shops_selected"][$i]]=0;
		$shipping_net_comp[$_POST["shops_selected"][$i]]=0;
	}
	
	$bill_adr_id=array();
	$res=q("SELECT adr_id FROM shop_bill_adr WHERE country_id IN (".implode(",", $_POST["shop_countries"]).");", $dbshop, __FILE__, __LINE__);
	while($shop_bill_adr=mysqli_fetch_assoc($res))
	{
		$bill_adr_id[$shop_bill_adr["adr_id"]]=0;
	}
	
	if($_POST["time_type"]=="order")
		$res=q("SELECT id_order,shop_id,customer_id, shipping_net, Currency_Code, bill_adr_id, ship_adr_id FROM shop_orders WHERE status_id!=4 AND firstmod>".$_POST["date_from"]." AND firstmod<".$_POST["date_to"]." AND shop_id IN (".implode(",", $_POST["shops_selected"]).") AND shipping_net>0;", $dbshop, __FILE__, __LINE__);
	else if($_POST["time_type"]=="idims")
		$res=q("SELECT id_order,shop_id,customer_id, shipping_net, Currency_Code, bill_adr_id, ship_adr_id FROM shop_orders WHERE status_id!=4 AND auf_id_date>".$_POST["date_from"]." AND auf_id_date<".$_POST["date_to"]." AND shop_id IN (".implode(",", $_POST["shops_selected"]).") AND shipping_net>0;", $dbshop, __FILE__, __LINE__);	
	if(mysqli_num_rows($res)>0)
	{
		while($shop_orders=mysqli_fetch_assoc($res))
		{
			if($_POST["shop_countries"][0]==0 or isset($bill_adr_id[$shop_orders["bill_adr_id"]]))
			{
				$order_shipping_net=$shop_orders["shipping_net"];
				$exchange_rate_to_EUR=1;
				$gewerblich=gewerblich($shop_orders["customer_id"]);
				if($shop_orders["Currency_Code"]!="" and $shop_orders["Currency_Code"]!="EUR")
				{
					$res2=q("SELECT exchange_rate_to_EUR FROM shop_orders_items WHERE order_id=".$shop_orders["id_order"].";", $dbshop, __FILE__, __LINE__);
					if(mysqli_num_rows($res2)>0)
					{
						$shop_orders_items=mysqli_fetch_array($res2);
						$exchange_rate_to_EUR=$shop_orders_items["exchange_rate_to_EUR"];
					}
				}
				$order_shipping_net=round(($order_shipping_net/$exchange_rate_to_EUR), 2);
				if($gewerblich)
				{
					$country=0;
					if($shop_orders["ship_adr_id"]>0) $adr_id=$shop_orders["ship_adr_id"];
					else $adr_id=$shop_orders["bill_adr_id"];
					$res3=q("SELECT country_id FROM shop_bill_adr WHERE adr_id=".$adr_id.";", $dbshop, __FILE__, __LINE__);
					if(mysqli_num_rows($res3)>0)
					{
						$shop_bill_adr=mysqli_fetch_assoc($res3);
						$country=$shop_bill_adr["country_id"];
					}
					else
					{
						if($shop_orders["ship_country_code"]=="DE")
							$country=1;
						if($shop_orders["ship_country_code"]=="" and $shop_orders["bill_country_code"]=="DE")
							$country=1;	
					}
					
					if($country==1)
					{
						$order_sum=0;
						$res4=q("SELECT netto, exchange_rate_to_EUR, amount FROM shop_orders_items WHERE order_id=".$shop_orders["id_order"].";", $dbshop, __FILE__, __LINE__);
						while($shop_orders_items=mysqli_fetch_assoc($res4))
						{
							$order_sum+=round(($shop_orders_items["netto"]/$shop_orders_items["exchange_rate_to_EUR"])*$shop_orders_items["amount"], 2);
							if($order_sum>=150)
							{
								$order_shipping_net=0;
								break;
							}
						}
					}
						
				}
				$shipping_net[$shop_orders["shop_id"]]+=$order_shipping_net;
			}
		}
	}
	
	if($_POST["mode"]=="comp")
	{
		if($_POST["time_type"]=="order")
		$res=q("SELECT id_order,shop_id,customer_id, shipping_net, Currency_Code, bill_adr_id, ship_adr_id FROM shop_orders WHERE status_id!=4 AND firstmod>".$_POST["date_comp_from"]." AND firstmod<".$_POST["date_comp_to"]." AND shop_id IN (".implode(",", $_POST["shops_selected"]).") AND shipping_net>0;", $dbshop, __FILE__, __LINE__);
	else if($_POST["time_type"]=="idims")
		$res=q("SELECT id_order,shop_id,customer_id, shipping_net, Currency_Code, bill_adr_id, ship_adr_id FROM shop_orders WHERE status_id!=4 AND auf_id_date>".$_POST["date_comp_from"]." AND auf_id_date<".$_POST["date_comp_to"]." AND shop_id IN (".implode(",", $_POST["shops_selected"]).") AND shipping_net>0;", $dbshop, __FILE__, __LINE__);	
		if(mysqli_num_rows($res)>0)
		{
			while($shop_orders=mysqli_fetch_array($res))
			{
				if($_POST["shop_countries"][0]==0 or isset($bill_adr_id[$shop_orders["bill_adr_id"]]))
				{
					$order_shipping_net=$shop_orders["shipping_net"];
					$exchange_rate_to_EUR=1;
					$gewerblich=gewerblich($shop_orders["customer_id"]);
					if($shop_orders["Currency_Code"]!="" and $shop_orders["Currency_Code"]!="EUR")
					{
						$res2=q("SELECT exchange_rate_to_EUR FROM shop_orders_items WHERE order_id=".$shop_orders["id_order"].";", $dbshop, __FILE__, __LINE__);
						if(mysqli_num_rows($res2)>0)
						{
							$shop_orders_items=mysqli_fetch_assoc($res2);
							$exchange_rate_to_EUR=$shop_orders_items["exchange_rate_to_EUR"];
						}
					}
					$order_shipping_net=round(($order_shipping_net/$exchange_rate_to_EUR), 2);
					if($gewerblich)
					{
						$country=0;
						if($shop_orders["ship_adr_id"]>0) $adr_id=$shop_orders["ship_adr_id"];
						else $adr_id=$shop_orders["bill_adr_id"];
						$res3=q("SELECT country_id FROM shop_bill_adr WHERE adr_id=".$adr_id.";", $dbshop, __FILE__, __LINE__);
						if(mysqli_num_rows($res3)>0)
						{
							$shop_bill_adr=mysqli_fetch_assoc($res3);
							$country=$shop_bill_adr["country_id"];
						}
						else
						{
							if($shop_orders["ship_country_code"]=="DE")
								$country=1;
							if($shop_orders["ship_country_code"]=="" and $shop_orders["bill_country_code"]=="DE")
								$country=1;	
						}
						
						if($country==1)
						{
							$order_sum=0;
							$res4=q("SELECT netto, exchange_rate_to_EUR, amount FROM shop_orders_items WHERE order_id=".$shop_orders["id_order"].";", $dbshop, __FILE__, __LINE__);
							while($shop_orders_items=mysqli_fetch_assoc($res4))
							{
								$order_sum+=round(($shop_orders_items["netto"]/$shop_orders_items["exchange_rate_to_EUR"])*$shop_orders_items["amount"], 2);
								if($order_sum>=150)
								{
									$order_shipping_net=0;
									break;
								}
							}
						}
							
					}
					$shipping_net_comp[$shop_orders["shop_id"]]+=$order_shipping_net;
				}
			}
		}
	}
	
	foreach($shipping_net as $key => $value){
		$xml.='<shop>'."\n";
		$xml.='		<shop_id><![CDATA['.$key.']]></shop_id>'."\n";
		$xml.='		<shop_shipping_net><![CDATA['.$value.']]></shop_shipping_net>'."\n";
		$xml.='</shop>'."\n";
	}
	
	foreach($shipping_net_comp as $key => $value){
		$xml.='<shop_comp>'."\n";
		$xml.='		<shop_id><![CDATA['.$key.']]></shop_id>'."\n";
		$xml.='		<shop_shipping_net><![CDATA['.$value.']]></shop_shipping_net>'."\n";
		$xml.='</shop_comp>'."\n";
	}
	
	echo $xml;
	
?>