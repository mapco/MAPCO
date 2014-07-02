<?php
	include("config.php");
	$login_required=true;
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_shop_index.php">Online-Shop</a>';
	echo ' > <a href="backend_shop_orders.php">Bestellungen</a>';
	echo ' > Bestellung Nr. '.$_GET["id_order"];
	echo '</p>';

	echo '<h1>'.t("Bestellung").' Nr. '.$_GET["id_order"].'</h1>';

	$results=q("SELECT * FROM shop_orders WHERE id_order=".$_GET["id_order"]." LIMIT 1;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		//customer number
		$results2=q("SELECT * FROM cms_users WHERE id_user=".$row["customer_id"].";", $dbweb, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$text = '<p>Kundennummer: '.$row2["username"].'</p>';
		
		//bill address
		$text .= '<p><b>Rechnungsanschrift:</b><br>';
		if ($row["bill_company"]!="") $text .= $row["bill_company"].'<br>';
		$text .= $row["bill_gender"].' '.$row["bill_title"].'<br>';
		$text .= $row["bill_firstname"].' '.$row["bill_lastname"].'<br>';
		$text .= $row["bill_street"].' '.$row["bill_number"].'<br>';
		$text .= $row["bill_zip"].' '.$row["bill_city"].'<br>';
		if ($row["bill_additional"]!="") $text .= $row["bill_additional"].'<br>';
		$text .= $row["bill_country"].'<br>';
		$text .= '</p>';

		//shipping address
		if ($row["ship_lastname"]!="")
		{
			$text .= '<p><b>Lieferanschrift:</b><br>';
			if ($row["ship_company"]!="") $text .= $row["ship_company"].'<br>';
			$text .= $row["ship_gender"].' '.$row["ship_title"].'<br>';
			$text .= $row["ship_firstname"].' '.$row["ship_lastname"].'<br>';
			$text .= $row["ship_street"].' '.$row["ship_number"].'<br>';
			$text .= $row["ship_zip"].' '.$row["ship_city"].'<br>';
			if ($row["ship_additional"]!="") $text .= $row["ship_additional"].'<br>';
			$text .= $row["ship_country"].'<br>';
			$text .= '</p>';
		}
		
		//bill
		$text .= '<table class="hover">';
		$text .= '  <tr><th colspan="4">Bestellung</th></tr>';
		$text .= '  <tr>';
		$text .= '    <td>Bezeichnung</td>';
		$text .= '    <td>Menge</td>';
		$text .= '    <td>EK</td>';
		$text .= '    <td>Gesamt</td>';
		$text .= '  </tr>';
		
		$results2=q("SELECT a.amount, a.netto, b.title FROM shop_orders_items AS a, shop_items_de AS b WHERE a.order_id=".$row["id_order"]." AND a.item_id=b.id_item;", $dbshop, __FILE__, __LINE__);
		$total=0;
		while($row2=mysqli_fetch_array($results2))
		{
			$text .= '  <tr>';
			$text .= '  <td>'.$row2["title"].'</td>';
			$text .= '  <td>'.number_format($row2["amount"], 2).'</td>';
			$text .= '  <td>€ '.number_format($row2["netto"], 2).'</td>';
			$price=$row2["amount"]*$row2["netto"];
			$total+=$price;
			$text .= '  <td>€ '.number_format($price, 2).'</td>';
			$text .= '  </tr>';
		}
		$text .= '  <tr>';
		$text .= '    <td colspan="3"><b>Summe Positionen</b></td><td><b>€ '.number_format($total, 2).'</b></td>';
		$text .= '  </tr>';
		$text .= '  <tr>';
		$text .= '    <td colspan="3">'.$row["shipping_details"].'</td><td>€ '.number_format($row["shipping_net"], 2).'</td>';
		$text .= '  </tr>';
		$total+=$row["shipping_net"];
		$ust=(UST/100)*$total;
		$text .= '  <tr>';
		$text .= '    <td colspan="3">gesetzliche Umsatzsteuer</td><td>€ '.number_format($ust, 2).'</td>';
		$text .= '  </tr>';
		$total=((100+UST)/100)*$total;
		$text .= '  <tr>';
		$text .= '    <td colspan="3"><b>Gesamtpreis</b></td><td><b>€ '.number_format($total, 2).'</b></td>';
		$text .= '  </tr>';
		$text .= '</table>';
		echo $text;
	}

	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>