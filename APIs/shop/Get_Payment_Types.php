<?php

	if ( !isset($_POST["mode"]) )
	{
		echo '<Get_Payment_TypesResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Ausgabemodus nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Modus für die Ausgabe der Zahlungstypen angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</Get_Payment_TypesResponse>'."\n";
		exit;
	}

	if ($_POST["mode"]=="all")
	{
		$xmldata='';
		
		$res=q("SELECT * FROM shop_payment_types;", $dbshop, __FILE__, __LINE__);
		while ($row=mysqli_fetch_array($res))
		{
			$xmldata.="	<PaymentType>\n";
			$xmldata.="		<id_paymenttype>".$row["id_paymenttype"]."</id_paymenttype>\n";
			$xmldata.="		<title><![CDATA[".$row["title"]."]]></title>\n";
			$xmldata.="		<description><![CDATA[".$row["description"]."]]></description>\n";
			$xmldata.="		<PaymentMethod>".$row["PaymentMethod"]."</PaymentMethod>\n";
			$xmldata.="		<method>".$row["id_paymenttype"]."</method>\n";
			$xmldata.="		<ZLG>".$row["ZLG"]."</ZLG>\n";
			$xmldata.="		<ship_at_once>".$row["ship_at_once"]."</ship_at_once>\n";
			$xmldata.="	</PaymentType>\n";
		}
	}

	echo "<Get_Payment_TypesResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo $xmldata;
	echo "</Get_Payment_TypesResponse>";

?>
