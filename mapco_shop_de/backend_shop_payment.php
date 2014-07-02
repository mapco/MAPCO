<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_shop_index.php">Online-Shop</a>';
	echo ' > Zahlungs- und Versandmöglichkeiten';
	echo '</p>';
?>

	<script>
		var id_country;
		var id_payment;
		var id_shipping;
		var country_edit_id;
		var country_remove_id;
		var payment_edit_id;
		var payment_remove_id;
		var shipping_edit_id;
		var shipping_remove_id;
		function view(country_id, payment_id, shipping_id)
		{
			id_country=country_id;
			id_payment=payment_id;
			id_shipping=shipping_id;
			response=ajax("modules/backend_shop_payment_view.php?id_country="+id_country+"&id_payment="+id_payment+"&id_shipping="+id_shipping, false);
			document.getElementById("view").innerHTML=response;
		}
		
		function country_order(country_id, direction)
		{
			response=ajax("modules/backend_shop_payment_country.php?country_id="+country_id+"&direction="+direction, false);
			if (response!="") show_status(response);
			else
			{
				view(id_country, id_payment, id_shipping);
			}
		}
		
		function country_add()
		{
			var country = document.getElementById("country_add_country").value;
			response=ajax("modules/backend_shop_payment_country.php?action=add&country="+encodeURIComponent(country), false);
			if (response!="") show_status(response);
			else
			{
				view(id_country, id_payment, id_shipping);
				document.getElementById("country_add_country").value="";
				showhide('country_add');
				hide('status');
			}
		}
		
		function country_edit()
		{
			var country = document.getElementById("country_edit_country").value;
			response=ajax("modules/backend_shop_payment_country.php?action=edit&id_country="+country_edit_id+"&country="+encodeURIComponent(country), false);
			if (response!="") show_status(response);
			else
			{
				view(id_country, id_payment, id_shipping);
				hide('status');
				showhide('country_edit');
			}
		}
		
		function country_remove()
		{
			response=ajax("modules/backend_shop_payment_country.php?action=remove&id_country="+country_remove_id, false);
			if (response!="") show_status(response);
			else
			{
				view(id_country, id_payment, id_shipping);
				showhide('country_remove');
			}
		}

		function country_cancel()
		{
			view(id_country, id_payment, id_shipping);
			document.getElementById("country_add_country").value="";
			document.getElementById("country_edit_country").value="";
			hide('country_add');
			hide('country_edit');
			hide('country_remove');
			hide('status');
		}

		function showhide(id)
		{
			var display=document.getElementById(id).style.display;
			if (display=="block")
			{
				document.getElementById(id).style.display="none";
			}
			else
			{
				document.getElementById(id).style.display="block";
			}
		}

		function hide(id)
		{
			document.getElementById(id).style.display="none";
		}

		function payment_order(payment_id, direction)
		{
			response=ajax("modules/backend_shop_payment_payment.php?payment_id="+payment_id+"&direction="+direction+"&id_country="+id_country, false);
			if (response!="") show_status(response);
			else
			{
				view(id_country, id_payment, id_shipping);
			}
		}
		
		function payment_add()
		{
			var payment = document.getElementById("payment_add_payment").value;
			var payment_memo = document.getElementById("payment_add_payment_memo").value;
			var paymenttype_id = document.getElementById("payment_add_paymenttype_id").value;
			var country_id = document.getElementById("payment_add_country_id").value;
			response=ajax("modules/backend_shop_payment_payment.php?action=add&payment="+encodeURIComponent(payment)+"&payment_memo="+encodeURIComponent(payment_memo)+"&paymenttype_id="+paymenttype_id+"&id_country="+country_id, false);
			if (response!="") show_status(response);
			else
			{
				view(id_country, id_payment, id_shipping);
				document.getElementById("payment_add_payment").value="";
				document.getElementById("payment_add_payment_memo").value="";
				showhide('payment_add');
				hide('status');
			}
		}
		
		function payment_edit()
		{
			var payment = document.getElementById("payment_edit_payment").value;
			var payment_memo = document.getElementById("payment_edit_payment_memo").value;
			var paymenttype_id = document.getElementById("payment_edit_paymenttype_id").value;
			var country_id = document.getElementById("payment_edit_country_id").value;
			response=ajax("modules/backend_shop_payment_payment.php?action=edit&id_payment="+payment_edit_id+"&payment="+encodeURIComponent(payment)+"&payment_memo="+encodeURIComponent(payment_memo)+"&paymenttype_id="+paymenttype_id+"&id_country="+country_id, false);
			if (response!="") show_status(response);
			else
			{
				view(id_country, id_payment, id_shipping);
				hide('status');
				showhide('payment_edit');
			}
		}
		
		function payment_remove()
		{
			response=ajax("modules/backend_shop_payment_payment.php?action=remove&id_payment="+payment_remove_id, false);
			if (response!="") show_status(response);
			else
			{
				view(id_country, id_payment, id_shipping);
				showhide('payment_remove');
			}
		}

		function payment_cancel()
		{
			view(id_country, id_payment, id_shipping);
			document.getElementById("payment_add_payment").value="";
			document.getElementById("payment_add_payment_memo").value="";
			document.getElementById("payment_edit_payment").value="";
			document.getElementById("payment_edit_payment_memo").value="";
			hide('payment_add');
			hide('payment_edit');
			hide('payment_remove');
			hide('status');
		}

		function shipping_order(shipping_id, direction)
		{
			response=ajax("modules/backend_shop_payment_shipping.php?shipping_id="+shipping_id+"&direction="+direction+"&id_payment="+id_payment, false);
			if (response!="") show_status(response);
			else
			{
				view(id_country, id_payment, id_shipping);
			}
		}
		
		function shipping_add()
		{
			var shipping = document.getElementById("shipping_add_shipping").value;
			var shipping_memo = document.getElementById("shipping_add_shipping_memo").value;
			var price = document.getElementById("shipping_add_price").value;
			var id_shippingtype = document.getElementById("shipping_add_shippingtype_id").value;
			response=ajax("modules/backend_shop_payment_shipping.php?action=add&shipping="+encodeURIComponent(shipping)+"&shipping_memo="+encodeURIComponent(shipping_memo)+"&price="+encodeURIComponent(price)+"&id_shippingtype="+id_shippingtype+"&id_payment="+id_payment, false);
			if (response!="") show_status(response);
			else
			{
				view(id_country, id_payment, id_shipping);
				document.getElementById("shipping_add_shipping").value="";
				document.getElementById("shipping_add_shipping_memo").value="";
				document.getElementById("shipping_add_price").value="";
				showhide('shipping_add');
				hide('status');
			}
		}
		
		function shipping_edit()
		{
			var shipping = document.getElementById("shipping_edit_shipping").value;
			var shipping_memo = document.getElementById("shipping_edit_shipping_memo").value;
			var price = document.getElementById("shipping_edit_price").value;
			var shippingtype_id = document.getElementById("shipping_edit_shippingtype_id").value;
			response=ajax("modules/backend_shop_payment_shipping.php?action=edit&id_shipping="+shipping_edit_id+"&shipping="+encodeURIComponent(shipping)+"&shipping_memo="+encodeURIComponent(shipping_memo)+"&price="+encodeURIComponent(price)+"&shippingtype_id="+shippingtype_id, false);
			if (response!="") show_status(response);
			else
			{
				view(id_country, id_payment, id_shipping);
				hide('status');
				showhide('shipping_edit');
			}
		}
		
		function shipping_remove()
		{
			response=ajax("modules/backend_shop_payment_shipping.php?action=remove&id_shipping="+shipping_remove_id, false);
			if (response!="") show_status(response);
			else
			{
				view(id_country, id_payment, id_shipping);
				showhide('shipping_remove');
			}
		}
		
		function shipping_cancel()
		{
			view(id_country, id_payment, id_shipping);
			document.getElementById("shipping_add_shipping").value="";
			document.getElementById("shipping_add_shipping_memo").value="";
			document.getElementById("shipping_add_price").value="";
			document.getElementById("shipping_edit_shipping").value="";
			document.getElementById("shipping_edit_shipping_memo").value="";
			document.getElementById("shipping_edit_price").value="";
			hide('shipping_add');
			hide('shipping_edit');
			hide('shipping_remove');
			hide('status');
		}

    </script>

