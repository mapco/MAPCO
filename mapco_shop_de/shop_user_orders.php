<?php
	include("config.php");
	include("functions/cms_tl.php");
	$login_required=true;
	include("templates/".TEMPLATE."/header.php");
	include("modules/cms_leftcolumn.php");

	echo '<div id="mid_column">';

	//PATH
	echo 'aaa';
	echo '<p>';
//	echo '<a href="'.PATH.$_GET["lang"].'/online-shop/mein-konto/">Mein Konto</a>';
	echo '<a href="'.PATHLANG.$tl[301]["alias"].'" title="'.$tl[301]["description"].'">'.$tl[301]["title"].'</a>';
	echo ' > '.$tl[299]["title"];
	echo '</p>';


	echo '<h1>'.t("Meine Bestellungen").'</h1>';
	echo '<table class="hover">';
	echo '<tr>';
	echo '	<th>Bestellnummer</th>';
	echo '	<th>Datum</th>';
	echo '	<th>Gesamtpreis</th>';
	echo '	<th>Optionen</th>';
	echo '</tr>';
	$results=q("SELECT * FROM shop_orders WHERE customer_id='".$_SESSION["id_user"]."' ORDER BY firstmod DESC;", $dbshop, __FILE__, __LINE__);
	while($row=mysql_fetch_array($results))
	{
		$total=0;
		$results2=q("SELECT * FROM shop_orders_items WHERE order_id='".$row["id_order"]."';", $dbshop, __FILE__, __LINE__);
		while($row2=mysql_fetch_array($results2))
		{
			if (gewerblich($_SESSION["id_user"]))
			{	
			$total+=($row2["amount"]*$row2["price"])*((100+UST)/100);
			}
			else
			{
				$total+=$row2["amount"]*$row2["price"];
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
		echo '	<td><a href="'.PATH.$_GET["lang"].'/online-shop/bestellung/'.$row["id_order"].'/">Rechnung anzeigen</a></td>';
		echo '</tr>';
	}
	echo '</table>';

	echo '</div>';

	include("modules/cms_rightcolumn.php");
	include("templates/".TEMPLATE."/footer.php");
?>