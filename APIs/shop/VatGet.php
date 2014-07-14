<?php

	/*
	*	SOA 2
	*/
	
	include( '../functions/mapco_gewerblich.php' );
	
	$required = array( 'mode' => 'text' );
	
	check_man_params( $required );
	
	if ( $_POST['mode'] == 'order' )
	{
		$required = array( 'order_id' => 'numericNN' );
	
		check_man_params( $required );
	}
	
	if ( $_POST['mode'] == 'order' )
	{
		$post_data = 				array();
		$post_data['API'] = 		'shop';
		$post_data['APIRequest'] = 	'OrderDetailGet_neu_test';
		$post_data['OrderID'] = 	$_POST['order_id'];
		
		$response = soa2( $post_data, __FILE__, __LINE__ );
		
		$bill_adr_id = 	(int)$response->Order[0]->bill_adr_id[0];
		$customer_id = 	(int)$response->Order[0]->customer_id[0];
		$shop_id = 		(int)$response->Order[0]->shop_id[0];
		
		$gewerblich = gewerblich( $customer_id );
		
		// get country id
		$country_id = 1;
		if ( $bill_adr_id > 0 )
		{
			$res = q( "SELECT * FROM shop_bill_adr WHERE adr_id=" . $bill_adr_id, $dbshop, __FILE__, __LINE__ );
			if ( mysqli_num_rows( $res ) > 0 )
			{
				$shop_bill_adr = mysqli_fetch_assoc( $res );
				
				$country_id = $shop_bill_adr['country_id'];
			}
			else
			{
				show_error( 11367, 7, __FILE__, __LINE__, 'adr_id: ' .  $bill_adr_id );
			}
		}
		
		// get shop-VAT
		$vat = 		19;
		$vat_temp = 19;
		
		$res2 = q( "SELECT * FROM shop_shops WHERE id_shop=" . $shop_id, $dbshop, __FILE__, __LINE__ );
		if ( mysqli_num_rows( $res2 ) > 0 )
		{
			$shop_shops = mysqli_fetch_assoc( $res2 );
			
			$vat = 		$shop_shops['shop_VAT'];
			$vat_temp = $shop_shops['shop_VAT'];
		}
		else
		{
			show_error( 11368, 7, __FILE__, __LINE__, 'shop_id: ' . $shop_id );
		}
		
		//GET COUNTRY DATA AND SET VAT
		$eu = 1;
		if ( $country_id > 0 )
		{
			$res6 = q( "SELECT * FROM shop_countries WHERE id_country=" . $country_id, $dbshop, __FILE__, __LINE__ );
			if ( mysqli_num_rows( $res6 ) == 1 )
			{
				$shop_countries = 	mysqli_fetch_assoc( $res6 );
				$eu = 				$shop_countries["EU"];
				$vat_temp = 		$shop_countries["VAT"];
//					$country_id=$_SESSION["bill_country_id"];
			}
		}
		elseif(isset($_SESSION["origin"]))
		{
			$res7=q("SELECT * FROM shop_countries WHERE country_code='".$_SESSION["origin"]."'", $dbshop, __FILE__, __LINE__);
			if(mysqli_num_rows($res7)==1)
			{
				$shop_countries=mysqli_fetch_assoc($res7);
				$eu=$shop_countries["EU"];
				$vat_temp=$shop_countries["VAT"];
				$country_id=$shop_countries["id_country"];
			}
		}
		
		//autopartner und franchise Korrektur
		if( $_SESSION['id_shop'] == 2 or $_SESSION['id_shop'] == 4 or $_SESSION['id_shop'] == 6 )
		{
			$vat_temp = 19;
			if ( $country_id == 3) // ÖSTERREICH
			{
				$vat_temp = 20;
			}
		}
		if ( $_SESSION['id_shop'] == 21 )
		{
			$vat_temp = 19;
		}
		if ( $_SESSION[ 'id_shop' ] == 19 )
		{
			$vat_temp = 21;
		}
		if ( $_SESSION['id_shop'] == 20 )
		{
			$vat_temp = 20;
		}	
		
		if ( $gewerblich and $country_id != 1 )
		{
			$vat = 0;
		}
		elseif ( $gewerblich and $country_id == 1 )
		{
			$vat = $vat_temp;
		}
		elseif ( !$gewerblich and $eu == 1 )
		{
			$vat = $vat_temp;
		}
		elseif ( !$gewerblich and $eu == 0 )
		{
			$vat = 0;
		}
		
		echo '<vat>' . $vat . '</vat>' . "\n";			
	}

?>