<?php

	
	//HEADLINE
//	echo '<h1>Zahlungs- und Versandmöglichkeiten';
//	echo '<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/calculator_add.png" alt="Neue Zahlungsmöglichkeit anlegen" title="Neue Zahlungsmöglichkeit anlegen" onclick="popup(\'modules/backend_shop_payment_editor.php\', 500, 250);" />';
//	if (isset($_GET["id_payment"])) echo '<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/mail_add.png" alt="Neue Versandmöglichkeit anlegen" title="Neue Versandmöglichkeit anlegen" onclick="popup(\'modules/backend_shop_shipping_editor.php?id_payment='.$_GET["id_payment"].'\', 520, 290);" />';
//	echo '</h1>';


	//CREATE PAYMENT
	if (isset($_POST["pay_add"]))
    {
		if ($_POST["title"]=="") echo '<div class="failure">Der Titel darf nicht leer sein!</div>';
		else
        {
			q("INSERT INTO shop_payment (title, memo, firstmod, firstmod_user_id, lastmod, lastmod_user_id) VALUES('".addslashes(stripslashes($_POST["title"]))."', '".addslashes(stripslashes($_POST["memo"]))."', '".time()."', '".$_SESSION["id_user"]."', '".time()."', '".$_SESSION["id_user"]."');", $dbshop, __FILE__, __LINE__);
			$_GET["id_payment"]=mysqli_insert_id($dbshop);
			echo '<div class="success">Zahlungsmöglichkeit erfolgreich angelegt!</div>';
        }
	}


	//UPDATE PAYMENT
	if (isset($_POST["pay_update"]))
    {
		if ($_POST["id_payment"]<=0) echo '<div class="failure">Es konnte keine ID für die Zahlungsmöglichkeit gefunden werden!</div>';
		elseif ($_POST["title"]=="") echo '<div class="failure">Der Titel darf nicht leer sein!</div>';
		else
        {
			q("UPDATE shop_payment
						 SET title='".addslashes(stripslashes($_POST["title"]))."',
						 	 memo='".addslashes(stripslashes($_POST["memo"]))."',
						 	 lastmod='".time()."',
						 	 lastmod_user_id='".$_SESSION["id_user"]."'
						 WHERE id_payment=".$_POST["id_payment"].";", $dbshop, __FILE__, __LINE__);
			echo '<div class="success">Zahlungsmöglichkeit erfolgreich aktualisiert!</div>';
        }
    }

	//REMOVE PAYMENT
	if (isset($_POST["pay_remove"]))
    {
		if (!($_POST["id_payment"]>0)) echo '<div class="failure">Es konnte keine ID für die Zahlungsmöglichkeit gefunden werden!</div>';
		else
		{
			q("DELETE FROM shop_shipping WHERE payment_id=".$_POST["id_payment"].";", $dbshop, __FILE__, __LINE__);
			q("DELETE FROM shop_payment WHERE id_payment=".$_POST["id_payment"]." LIMIT 1;", $dbshop, __FILE__, __LINE__);
//			echo '<div class="success">Zahlungsmöglichkeit erfolgreich gelöscht!</div>';
		}
	}


	//CREATE SHIPPING
	if (isset($_POST["ship_add"]))
    {
		if ($_GET["id_payment"]<=0) echo '<div class="failure">Es konnte keine ID für die Zahlungsmöglichkeit gefunden werden!</div>';
		elseif ($_POST["title"]=="") echo '<div class="failure">Der Titel darf nicht leer sein!</div>';
		elseif ($_POST["price"]=="") echo '<div class="failure">Das Preisfeld darf nicht leer sein!</div>';
		else
        {
			if (!($_POST["ordering"]>0))
			{
				$results=q("SELECT * FROM shop_shipping WHERE payment_id=".$_GET["id_payment"].";", $dbshop, __FILE__, __LINE__);
				$_POST["ordering"]=mysqli_num_rows($results)+1;
			}
			q("INSERT INTO shop_shipping (title, memo, price, payment_id, ordering, firstmod, firstmod_user_id, lastmod, lastmod_user_id) VALUES('".addslashes(stripslashes($_POST["title"]))."', '".addslashes(stripslashes($_POST["memo"]))."', '".$_POST["price"]."', ".$_GET["id_payment"].", ".$_POST["ordering"].", '".time()."', '".$_SESSION["id_user"]."', '".time()."', '".$_SESSION["id_user"]."');", $dbshop, __FILE__, __LINE__);
			$_GET["id_shipping"]=mysqli_insert_id($dbshop);
			echo '<div class="success">Versandmöglichkeit erfolgreich angelegt!</div>';
        }
	}

	//UPDATE SHIPPING
	if (isset($_POST["ship_update"]))
    {
		if (!($_POST["id_shipping"]>0)) echo '<div class="failure">Es konnte keine ID für die Versandmöglichkeit gefunden werden!</div>';
		elseif ($_POST["title"]=="") echo '<div class="failure">Der Titel darf nicht leer sein!</div>';
		elseif ($_POST["price"]=="") echo '<div class="failure">Das Preisfeld darf nicht leer sein!</div>';
		else
        {
			q("UPDATE shop_shipping
						 SET title='".addslashes(stripslashes($_POST["title"]))."',
						 	 memo='".addslashes(stripslashes($_POST["memo"]))."',
						 	 price='".$_POST["price"]."',
						 	 ordering='".$_POST["ordering"]."',
						 	 lastmod='".time()."',
						 	 lastmod_user_id='".$_SESSION["id_user"]."'
						 WHERE id_shipping=".$_POST["id_shipping"].";", $dbshop, __FILE__, __LINE__);
			echo '<div class="success">Versandmöglichkeit erfolgreich aktualisiert!</div>';
        }
    }


	//REMOVE SHIPPING
	if (isset($_POST["ship_remove"]))
    {
		if (!($_POST["id_shipping"]>0)) echo '<div class="failure">Es konnte keine ID für die Versandmöglichkeit gefunden werden!</div>';
		else
		{
			q("DELETE FROM shop_shipping WHERE id_shipping=".$_POST["id_shipping"]." LIMIT 1;", $dbshop, __FILE__, __LINE__);
//			echo '<div class="success">Versandmöglichkeit erfolgreich gelöscht!</div>';
		}
	}


	//VIEW
	echo '<div id="view"></div>';
?>
	<script> view('', '', ''); </script>
<?php

	//COUNTRY ADD WINDOW
	echo '<div id="country_add" class="popup" style="display:none;">';
	echo '<table style="position:absolute; left:50%; top:50%; width:320px; height:150px; margin-left:-160px; margin-top:-75px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">Land hinzufügen</span>';
	echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="country_cancel();" alt="Schließen" title="Schließen" />';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Land</td>';
	echo '		<td>';
	echo '			<input id="country_add_country" type="text" name="country" value="" />';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">';
	echo '			<input class="formbutton" type="button" value="Speichern" onclick="country_add();">';
	echo '			<input class="formbutton" type="button" value="Abbrechen" onclick="country_cancel();" />';
	echo '		</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';
	
	//COUNTRY EDIT WINDOW
	echo '<div id="country_edit" class="popup" style="display:none;">';
	echo '<table style="position:absolute; left:50%; top:50%; width:320px; height:150px; margin-left:-160px; margin-top:-75px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">Land bearbeiten</span>';
	echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="country_cancel();" alt="Schließen" title="Schließen" />';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Land</td>';
	echo '		<td>';
	echo '			<input id="country_edit_country" type="text" name="country" value="" />';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">';
	echo '			<input class="formbutton" type="button" value="Speichern" onclick="country_edit();">';
	echo '			<input class="formbutton" type="button" value="Abbrechen" onclick="country_cancel();" />';
	echo '	</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';
	
	//COUNTRY REMOVE WINDOW
	echo '<div id="country_remove" class="popup" style="display:none;">';
	echo '<table style="position:absolute; left:50%; top:50%; width:320px; height:150px; margin-left:-160px; margin-top:-75px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">Land wirklich löschen?</span>';
	echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="country_cancel();" alt="Schließen" title="Schließen" />';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">Sind Sie sicher, dass Sie dieses Land löschen möchten?</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>';
	echo '			<input class="formbutton" type="button" value="Ja" onclick="country_remove();" />';
	echo '			<input class="formbutton" type="button" value="Nein" onclick="country_cancel();" />';
	echo '		</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';
	
	//PAYMENT ADD WINDOW
	echo '<div id="payment_add" class="popup" style="display:none;">';
	echo '<table style="position:absolute; left:50%; top:50%; width:320px; height:150px; margin-left:-160px; margin-top:-75px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">Zahlungsmöglichkeit hinzufügen</span>';
	echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="payment_cancel();" alt="Schließen" title="Schließen" />';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Land</td>';
	echo '		<td>';
	echo '			<select id="payment_add_country_id">';
	$results=q("SELECT * FROM shop_countries ORDER BY country;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '				<option value="'.$row["id_country"].'">'.$row["country"].'</option>';
	}
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Zahlungsmöglichkeit</td>';
	echo '		<td>';
	echo '			<input id="payment_add_payment" type="text" value="" />';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Memo</td>';
	echo '		<td>';
	echo '			<textarea style="width:300px; height:50px;" id="payment_add_payment_memo"></textarea>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Zahlungsart</td>';
	echo '		<td>';
	echo '			<select id="payment_add_paymenttype_id">';
	$results=q("SELECT * FROM shop_payment_types ORDER BY title;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '<option value="'.$row["id_paymenttype"].'">'.$row["title"].'</option>';
	}
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">';
	echo '			<input class="formbutton" type="button" value="Speichern" onclick="payment_add();">';
	echo '			<input class="formbutton" type="button" value="Abbrechen" onclick="payment_cancel();" />';
	echo '		</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';
	
	//PAYMENT EDIT WINDOW
	echo '<div id="payment_edit" class="popup" style="display:none;">';
	echo '<table style="position:absolute; left:50%; top:50%; width:320px; height:200px; margin-left:-160px; margin-top:-100px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">Zahlungsmöglichkeit bearbeiten</span>';
	echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="payment_cancel();" alt="Schließen" title="Schließen" />';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Land</td>';
	echo '		<td>';
	echo '			<select id="payment_edit_country_id">';
	$results=q("SELECT * FROM shop_countries ORDER BY country;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '				<option value="'.$row["id_country"].'">'.$row["country"].'</option>';
	}
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Zahlungsmöglichkeit</td>';
	echo '		<td>';
	echo '			<input id="payment_edit_payment" type="text" value="" />';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Memo</td>';
	echo '		<td>';
	echo '			<textarea style="width:300px; height:50px;" id="payment_edit_payment_memo"></textarea>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Zahlungsart</td>';
	echo '		<td>';
	echo '			<select id="payment_edit_paymenttype_id">';
	$results=q("SELECT * FROM shop_payment_types ORDER BY title;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '<option value="'.$row["id_paymenttype"].'">'.$row["title"].'</option>';
	}
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">';
	echo '			<input class="formbutton" type="button" value="Speichern" onclick="payment_edit();">';
	echo '			<input class="formbutton" type="button" value="Abbrechen" onclick="payment_cancel();" />';
	echo '	</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';
	
	//PAYMENT REMOVE WINDOW
	echo '<div id="payment_remove" class="popup" style="display:none;">';
	echo '<table style="position:absolute; left:50%; top:50%; width:320px; height:150px; margin-left:-160px; margin-top:-75px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">Zahlungsmöglichkeit wirklich löschen?</span>';
	echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="payment_cancel();" alt="Schließen" title="Schließen" />';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">Sind Sie sicher, dass Sie diese Zahlungsmöglichkeit löschen möchten?</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>';
	echo '			<input class="formbutton" type="button" value="Ja" onclick="payment_remove();" />';
	echo '			<input class="formbutton" type="button" value="Nein" onclick="payment_cancel();" />';
	echo '		</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';
	
	//SHIPPING ADD WINDOW
	echo '<div id="shipping_add" class="popup" style="display:none;">';
	echo '<table style="position:absolute; left:50%; top:50%; width:320px; height:150px; margin-left:-160px; margin-top:-75px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">Versandart hinzufügen</span>';
	echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="shipping_cancel();" alt="Schließen" title="Schließen" />';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Versandart</td>';
	echo '		<td>';
	echo '			<input id="shipping_add_shipping" type="text" value="" />';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Memo</td>';
	echo '		<td>';
	echo '			<textarea style="width:300px; height:50px;" id="shipping_add_shipping_memo"></textarea>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Preis</td>';
	echo '		<td>';
	echo '			<input id="shipping_add_price" type="text" value="" />';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Versandtyp</td>';
	echo '		<td>';
	echo '			<select id="shipping_add_shippingtype_id">';
	$results=q("SELECT * FROM shop_shipping_types ORDER BY title;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '<option value="'.$row["id_shippingtype"].'">'.$row["title"].'</option>';
	}
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">';
	echo '			<input class="formbutton" type="button" value="Speichern" onclick="shipping_add();">';
	echo '			<input class="formbutton" type="button" value="Abbrechen" onclick="shipping_cancel();" />';
	echo '		</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';
	
	//SHIPPING EDIT WINDOW
	echo '<div id="shipping_edit" class="popup" style="display:none;">';
	echo '<table style="position:absolute; left:50%; top:50%; width:320px; height:200px; margin-left:-160px; margin-top:-100px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">Versandart bearbeiten</span>';
	echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="shipping_cancel();" alt="Schließen" title="Schließen" />';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Versandart</td>';
	echo '		<td>';
	echo '			<input id="shipping_edit_shipping" type="text" value="" />';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Memo</td>';
	echo '		<td>';
	echo '			<textarea style="width:300px; height:50px;" id="shipping_edit_shipping_memo"></textarea>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Preis</td>';
	echo '		<td>';
	echo '			<input id="shipping_edit_price" type="text" value="" />';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Versandtyp</td>';
	echo '		<td>';
	echo '			<select id="shipping_edit_shippingtype_id">';
	$results=q("SELECT * FROM shop_shipping_types ORDER BY title;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '<option value="'.$row["id_shippingtype"].'">'.$row["title"].'</option>';
	}
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">';
	echo '			<input class="formbutton" type="button" value="Speichern" onclick="shipping_edit();">';
	echo '			<input class="formbutton" type="button" value="Abbrechen" onclick="shipping_cancel();" />';
	echo '	</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';
	
	//SHIPPING REMOVE WINDOW
	echo '<div id="shipping_remove" class="popup" style="display:none;">';
	echo '<table style="position:absolute; left:50%; top:50%; width:320px; height:150px; margin-left:-160px; margin-top:-75px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">Versandart wirklich löschen?</span>';
	echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="shipping_cancel();" alt="Schließen" title="Schließen" />';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">Sind Sie sicher, dass Sie diese Versandart löschen möchten?</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>';
	echo '			<input class="formbutton" type="button" value="Ja" onclick="shipping_remove();" />';
	echo '			<input class="formbutton" type="button" value="Nein" onclick="shipping_cancel();" />';
	echo '		</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>