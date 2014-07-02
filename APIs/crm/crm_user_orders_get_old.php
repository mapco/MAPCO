<?php

	$res=q("SELECT * FROM shop_orders WHERE customer_id = ".$_SESSION["id_user"]." AND firstmod > 1367393383 AND shipping_number = '' AND status_id = 1 ORDER BY firstmod DESC;", $dbshop, __FILE__, __LINE__);

	if (mysql_num_rows($res)==0)
	{
		echo "<crm_user_orders_getResponse>\n";
		echo "<Ack>Success</Ack>\n";
		echo "<order_items></order_items>\n";
		echo "</crm_user_orders_getResponse>";
		exit;
	/*	
		echo '<crm_get_order_detailResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>UserID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zur UserID konnte keine Bestellung gefunden werden</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_get_order_detailResponse>'."\n";
		exit;
	*/
	}
$i=0;
	$idorder = array(); //Order IDs besorgen
	while ($result=mysql_fetch_array($res))
	{
		$idorder[$i] = $result;	
		$i++;
	}
	
	$xmldata = "";
	for ($i=0; $i<sizeof($idorder); $i++)
//	foreach ($idorder as $ges) //Daten aus shop_order_items und shop_items_de besorgen
	{
		$ges=$idorder[$i]; 
		$res=q("SELECT * FROM shop_orders_items WHERE order_id = ".$ges["id_order"].";", $dbshop, __FILE__, __LINE__);
		while ($row=mysql_fetch_array($res))
		{
			$xmldata.='<item>';
			$xmldata.='<id>'.$row["id"].'</id>';
			$xmldata.='<order_id>'.$row["order_id"].'</order_id>';
			$xmldata.='<item_id>'.$row["item_id"].'</item_id>';
			$xmldata.='<amount>'.$row["amount"].'</amount>';
			$xmldata.='<price>'.$row["price"].'</price>';
			$xmldata.='<Currency_Code><![CDATA['.$row["Currency_Code"].']]></Currency_Code>';
			$xmldata.='<customer_vehicle_id>'.$row["customer_vehicle_id"].'</customer_vehicle_id>';
			
			$res2=q("SELECT * FROM shop_items_de WHERE id_item = ".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
			$row2=mysql_fetch_array($res2);
			if (mysql_num_rows($res)!=0)
			{
				$xmldata.='<title><![CDATA['.$row2["title"].']]></title>';
			}
			else
			{
				$xmldata.='<title><![CDATA[No title found in DB]]></title>';
			}
			
		  	$xmldata.= '<firstmod>'.$ges["firstmod"].'</firstmod>';
			$xmldata.='</item>';
		}
//		echo $ges["id_order"];
//		echo "\n";
		
	}
	
	echo "<crm_user_orders_getResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "<order_items>".$xmldata."</order_items>\n";
	echo "</crm_user_orders_getResponse>";
	//echo 'Service ist da!';
?>