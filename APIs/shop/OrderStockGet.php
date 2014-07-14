<?php

	$required = array();
	$required['order_id'] = 'numericNN';
	
	$stock = array();
	
	$lagerNr = array();
	if ( !isset( $_POST['LagerNr'] ) && $_POST['LagerNr'] != 0 && $_POST['LagerNr'] != "" )
	{
		$lager[] = $_POST['LagerNr'];
	}
	
	check_man_params( $required );
	
	//GET ALL ORDERITEMS
	$postfields = array();
	$postfields['API'] = 'shop';
	$postfields['APIRequest'] = 'OrderDetailGet_neu_test';
	$postfields['OrderID'] = $_POST['order_id'];
	
	$response = soa2( $postfields, __FILE__, __LINE__, 'obj');
	
	if ( (string)$response->Ack[0] != 'Success' )
	{
		//show_error();	
		exit;
	}

	//GET ITEMS
	$items = array();
	$index = 0;
	while ( isset( $response->Order[0]->OrderItems[0]->Item[$index] ) )
	{
		$items[] = (string)$response->Order[0]->OrderItems[0]->Item[$index]->OrderItemMPN[0];
		$index ++;
	}

	$lagerbezeichnung = array();
	//GET CENTRAL STOCK 
	$res_stock = q("SELECT * FROM lager WHERE ArtNr IN ('".implode("', '", $items)."')", $dbshop, __FILE__, __LINE__);
	while ( $row_stock = mysqli_fetch_assoc( $res_stock ) )
	{
		
		$stock[$row_stock['ArtNr']]['istbestand'] 	= $row_stock['ISTBESTAND'];
		$lagerbezeichnung['istbestand'] 			= 'Zentrallager'; 
		$stock[$row_stock['ArtNr']]['mocom'] 		= $row_stock['MOCOMBESTAND'];
		$lagerbezeichnung['mocom'] 					= 'MOCOM';
		$stock[$row_stock['ArtNr']]['online'] 		= $row_stock['ONLINEBESTAND'];
		$lagerbezeichnung['online'] 				= 'Online-lager';
		$stock[$row_stock['ArtNr']]['amamzon'] 		= $row_stock['AMAZONBESTAND'];
		$lagerbezeichnung['amazon'] 				= 'Amazon-Lager';
		$stock[$row_stock['ArtNr']]['verfuegbar'] 	= $row_stock['AMAZONBESTAND']+$row_stock['AMAZONBESTAND']+$row_stock['ONLINEBESTAND']+$row_stock['MOCOMBESTAND']+$row_stock['ISTBESTAND'];
		$lagerbezeichnung['verfuegbar'] 			= 'Verfügbar';
		
	}
	
	//GET RC STOCK
	if ( sizeof( $lagerNr ) == 0 )
	{
		$res_stock_rc = q("SELECT * FROM lagerrc WHERE ARTNR IN ('".implode("', '", $items)."')", $dbshop, __FILE__, __LINE__);
	}
	else
	{
		$res_stock_rc = q("SELECT * FROM lagerrc WHERE RCNR IN  ('".implode("', '", $lagerNr)."') AND ARTNR IN ('".implode("', '", $items)."')", $dbshop, __FILE__, __LINE__);
	}

	while ( $row_stock_rc = mysqli_fetch_assoc( $res_stock_rc ) )
	{
		$stock[$row_stock_rc['ARTNR']][$row_stock_rc['RCNR']] 	= $row_stock_rc['ISTBESTAND'];
		$lagerbezeichnung[$row_stock_rc['RCNR']]				= $row_stock_rc['RCBEZ'];		
	}
	

//CREATE OUTPUT
	foreach ( $stock as $MPN => $itemstock )
	{
		echo '<item MPN="'.$MPN.'">'."\n";
		foreach ( $itemstock as $lagerortNr => $bestand )
		{
			echo '	<stock nr="'.$lagerortNr.'" title="'.$lagerbezeichnung[$lagerortNr].'">'.$bestand.'</stock>'."\n";
		}
		echo '</item>';
	}

?>