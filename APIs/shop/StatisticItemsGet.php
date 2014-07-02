<?php
	
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
	$shop_sql="";
	$order_shop_cnt=array();
	$order_shop_cnt2=array();
	
	for($i=0;$i<count($_POST["shops_selected"]);$i++)
	{
		$order_shop_cnt[$_POST["shops_selected"][$i]]=0;
		$order_shop_cnt2[$_POST["shops_selected"][$i]]=0;
		
		if($i<count($_POST["shops_selected"])-1)
			$shop_sql.=$_POST["shops_selected"][$i].", ";
		else
			$shop_sql.=$_POST["shops_selected"][$i];
	}
	
	$order_ids=array();
	$order_shops=array();
	$order_customer=array();

	$cnt=0;
	
	$bill_adr_id=array();
	$res=q("SELECT adr_id FROM shop_bill_adr WHERE country_id IN (".implode(",", $_POST["shop_countries"]).");", $dbshop, __FILE__, __LINE__);
	while($shop_bill_adr=mysqli_fetch_assoc($res))
	{
		$bill_adr_id[$shop_bill_adr["adr_id"]]=0;
	}
	
	if($_POST["time_type"]=="order")
		$res=q("SELECT id_order,shop_id,customer_id,bill_adr_id FROM shop_orders WHERE status_id!=4 AND firstmod>".$_POST["date_from"]." AND firstmod<".$_POST["date_to"]." AND shop_id IN (".$shop_sql.");", $dbshop, __FILE__, __LINE__);
	else if($_POST["time_type"]=="idims")
		$res=q("SELECT id_order,shop_id,customer_id,bill_adr_id FROM shop_orders WHERE status_id!=4 AND invoice_date>".$_POST["date_from"]." AND invoice_date<".$_POST["date_to"]." AND shop_id IN (".$shop_sql.");", $dbshop, __FILE__, __LINE__);
	
	if(mysqli_num_rows($res)>0)
	{
		while($res_order=mysqli_fetch_array($res))
		{
			if($_POST["shop_countries"][0]==0 or isset($bill_adr_id[$res_order["bill_adr_id"]]))
			{
				$order_shop_cnt[$res_order["shop_id"]]=$order_shop_cnt[$res_order["shop_id"]]+1;
				
				$order_ids[$res_order["id_order"]]=$res_order["id_order"];
				$order_shops[$res_order["id_order"]]["shop_id"]=$res_order["shop_id"];
				$order_customer[$res_order["id_order"]]=$res_order["customer_id"];
				$cnt++;
			}
		}
	}
	if($cnt==0)
	{
		$xml.="<orders_number_single>".$cnt."</orders_number_single>\n";
		for($i=0;$i<count($_POST["shops_selected"]);$i++)
		{
			$xml.="<orders_number_".$_POST["shops_selected"][$i]."_single>".$order_shop_cnt[$_POST["shops_selected"][$i]]."</orders_number_".$_POST["shops_selected"][$i]."_single>\n";
		}
		$xml.="<order_items_number_single>".$cnt."</order_items_number_single>\n";
		if($_POST["mode"]=="single")
			echo $xml;
	}
	
	if($cnt>0)
	{
		//Order item "Block" ausschneiden
		$order_items=array();
		//$res2=q("SELECT * FROM shop_orders_items WHERE order_id BETWEEN ".min($order_ids)." AND ".max($order_ids).";", $dbshop, __FILE__, __LINE__);
		$res2=q("SELECT * FROM shop_orders_items WHERE order_id IN (".implode(",", $order_ids).");", $dbshop, __FILE__, __LINE__);
		if(mysqli_num_rows($res2)>0)
		{
			$cnt2=0;
			while($res_item=mysqli_fetch_array($res2))
			{
				//$order_items[$cnt2]=$res_item["order_id"];
				if(isset($order_ids[$res_item["order_id"]]) && $res_item["item_id"]!=0)
				{
					$res3=q("SELECT title FROM shop_items_de WHERE id_item=".$res_item["item_id"].";",$dbshop, __FILE__, __LINE__);
					if(mysqli_num_rows($res3)==1)
					{
						$result=mysqli_fetch_array($res3);
						$title=$result["title"];
					}
					else
						$title="";
						
					$res4=q("SELECT MPN,menuitem_id,GART FROM shop_items WHERE id_item=".$res_item["item_id"].";",$dbshop, __FILE__, __LINE__);
					if(mysqli_num_rows($res4)==1)
					{
						$result2=mysqli_fetch_array($res4);
						$mpn=			$result2["MPN"];
						$gart=			$result2["GART"];
						$menuitem_id=	$result2["menuitem_id"];
					}
					else
					{
						$mpn=			"";
						$gart=			0;
						$menuitem_id=	0;
					}
					
					$res5=q("SELECT keyword FROM shop_items_keywords WHERE GART=".$gart." AND language_id='de' AND ordering=1;",$dbshop, __FILE__, __LINE__);
					if(mysqli_num_rows($res5)>0)
					{
						$result3=mysqli_fetch_array($res5);
						$keyword=			$result3["keyword"];
					}
					else
					{
						$keyword=			"";
					}
						
					$xml.="	<item>\n";
					$xml.="		<order_id>".$res_item["order_id"]."</order_id>\n";
					$xml.="		<item_id>".$res_item["item_id"]."</item_id>\n";
					$xml.="		<customer_id>".$order_customer[$res_item["order_id"]]."</customer_id>\n";
					$xml.="		<MPN><![CDATA[".$mpn."]]></MPN>\n";
					$xml.="		<GART><![CDATA[".$gart."]]></GART>\n";
					$xml.="		<keyword><![CDATA[".$keyword."]]></keyword>\n";
					$xml.="		<menuitem_id>".$menuitem_id."</menuitem_id>\n";
					$xml.="		<title><![CDATA[".$title."]]></title>\n";
					$xml.="		<shop_id>".$order_shops[$res_item["order_id"]]["shop_id"]."</shop_id>\n";
					$xml.="		<amount>".$res_item["amount"]."</amount>\n";
					$xml.="		<netto>".$res_item["netto"]."</netto>\n";
					$xml.="		<Currency_Code><![CDATA[".$res_item["Currency_Code"]."]]></Currency_Code>\n";
					$xml.="		<exchange_rate_to_EUR>".$res_item["exchange_rate_to_EUR"]."</exchange_rate_to_EUR>\n";
					$xml.="		<time_range><![CDATA[single]]></time_range>\n";
					$xml.="	</item>\n";
					$cnt2=$cnt2+1;
				}
			}
		}
		
		$xml.="<orders_number_single>".$cnt."</orders_number_single>\n";
		for($i=0;$i<count($_POST["shops_selected"]);$i++)
		{
			$xml.="<orders_number_".$_POST["shops_selected"][$i]."_single>".$order_shop_cnt[$_POST["shops_selected"][$i]]."</orders_number_".$_POST["shops_selected"][$i]."_single>\n";
		}
		$xml.="<order_items_number_single>".$cnt2."</order_items_number_single>\n";
		if($_POST["mode"]=="single")
			echo $xml;
	}
	
	if($_POST["mode"]=="comp")
	{	
		$order_ids=array();
		$order_shops=array();
		$order_customer=array();
		$cnt=0;
		if($_POST["time_type"]=="order")
			$res=q("SELECT id_order,shop_id,customer_id,bill_adr_id FROM shop_orders WHERE status_id!=4 AND firstmod>".$_POST["date_comp_from"]." AND firstmod<".$_POST["date_comp_to"]." AND shop_id IN (".$shop_sql.");", $dbshop, __FILE__, __LINE__);
		else if($_POST["time_type"]=="idims")
			$res=q("SELECT id_order,shop_id,customer_id,bill_adr_id FROM shop_orders WHERE status_id!=4 AND invoice_date>".$_POST["date_comp_from"]." AND invoice_date<".$_POST["date_comp_to"]." AND shop_id IN (".$shop_sql.");", $dbshop, __FILE__, __LINE__);
		if(mysqli_num_rows($res)>0)
		{
			while($res_order=mysqli_fetch_array($res))
			{
				if($_POST["shop_countries"][0]==0 or isset($bill_adr_id[$res_order["bill_adr_id"]]))
				{
					$order_shop_cnt2[$res_order["shop_id"]]=$order_shop_cnt2[$res_order["shop_id"]]+1;
				
					$order_ids[$res_order["id_order"]]=$res_order["id_order"];
					$order_shops[$res_order["id_order"]]["shop_id"]=$res_order["shop_id"];
					$order_customer[$res_order["id_order"]]=$res_order["customer_id"];
					$cnt++;
				}
			}
		}
		if($cnt==0)
		{
			$xml.="<orders_number_comp>".$cnt."</orders_number_comp>\n";
			for($i=0;$i<count($_POST["shops_selected"]);$i++)
			{
				$xml.="<orders_number_".$_POST["shops_selected"][$i]."_comp>".$order_shop_cnt2[$_POST["shops_selected"][$i]]."</orders_number_".$_POST["shops_selected"][$i]."_comp>\n";
			}
			$xml.="<order_items_number_comp>".$cnt."</order_items_number_comp>\n";
			echo $xml;
		}
		
		if($cnt>0)
		{
			//Order item "Block" ausschneiden
			$order_items=array();
			//$res2=q("SELECT * FROM shop_orders_items WHERE order_id BETWEEN ".min($order_ids)." AND ".max($order_ids).";", $dbshop, __FILE__, __LINE__);
			$res2=q("SELECT * FROM shop_orders_items WHERE order_id IN (".implode(",", $order_ids).");", $dbshop, __FILE__, __LINE__);
			if(mysqli_num_rows($res2)>0)
			{
				$cnt2=0;
				while($res_item=mysqli_fetch_array($res2))
				{
					//$order_items[$cnt2]=$res_item["order_id"];
					if(isset($order_ids[$res_item["order_id"]]) && $res_item["item_id"]!=0)
					{
						$res3=q("SELECT title FROM shop_items_de WHERE id_item=".$res_item["item_id"].";",$dbshop, __FILE__, __LINE__);
						if(mysqli_num_rows($res3)==1)
						{
							$result=mysqli_fetch_array($res3);
							$title=$result["title"];
						}
						else
							$title="";
							
						$res4=q("SELECT MPN,menuitem_id,GART FROM shop_items WHERE id_item=".$res_item["item_id"].";",$dbshop, __FILE__, __LINE__);
						if(mysqli_num_rows($res4)==1)
						{
							$result2=mysqli_fetch_array($res4);
							$mpn=			$result2["MPN"];
							$gart=			$result2["GART"];
							$menuitem_id=	$result2["menuitem_id"];
						}
						else
						{
							$mpn=			"";
							$gart=			0;
							$menuitem_id=	0;
						}
						
						$res5=q("SELECT keyword FROM shop_items_keywords WHERE GART=".$gart." AND language_id='de' AND ordering=1;",$dbshop, __FILE__, __LINE__);
						if(mysqli_num_rows($res5)>0)
						{
							$result3=mysqli_fetch_array($res5);
							$keyword=			$result3["keyword"];
						}
						else
						{
							$keyword=			"";
						}
							
						$xml.="	<item>\n";
						$xml.="		<order_id>".$res_item["order_id"]."</order_id>\n";
						$xml.="		<item_id>".$res_item["item_id"]."</item_id>\n";
						$xml.="		<customer_id>".$order_customer[$res_item["order_id"]]."</customer_id>\n";
						$xml.="		<MPN><![CDATA[".$mpn."]]></MPN>\n";
						$xml.="		<GART><![CDATA[".$gart."]]></GART>\n";
						$xml.="		<keyword><![CDATA[".$keyword."]]></keyword>\n";
						$xml.="		<menuitem_id>".$menuitem_id."</menuitem_id>\n";
						$xml.="		<title><![CDATA[".$title."]]></title>\n";
						$xml.="		<shop_id>".$order_shops[$res_item["order_id"]]["shop_id"]."</shop_id>\n";
						$xml.="		<amount>".$res_item["amount"]."</amount>\n";
						$xml.="		<netto>".$res_item["netto"]."</netto>\n";
						$xml.="		<Currency_Code><![CDATA[".$res_item["Currency_Code"]."]]></Currency_Code>\n";
						$xml.="		<exchange_rate_to_EUR>".$res_item["exchange_rate_to_EUR"]."</exchange_rate_to_EUR>\n";
						$xml.="		<time_range><![CDATA[comp]]></time_range>\n";
						$xml.="	</item>\n";
						$cnt2=$cnt2+1;
					}
				}
			}
			
			$xml.="<orders_number_comp>".$cnt."</orders_number_comp>\n";
			for($i=0;$i<count($_POST["shops_selected"]);$i++)
			{
				$xml.="<orders_number_".$_POST["shops_selected"][$i]."_comp>".$order_shop_cnt2[$_POST["shops_selected"][$i]]."</orders_number_".$_POST["shops_selected"][$i]."_comp>\n";
			}
			$xml.="<order_items_number_comp>".$cnt2."</order_items_number_comp>\n";
			echo $xml;
		}
	}
	
?>