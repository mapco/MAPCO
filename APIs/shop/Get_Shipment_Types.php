<?php

	if ( !isset($_POST["mode"]) )
	{
		echo '<Get_Shipment_TypesResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Ausgabemodus nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Modus für die Ausgabe der Versandmethoden angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</Get_Shipment_TypesResponse>'."\n";
		exit;
	}

	if ($_POST["mode"]=="all")
	{
		$xmldata='';
		
		$res=q("SELECT * FROM shop_shipping_types;", $dbshop, __FILE__, __LINE__);
		while ($row=mysqli_fetch_array($res))
		{
			$xmldata.="	<ShipmentType>\n";
			$xmldata.="		<id_shippingtype>".$row["id_shippingtype"]."</id_shippingtype>\n";
			$xmldata.="		<title><![CDATA[".$row["title"]."]]></title>\n";
			$xmldata.="		<description><![CDATA[".$row["description"]."]]></description>\n";
			$xmldata.="		<ShippingServiceType><![CDATA[".$row["ShippingServiceType"]."]]></ShippingServiceType>\n";
			$xmldata.="	</ShipmentType>\n";
		}
	}

	echo "<Get_Shipment_TypesResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo $xmldata;
	echo "</Get_Shipment_TypesResponse>";

?>
