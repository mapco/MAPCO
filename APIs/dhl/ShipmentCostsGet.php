<?php
	if( !isset($_POST["shipping_type"]) or $_POST["shipping_type"]=="" )
	{
		echo '<ShipmentCostsGetResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Versandart nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Versandart (shipping_type) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ShipmentCostsGetResponse>'."\n";
		exit;
	}

	if( !isset($_POST["id_country"]) or $_POST["id_country"]=="" )
	{
		echo '<ShipmentCostsGetResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Land nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Land (id_country) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ShipmentCostsGetResponse>'."\n";
		exit;
	}
	$results=q("SELECT * FROM shop_countries WHERE id_country=".$_POST["id_country"].";", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<ShipmentCostsGetResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Land nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Das angegebene Land (id_country) konnte in der Ländertabelle (shop_countries) nicht gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ShipmentCostsGetResponse>'."\n";
		exit;
	}
	$country=mysqli_fetch_array($results);

	if( !isset($_POST["WeightInKG"]) or $_POST["WeightInKG"]=="" )
	{
		echo '<ShipmentCostsGetResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Paketgewicht nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Paketgewicht (WeightInKG) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ShipmentCostsGetResponse>'."\n";
		exit;
	}
	
	if( !isset($_POST["COD"])) $_POST["COD"]="";
	if( !isset($_POST["cargo"])) $_POST["cargo"]="";
	
	if($_POST["COD"]=="cod")
		$cod=3.6;
	else
		$cod=0;
	
	if($_POST["cargo"]=="cargo")
	{
		if($country["id_country"]==1)
			$cargo=5;
		else
			$cargo=20;
	}
	else
		$cargo=0;

	//DHL Germany
	if( $_POST["shipping_type"]==1 and $country["id_country"]==1 )
	{
		if( $_POST["WeightInKG"]<3 )
		{
			$price["our_net"]=2.90+$cod+$cargo;
			$price["our_gross"]=round($price["our_net"]*1.19, 2);
			$price["customer_gross"]=ceil($price["our_gross"])-.10;
			if( $price["customer_gross"]<$price["our_gross"] ) $price["customer_gross"]+=1;
			$price["customer_net"]=round($price["customer_gross"]/119*100, 2);
		}
		elseif( $_POST["WeightInKG"]<31.5 )
		{
			$price["our_net"]=3.29+$cod+$cargo;
			$price["our_gross"]=round($price["our_net"]*1.19, 2);
			$price["customer_gross"]=ceil($price["our_gross"])-.10;
			if( $price["customer_gross"]<$price["our_gross"] ) $price["customer_gross"]+=1;
			$price["customer_net"]=round($price["customer_gross"]/119*100, 2);
		}
		else
		{
			$price["our_net"]="???";
			$price["our_gross"]="???";
			$price["customer_gross"]="???";
			$price["customer_net"]="???";
		}
	}
	//DHL International Austria
	elseif( $_POST["shipping_type"]==1 and $country["id_country"]==3 )
	{
		$baseprice=8.23;
		$kiloprice=0.00;

		$price["our_net"]=$baseprice+$kiloprice*$_POST["WeightInKG"]+$cod+$cargo;
		$price["our_gross"]=round($price["our_net"]*1.19, 2);
		$price["customer_gross"]=ceil($price["our_gross"])-.10;
		if( $price["customer_gross"]<$price["our_gross"] ) $price["customer_gross"]+=1;
		$price["customer_net"]=round($price["customer_gross"]/119*100, 2);
	}
	//DHL International Belgium
	elseif( $_POST["shipping_type"]==1 and $country["id_country"]==18 )
	{
		$baseprice=8.23;
		$kiloprice=0.00;

		$price["our_net"]=$baseprice+$kiloprice*$_POST["WeightInKG"]+$cod+$cargo;
		$price["our_gross"]=round($price["our_net"]*1.19, 2);
		$price["customer_gross"]=ceil($price["our_gross"])-.10;
		if( $price["customer_gross"]<$price["our_gross"] ) $price["customer_gross"]+=1;
		$price["customer_net"]=round($price["customer_gross"]/119*100, 2);
	}
	//DHL International Czech Republic
	elseif( $_POST["shipping_type"]==1 and $country["id_country"]==39 )
	{
		$baseprice=9.15;
		$kiloprice=0.00;

		$price["our_net"]=$baseprice+$kiloprice*$_POST["WeightInKG"]+$cod+$cargo;
		$price["our_gross"]=round($price["our_net"]*1.19, 2);
		$price["customer_gross"]=ceil($price["our_gross"])-.10;
		if( $price["customer_gross"]<$price["our_gross"] ) $price["customer_gross"]+=1;
		$price["customer_net"]=round($price["customer_gross"]/119*100, 2);
	}
	//DHL International Denmark
	elseif( $_POST["shipping_type"]==1 and $country["id_country"]==15 )
	{
		$baseprice=8.23;
		$kiloprice=0.00;

		$price["our_net"]=$baseprice+$kiloprice*$_POST["WeightInKG"]+$cod+$cargo;
		$price["our_gross"]=round($price["our_net"]*1.19, 2);
		$price["customer_gross"]=ceil($price["our_gross"])-.10;
		if( $price["customer_gross"]<$price["our_gross"] ) $price["customer_gross"]+=1;
		$price["customer_net"]=round($price["customer_gross"]/119*100, 2);
	}
	//DHL International France
	elseif( $_POST["shipping_type"]==1 and $country["id_country"]==20 )
	{
		$baseprice=9.15;
		$kiloprice=0.00;

		$price["our_net"]=$baseprice+$kiloprice*$_POST["WeightInKG"]+$cod+$cargo;
		$price["our_gross"]=round($price["our_net"]*1.19, 2);
		$price["customer_gross"]=ceil($price["our_gross"])-.10;
		if( $price["customer_gross"]<$price["our_gross"] ) $price["customer_gross"]+=1;
		$price["customer_net"]=round($price["customer_gross"]/119*100, 2);
	}
	//DHL International Italy
	elseif( $_POST["shipping_type"]==1 and $country["id_country"]==13 )
	{
		$baseprice=9.15;
		$kiloprice=0.00;

		$price["our_net"]=$baseprice+$kiloprice*$_POST["WeightInKG"]+$cod+$cargo;
		$price["our_gross"]=round($price["our_net"]*1.19, 2);
		$price["customer_gross"]=ceil($price["our_gross"])-.10;
		if( $price["customer_gross"]<$price["our_gross"] ) $price["customer_gross"]+=1;
		$price["customer_net"]=round($price["customer_gross"]/119*100, 2);
	}
	//DHL International Kroatien
	elseif( $_POST["shipping_type"]==1 and $country["id_country"]==51 )
	{
		$baseprice=18.10;
		$kiloprice=0.00;

		$price["our_net"]=$baseprice+$kiloprice*$_POST["WeightInKG"]+$cod+$cargo;
		$price["our_gross"]=round($price["our_net"]*1.19, 2);
		$price["customer_gross"]=ceil($price["our_gross"])-.10;
		if( $price["customer_gross"]<$price["our_gross"] ) $price["customer_gross"]+=1;
		$price["customer_net"]=round($price["customer_gross"]/119*100, 2);
	}
	//DHL International Liechtenstein
	elseif( $_POST["shipping_type"]==1 and $country["id_country"]==43 )
	{
		$baseprice=12.90;
		$kiloprice=0.00;

		$price["our_net"]=$baseprice+$kiloprice*$_POST["WeightInKG"]+$cod+$cargo;
		$price["our_gross"]=round($price["our_net"]*1.19, 2);
		$price["customer_gross"]=ceil($price["our_gross"])-.10;
		if( $price["customer_gross"]<$price["our_gross"] ) $price["customer_gross"]+=1;
		$price["customer_net"]=round($price["customer_gross"]/119*100, 2);
	}
	//DHL International Luxembourg
	elseif( $_POST["shipping_type"]==1 and $country["id_country"]==19 )
	{
		$baseprice=8.23;
		$kiloprice=0.00;

		$price["our_net"]=$baseprice+$kiloprice*$_POST["WeightInKG"]+$cod+$cargo;
		$price["our_gross"]=round($price["our_net"]*1.19, 2);
		$price["customer_gross"]=ceil($price["our_gross"])-.10;
		if( $price["customer_gross"]<$price["our_gross"] ) $price["customer_gross"]+=1;
		$price["customer_net"]=round($price["customer_gross"]/119*100, 2);
	}
	//DHL International Netherlands
	elseif( $_POST["shipping_type"]==1 and $country["id_country"]==17 )
	{
		$baseprice=8.23;
		$kiloprice=0.00;

		$price["our_net"]=$baseprice+$kiloprice*$_POST["WeightInKG"]+$cod+$cargo;
		$price["our_gross"]=round($price["our_net"]*1.19, 2);
		$price["customer_gross"]=ceil($price["our_gross"])-.10;
		if( $price["customer_gross"]<$price["our_gross"] ) $price["customer_gross"]+=1;
		$price["customer_net"]=round($price["customer_gross"]/119*100, 2);
	}
	//DHL International Sweden
	elseif( $_POST["shipping_type"]==1 and $country["id_country"]==16 )
	{
		$baseprice=12.05;
		$kiloprice=0.00;

		$price["our_net"]=$baseprice+$kiloprice*$_POST["WeightInKG"]+$cod+$cargo;
		$price["our_gross"]=round($price["our_net"]*1.19, 2);
		$price["customer_gross"]=ceil($price["our_gross"])-.10;
		if( $price["customer_gross"]<$price["our_gross"] ) $price["customer_gross"]+=1;
		$price["customer_net"]=round($price["customer_gross"]/119*100, 2);
	}
	//DHL International Slovakia
	elseif( $_POST["shipping_type"]==1 and $country["id_country"]==37 )
	{
		$baseprice=9.95;
		$kiloprice=0.00;

		$price["our_net"]=$baseprice+$kiloprice*$_POST["WeightInKG"]+$cod+$cargo;
		$price["our_gross"]=round($price["our_net"]*1.19, 2);
		$price["customer_gross"]=ceil($price["our_gross"])-.10;
		if( $price["customer_gross"]<$price["our_gross"] ) $price["customer_gross"]+=1;
		$price["customer_net"]=round($price["customer_gross"]/119*100, 2);
	}
	//DHL International Spain
	elseif( $_POST["shipping_type"]==1 and $country["id_country"]==21 )
	{
		$baseprice=12.05;
		$kiloprice=0.00;

		$price["our_net"]=$baseprice+$kiloprice*$_POST["WeightInKG"]+$cod+$cargo;
		$price["our_gross"]=round($price["our_net"]*1.19, 2);
		$price["customer_gross"]=ceil($price["our_gross"])-.10;
		if( $price["customer_gross"]<$price["our_gross"] ) $price["customer_gross"]+=1;
		$price["customer_net"]=round($price["customer_gross"]/119*100, 2);
	}
	//DHL International Switzerland
	elseif( $_POST["shipping_type"]==1 and $country["id_country"]==2 )
	{
		$baseprice=13.75;
		$kiloprice=0.00;

		$price["our_net"]=$baseprice+$kiloprice*$_POST["WeightInKG"]+$cod+$cargo;
		$price["our_gross"]=round($price["our_net"]*1.19, 2);
		$price["customer_gross"]=ceil($price["our_gross"])-.10;
		if( $price["customer_gross"]<$price["our_gross"] ) $price["customer_gross"]+=1;
		$price["customer_net"]=round($price["customer_gross"]/119*100, 2);
	}
	//DHL International United Kingdom
	elseif( $_POST["shipping_type"]==1 and $country["id_country"]==29 )
	{
		$baseprice=9.15;
		$kiloprice=0.00;

		$price["our_net"]=$baseprice+$kiloprice*$_POST["WeightInKG"]+$cod+$cargo;
		$price["our_gross"]=round($price["our_net"]*1.19, 2);
		$price["customer_gross"]=ceil($price["our_gross"])-.10;
		if( $price["customer_gross"]<$price["our_gross"] ) $price["customer_gross"]+=1;
		$price["customer_net"]=round($price["customer_gross"]/119*100, 2);
	}
	//DHL International Zones
	elseif( $_POST["shipping_type"]==1 and $country["zone"]>0 )
	{
		$baseprice=array(1 => 12.99, 2 => 17.30, 3 => 28.00, 4 => 24.50, 5 => 26.00, 6 => 30.00);
		$kiloprice=array(1 => 0, 2 =>  0, 3 =>  1.50, 4 =>  5.00, 5 => 5.00,  6 =>  7.00);

		$price["our_net"]=$baseprice[$country["zone"]]+$kiloprice[$country["zone"]]*$_POST["WeightInKG"]+$cod+$cargo;
		$price["our_gross"]=round($price["our_net"]*1.19, 2);
		$price["customer_gross"]=ceil($price["our_gross"])-.10;
		if( $price["customer_gross"]<$price["our_gross"] ) $price["customer_gross"]+=1;
		$price["customer_net"]=round($price["customer_gross"]/119*100, 2);
	}
	//DHL Express Germany
	elseif( $_POST["shipping_type"]==2 and $_POST["id_country"]==1 )
	{
		$jfb=7.5;
		if( $_POST["WeightInKG"]<5 ) $baseprice=9.9;
		else if( $_POST["WeightInKG"]<10 ) $baseprice=12.9;
		else if( $_POST["WeightInKG"]<20 ) $baseprice=16.9;
		else if( $_POST["WeightInKG"]<30 ) $baseprice=19.9;
		else
		{
			$baseprice=19.9+($_POST["WeightInKG"]-30)*1.6;
		}
		
		//special rates
		if( $_POST["express8"]=="checked" ) $baseprice+=45;
		if( $_POST["express9"]=="checked" ) $baseprice+=15;
		if( $_POST["express10"]=="checked" ) $baseprice+=8;
		if( $_POST["express12"]=="checked" ) $baseprice+=1;
		if( $_POST["saturdayexpress"]=="checked" ) $baseprice+=10;
		if( $_POST["sundayexpress"]=="checked" ) $baseprice+=35;

		//final price
		$price["our_net"]=$baseprice*($jfb/100+1);
		$price["our_gross"]=round($price["our_net"]*1.19, 2);
		$price["customer_gross"]=ceil($price["our_gross"])-.10;
		if( $price["customer_gross"]<$price["our_gross"] ) $price["customer_gross"]+=1;
		$price["customer_net"]=round($price["customer_gross"]/119*100, 2);
	}
	else
	{
		echo '<ShipmentCostsGetResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Preis nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte kein gültiger Versandpreis ermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ShipmentCostsGetResponse>'."\n";
		exit;
	}
	
	echo '<ShipmentCostsGetResponse>';
	echo '	<Ack>Success</Ack>';
	echo '	<OurNetPrice>'.$price["our_net"].'</OurNetPrice>';
	echo '	<OurGrossPrice>'.$price["our_gross"].'</OurGrossPrice>';
	echo '	<CustomerNetPrice>'.$price["customer_net"].'</CustomerNetPrice>';
	echo '	<CustomerGrossPrice>'.$price["customer_gross"].'</CustomerGrossPrice>';
	echo '</ShipmentCostsGetResponse>';
	
?>