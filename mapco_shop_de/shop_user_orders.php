<?php
	include("config.php");
	$login_required=true;
	include("templates/".TEMPLATE."/header.php");
	include("templates/".TEMPLATE."/cms_leftcolumn.php");

	//echo '<div id="mid_column">';
	echo '<div id="mid_right_column">';

	//PATH
	echo '<p>';
	echo '<a href="'.PATHLANG.'online-shop/mein-konto/">Mein Konto</a>';
	echo ' > '.t("Bestellungen");
	echo '</p>';


	echo '<h1>'.t("Meine Bestellungen").'</h1>';
	echo '<table class="hover">';
	echo '<tr>';
	echo '	<th>'.t("Bestellnummer").'</th>';
	echo '	<th>'.t("Datum").'</th>';
	echo '	<th>'.t("Gesamtpreis").'</th>';
	echo '	<th>'.t("Optionen").'</th>';
	echo '	<th>'.t("Sendungsverfolgungsnr.").'</th>';
	echo '</tr>';
	$results=q("SELECT * FROM shop_orders WHERE customer_id='".$_SESSION["id_user"]."' ORDER BY firstmod DESC;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$total=0;
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
		$total+=$row["shipping_costs"]*((100+UST)/100);
		}
		else
		{
			$total+=$row["shipping_costs"];
		}
		echo '<tr>';
		echo '	<td>'.$row["id_order"].'</td>';
		echo '	<td>'.date("d-m-Y H:i", $row["firstmod"]).'</td>';
		echo '	<td>€ '.number_format($total, 2).'</td>';
		echo '	<td><a href="'.PATHLANG.'online-shop/bestellung/'.$row["id_order"].'/">'.t("Rechnung anzeigen").'</a></td>';
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