<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	include("functions/shop_get_price.php");
?>

<script>
	$(document).ready(function()
	{
		$("#datepicker_followup").datepicker( { "dateFormat":"D dd.mm.yy" });
	});
	
	function update_customer(field, value)
	{
		var response=ajax("modules/backend_crm_customer_editor_update.php?id_user=<?php echo $_GET["id_user"]; ?>&field="+encodeURIComponent(field)+"&value="+encodeURIComponent(value));
		if (response!="") show_status(response);
		else
		{
			if (value=="") show_status("Feld erfolgreich geleert.");
			else
			{
				if (value.length>50) value=value.substring(0, 50)+'...';
				show_status('"'+value+'" erfolgreich gespeichert.');
			}
		}
	}
	function update_number(id_number, value, field)
	{
		var response=ajax("modules/backend_crm_customer_editor_update.php?id_number="+id_number+"&field="+field+"&value="+encodeURIComponent(value));
		if (response!="") show_status(response);
		else
		{
			if (value=="") show_status("Feld erfolgreich geleert."); else show_status(value+" erfolgreich gespeichert.");
		}
	}
</script>

<?php

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_crm_index.php">Kundenpflege</a>';
	echo ' > <a href="backend_crm_customers.php">Alle Kunden</a>';
	echo ' > Kunden-Editor';
	echo '</p>';


	//REMOVE
	if (isset($_POST["remove"]))
    {
		if ($_POST["id_user"]<=0) echo '<div class="failure">Es konnte keine ID für den Kunden gefunden werden!</div>';
		else
		{
			q("DELETE FROM crm_customers WHERE id_user=".$_POST["id_user"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
			echo '<div class="success">Kunden erfolgreich gelöscht!</div>';
		}
	}

	//LIST
	echo '<h1>';
	echo '&nbsp;<span style="display:inline; float:left;">Kundentool</span>';
	echo '<a href="backend_crm_customer_editor.php" title="Neuen Kunden anlegen"><img src="images/icons/24x24/page_add.png" alt="Neuen Kunden anlegen" title="Neuen Kunden anlegen" /></a>';
	echo '</h1>';
	
	echo '<div id="view"></div>';
	
	echo '<div style="float:left;">';

	if ($_GET["id_user"]>0)
	{
		$results=q("SELECT * FROM crm_customers WHERE id_user=".$_GET["id_user"].";", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
	}
	else
	{
		$results=q("SELECT * FROM crm_customers;", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$_GET["id_user"]=$row["id_user"];
	}
	
	//Name und Adresse
	echo '<table style="width:300px; margin:10px; float:left;">';
	echo '	<tr><th colspan="2">Adresse</th></tr>';
	echo '	<tr>';
	echo '		<td>Firma</td>';
	echo '		<td><input class="forminput" type="text" name="company" value="'.$row["company"].'" onchange="update_customer(this.name, this.value);" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Anrede</td>';
	echo '		<td>';
	echo '			<input class="forminput" type="text" name="salutation" value="'.$row["salutation"].'" onchange="update_customer(this.name, this.value);" />';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Vornamen</td>';
	echo '		<td>';
	echo '			<input class="forminput" type="text" name="firstname" value="'.$row["firstname"].'" onchange="update_customer(this.name, this.value);" />';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>2. Vorname</td>';
	echo '		<td>';
	echo '			<input class="forminput" type="text" name="middlename" value="'.$row["middlename"].'" onchange="update_customer(this.name, this.value);" />';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Nachname</td>';
	echo '		<td>';
	echo '			<input class="forminput" type="text" name="lastname" value="'.$row["lastname"].'" onchange="update_customer(this.name, this.value);" />';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Straße</td>';
	echo '		<td>';
	echo '			<input style="width:150px;" class="forminput" type="text" name="street" value="'.$row["street"].'" onchange="update_customer(this.name, this.value);" />';
	echo '			Nr. <input style="width:20px;" class="forminput" type="text" name="street_nr" value="'.$row["street_nr"].'" onchange="update_customer(this.name, this.value);" />';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>PLZ und Ort</td>';
	echo '		<td>';
	echo '			<input style="width:40px;" class="forminput" type="text" name="zip" value="'.$row["zip"].'" onchange="update_customer(this.name, this.value);" />';
	echo '			<input style="width:150px;" class="forminput" type="text" name="city" value="'.$row["city"].'" onchange="update_customer(this.name, this.value);" />';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Land</td>';
	echo '		<td>';
	echo '			<input class="forminput" type="text" name="country" value="'.$row["country"].'" onchange="update_customer(this.name, this.value);" />';
	echo '		</td>';
	echo '	</tr>';
	echo '</table>';
	
	//communication
	echo '<table style="width:250px; margin:10px; float:left;">';
	echo '	<tr><th colspan="2">Kommunikation</th></tr>';
	$results2=q("SELECT * FROM crm_numbers WHERE user_id=".$_GET["id_user"]." ORDER BY memo;", $dbweb, __FILE__, __LINE__);
	while($row2=mysqli_fetch_array($results2))
	{
		echo '	<tr>';
		echo '		<td><input style="width:150px;" class="forminput" type="text" name="'.$row2["id"].'" value="'.$row2["memo"].'" onchange="update_number(this.name, this.value, 0);" /></td>';
		echo '		<td><input class="forminput" type="text" name="'.$row2["id"].'" value="'.$row2["number"].'" onchange="update_number(this.name, this.value, 1);" /></td>';
		echo '	</tr>';
	}
	echo '</table>';

	//followup
	echo '<table style="width:275px; margin:10px; float:left;">';
	echo '	<tr><th colspan="2">Weiteres</th></tr>';
	echo '	<tr>';
	echo '		<td>Wiedervorlage</td>';
	echo '		<td><input class="forminput" id="datepicker_followup" type="text" name="followup" value="'.date("D d.m.Y", $row["followup"]).'" onchange="update_customer(this.name, this.value);" /></td>';
	echo '	</tr>';
	echo '</table>';
	
	
	//Informationsfeld
	echo '<br style="clear:both;" />';
	echo '<table style="margin:10px; float:left;" class="hover">';
	echo '	<tr><th colspan="2">Informationen zum Kunden</th></tr>';
	$row["memo"]=str_replace("<<", "\n", $row["memo"]);
	echo '<tr><td><textarea style="width:900px; height:500px;" class="forminput" name="memo" onchange="update_customer(this.name, this.value);">'.$row["memo"].'</textarea></td></tr>';
	echo '</table>';
	
	echo '</div>';
	echo '<div style="float:left;">';
	
	//TOP 50 SHOP ITEMS
	$item_id=array();
	$title=array();
	$amount=array();
	$price=array();
	$query="SELECT * FROM shop_orders AS a, shop_orders_items AS b WHERE a.customer_id=".$_GET["id_user"]." AND a.id_order=b.order_id;";
	$results=q($query, $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$query="SELECT * FROM shop_items AS a, shop_items_de AS b WHERE a.id_item=".$row["item_id"]." AND a.id_item=b.id_item;";
		$results2=q($query, $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$item_id[$row["item_id"]]=$row["item_id"];
		$title[$row["item_id"]]=$row2["title"];
		$amount[$row["item_id"]]+=$row["amount"];
		$price[$row["item_id"]]=get_price($row["item_id"]);
	}
	array_multisort($amount, SORT_DESC, $item_id, $title, $price);
	echo '<table style="width:400px; margin:10px; float:left;" class="hover">';
	echo '<tr><th colspan="5">Top Artikel</th></tr>';
	echo '<tr>';
	echo '	<th>Nr.</th>';
	echo '	<th>Artikel</th>';
	echo '	<th>Menge</th>';
	echo '	<th>Preis</th>';
	echo '	<th>Bestellen</th>';
	echo '</tr>';
	for($i=0; $i<sizeof($item_id); $i++)
	{
		echo '<tr>';
		echo '	<td>'.($i+1).'</td>';
		echo '	<td><a href="shop_item.php?id_item='.$item_id[$i].'">'.$title[$i].'</a></td>';
		echo '	<td>'.$amount[$i].'</td>';
		echo '	<td>€ '.number_format($price[$i], 2).'</td>';
		echo '	<td>';
		echo '		<form onsubmit="return backend_cart_add('.$item_id[$i].');">';
		echo '			<input style="width:30px;" id="article'.$item_id[$i].'" type="text" value="1" /></form>';
		echo '			<input type="image" src="images/icons/24x24/shopping_cart.png" onclick="return backend_cart_add('.$item_id[$i].');" alt="'.t("In den Warenkorb").'" title="'.t("In den Warenkorb").'" />';
		echo '	</form>';
		echo '	</td>';
		echo '</tr>';
	}
	/*
	$total*=1.19;
	echo '<tr><td colspan="3"><b>Gesamt Brutto</b></td><td><b>€ '.number_format($total, 2).'</b></td></tr>';
	*/
	echo '</table>';
	
	
	//ALL ORDERS
	echo '<br style="clear:both;" />';
	echo '<table style="width:400px; margin:10px; float:left;" class="hover">';
	echo '<tr><th colspan="4">Bestellungen</th></tr>';
	echo '<tr>';
	echo '	<th>Nr.</th>';
	echo '	<th>Bestellnummer</th>';
	echo '	<th>Datum</th>';
	echo '	<th>Gesamtpreis</th>';
	echo '</tr>';
	$total=0;
	$i=0;
	$query="SELECT * FROM shop_orders WHERE customer_id=".$_GET["id_user"]." ORDER BY firstmod DESC;";
	$results=q($query, $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$ordertotal=0;
		$results2=q("SELECT * FROM shop_orders_items WHERE order_id='".$row["id_order"]."';", $dbshop, __FILE__, __LINE__);
		while($row2=mysqli_fetch_array($results2))
		{
			$ordertotal+=$row2["price"]*$row2["amount"];
		}
		$total+=$ordertotal;
		echo '<tr>';
		$i++;
		echo '	<td>'.$i.'</td>';
		echo '	<td><a href="backend_shop_order.php?id_order='.$row["id_order"].'">'.$row["id_order"].'</a></td>';
		echo '	<td>'.date("d.m.Y H:i", $row["firstmod"]).'</td>';
		echo '	<td>€ '.number_format($ordertotal, 2).'</td>';
		echo '</tr>';
	}
	$total*=1.19;
	echo '<tr><td colspan="3"><b>Gesamt Brutto</b></td><td><b>€ '.number_format($total, 2).'</b></td></tr>';
	echo '</table>';
	
	echo '</div>';

	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>