<?php
	include("config.php");
	$login_required=true;
	include("templates/".TEMPLATE."/header.php");
	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	include("functions/mapco_gewerblich.php");
	include("functions/cms_tl.php");

	//echo '<div id="mid_column">';
	echo '<div id="mid_right_column">';

	//PATH
	echo '<p>';
	echo '<a href="'.PATHLANG.tl(301, "alias").'" title="'.tl(301, "description").'">'.tl(301, "title").'</a>';
	echo ' > '.tl(299, "title");
	echo '</p>';

	// GET SHOP-ID'S
	$shop_ids = array();
	$res = q( "SELECT * FROM shop_shops WHERE id_shop=" . $_SESSION['id_shop'] . " OR parent_shop_id=" . $_SESSION['id_shop'], $dbshop, __FILE__, __LINE__ );
	while ( $shop_shops = mysqli_fetch_assoc( $res ) ) {
		$shop_ids[] = $shop_shops['id_shop'];
	}
	
	echo '<h1>'.t("Meine Bestellungen").'</h1>';
	echo '<p><span style="color: #004CFF">* Angebot</span><br /><span style="color: #FF0000">* nicht abgeschlossene Bestellung</span></p>';
	echo '<table class="hover">';
	echo '<tr>';
	echo '	<th>'.t("Bestellnummer").'</th>';
	echo '	<th>'.t("Datum").'</th>';
	echo '	<th>'.t("Gesamtpreis").'</th>';
	echo '	<th>'.t("Optionen").'</th>';
	echo '	<th>'.t("Sendungsverfolgungsnr.").'</th>';
	echo '</tr>';
//	$results=q("SELECT * FROM shop_orders WHERE customer_id='".$_SESSION["id_user"]."' ORDER BY firstmod DESC;", $dbshop, __FILE__, __LINE__);
	$results=q("SELECT * FROM shop_orders WHERE customer_id='".$_SESSION["id_user"]."' AND shop_id IN (" . implode( ',', $shop_ids ) . ") ORDER BY firstmod DESC;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$total=0;
/*		
		$results2=q("SELECT * FROM shop_orders_items WHERE order_id='".$row["id_order"]."';", $dbshop, __FILE__, __LINE__);
		while($row2=mysqli_fetch_array($results2))
		{
			if (gewerblich($_SESSION["id_user"]))
			{	
			$total+=($row2["amount"]*$row2["price"])*((100+UST)/100);
			$total+=($row2["amount"]*$row2["collateral"])*((100+UST)/100);
			}
			else
			{
				$total+=$row2["amount"]*$row2["price"];
				$total+=$row2["amount"]*$row2["collateral"];
			}
		}
		if (gewerblich($_SESSION["id_user"]))
		{	
//			$total+=$row["shipping_costs"]*((100+UST)/100);
			$total+=$row["shipping_net"];
		}
		else
		{
			$total+=$row["shipping_costs"];
		}
*/		

		$post_data = array();
		$post_data['API'] = 'shop';
		$post_data['APIRequest'] = 'OrderDetailGet_neu_test';
		$post_data['OrderID'] = $row['id_order'];
		
		$response = soa2( $post_data, __FILE__, __LINE__ );
		
		$total = (string)$response->Order[0]->orderTotalGrossFC[0];
		$total = (float)str_replace( ',', '.', $total );
		
		echo '<tr>';
		if ( $row['status_id'] == 0 and $row['ordertype_id'] == 5 ) //Angebot
		{
			echo '	<td style="color: #004CFF;">'.$row["id_order"].'</td>';
		}
		elseif ( $row['status_id'] == 0 and $row['ordertype_id'] != 5 ) // nicht abgeschlossene Bestellung
		{
			echo '	<td style="color: #FF0000;">'.$row["id_order"].'</td>';
		}
		else
		{
			echo '	<td>'.$row["id_order"].'</td>';
		}
		echo '	<td>'.date("d-m-Y H:i", $row["firstmod"]).'</td>';
		echo '	<td>€ '.number_format($total, 2).'</td>';
		if ( $row['status_id'] == 0 ) //Angebot oder nicht abgeschlossene Bestellung
		{
			echo '	<td><a href="'.PATHLANG.tl( 844, 'alias' ).$row["id_order"].'/">'.t("Zur Kasse").'</a></td>';
		}
		else
		{
			echo '	<td><a href="'.PATHLANG.tl( 827, 'alias' ).$row["id_order"].'/">'.t("Rechnung anzeigen").'</a></td>';
		}
		if($row["shipping_number"]=="")
		{
			echo '	<td></td>';
		}
		else
		{
			if($row["shipping_type_id"]==3 || $row["shipping_type_id"]==6)
			{
				echo '	<td><a href="https://tracking.dpd.de/cgi-bin/delistrack?pknr='.$row["shipping_number"].'" target="_blank">'.$row["shipping_number"].'</a></td>';
			}
			else if($row["shipping_type_id"]==1 || $row["shipping_type_id"]==2 || $row["shipping_type_id"]==5 || $row["shipping_type_id"]==7)
			{
				echo '	<td><a href="http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=de&idc='.$row["shipping_number"].'&rfn=&extendedSearch=true" target="_blank">'.$row["shipping_number"].'</a></td>';
			}
		}
		echo '</tr>';
	}
	echo '</table>';

	echo '</div>';

	//include("modules/cms_rightcolumn.php");
	include("templates/".TEMPLATE."/footer.php");
?>