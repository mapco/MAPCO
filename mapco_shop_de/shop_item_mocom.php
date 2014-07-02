<?php
	include("config.php");
	include("functions/shop_get_price.php");
	include("functions/shop_get_net_price.php");
	include("functions/shop_mail_order.php");
	include("functions/shop_itemstatus.php");	
	include("functions/cms_send_html_mail.php");
	include("functions/cms_t.php");
	include("functions/mapco_gewerblich.php");
	include("functions/cms_remove_element.php");
	include("functions/mapco_get_titles.php");


?>
	<script>
		function bill_cancel()
		{
			hide("bill_window");
		}

		function bill_edit()
		{
			var response=ajax("modules/shop_cart_actions.php?action=bill_edit", false);
			document.getElementById("bill_window").innerHTML=response;
			show("bill_window");
		}
		
		function bill_save()
		{
			var bill_company=document.getElementById("bill_company").value;
			var bill_gender=document.getElementById("bill_gender").value;
			var bill_title=document.getElementById("bill_title").value;
			var bill_firstname=document.getElementById("bill_firstname").value;
			var bill_lastname=document.getElementById("bill_lastname").value;
			var bill_street=document.getElementById("bill_street").value;
			var bill_number=document.getElementById("bill_number").value;
			var bill_additional=document.getElementById("bill_additional").value;
			var bill_zip=document.getElementById("bill_zip").value;
			var bill_city=document.getElementById("bill_city").value;
			var bill_country_id=document.getElementById("bill_country_id").value;
			var response=ajax("modules/shop_cart_actions.php?action=bill_save&bill_company="+encodeURIComponent(bill_company)+"&bill_gender="+encodeURIComponent(bill_gender)+"&bill_title="+encodeURIComponent(bill_title)+"&bill_firstname="+encodeURIComponent(bill_firstname)+"&bill_lastname="+encodeURIComponent(bill_lastname)+"&bill_street="+encodeURIComponent(bill_street)+"&bill_number="+encodeURIComponent(bill_number)+"&bill_additional="+encodeURIComponent(bill_additional)+"&bill_zip="+encodeURIComponent(bill_zip)+"&bill_city="+encodeURIComponent(bill_city)+"&bill_country_id="+bill_country_id, false);
			if (response!="") show_status(response);
			hide("bill_window");
			view_cart();
		}

		function ship_cancel()
		{
			hide("ship_window");
		}

		function ship_edit()
		{
			var response=ajax("modules/shop_cart_actions.php?action=ship_edit", false);
			document.getElementById("ship_window").innerHTML=response;
			show("ship_window");
		}
		
		function ship_save()
		{
			var ship_company=document.getElementById("ship_company").value;
			var ship_gender=document.getElementById("ship_gender").value;
			var ship_title=document.getElementById("ship_title").value;
			var ship_firstname=document.getElementById("ship_firstname").value;
			var ship_lastname=document.getElementById("ship_lastname").value;
			var ship_street=document.getElementById("ship_street").value;
			var ship_number=document.getElementById("ship_number").value;
			var ship_additional=document.getElementById("ship_additional").value;
			var ship_zip=document.getElementById("ship_zip").value;
			var ship_city=document.getElementById("ship_city").value;
			var ship_country_id=document.getElementById("ship_country_id").value;
			var response=ajax("modules/shop_cart_actions.php?action=ship_save&ship_company="+encodeURIComponent(ship_company)+"&ship_gender="+encodeURIComponent(ship_gender)+"&ship_title="+encodeURIComponent(ship_title)+"&ship_firstname="+encodeURIComponent(ship_firstname)+"&ship_lastname="+encodeURIComponent(ship_lastname)+"&ship_street="+encodeURIComponent(ship_street)+"&ship_number="+encodeURIComponent(ship_number)+"&ship_additional="+encodeURIComponent(ship_additional)+"&ship_zip="+encodeURIComponent(ship_zip)+"&ship_city="+encodeURIComponent(ship_city)+"&ship_country_id="+ship_country_id, false);
			if (response!="") show_status(response);
			hide("ship_window");
			view_cart();
		}

	</script>


<?php

	//ADDITIONAL SAVE
	if ($_GET["action"]=="additional_save")
	{
		session_start();
		$_SESSION["ordernr"]=$_GET["ordernr"];
		$_SESSION["comment"]=$_GET["comment"];
		$_SESSION["usermail"]=$_GET["usermail"];
		$_SESSION["userphone"]=$_GET["userphone"];
		$_SESSION["userfax"]=$_GET["userfax"];
		$_SESSION["usermobile"]=$_GET["usermobile"];
	}
	
	//BILL EDIT
	if ($_GET["action"]=="bill_edit")
	{
		session_start();
		echo '<table class="hover" style="position:absolute; left:50%; top:50%; width:320px; height:500px; margin-left:-160px; margin-top:-250px; background:#ffffff; text-align:left;">';
		echo '	<tr>';
		echo '		<th colspan="2">';
		echo '			<span style="display:inline; float:left;">Rechnungsanschrift</span>';
		echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="bill_cancel();" alt="Schließen" title="Schließen" />';
		echo '		</th>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Firma").'</td>';
		echo '		<td><input type="" id="bill_company" value="'.$_SESSION["bill_company"].'" /></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Anrede").'</td>';
		echo '		<td>';
		echo '			<select id="bill_gender">';
		if($_SESSION["bill_gender"]==0) $selected=' selected="selected"'; else $selected='';
		echo '				<option'.$selected.' value="0">'.t("Herr").'</option>';
		if($_SESSION["bill_gender"]==1) $selected=' selected="selected"'; else $selected='';
		echo '				<option'.$selected.' value="1">'.t("Frau").'</option>';
		echo '			</select';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Titel").'</td>';
		echo '		<td><input type="" id="bill_title" value="'.$_SESSION["bill_title"].'" /></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Vorname").'</td>';
		echo '		<td><input type="" id="bill_firstname" value="'.$_SESSION["bill_firstname"].'" /></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Nachname").'</td>';
		echo '		<td><input type="" id="bill_lastname" value="'.$_SESSION["bill_lastname"].'" /></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Straße").'</td>';
		echo '		<td><input type="" id="bill_street" value="'.$_SESSION["bill_street"].'" /></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Hausnummer").'</td>';
		echo '		<td><input type="" id="bill_number" value="'.$_SESSION["bill_number"].'" /></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Adresszusatz").'</td>';
		echo '		<td><input type="" id="bill_additional" value="'.$_SESSION["bill_additional"].'" /></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Postleitzahl").'</td>';
		echo '		<td><input type="" id="bill_zip" value="'.$_SESSION["bill_zip"].'" /></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Ort").'</td>';
		echo '		<td><input type="" id="bill_city" value="'.$_SESSION["bill_city"].'" /></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Land").'</td>';
		echo '		<td>';
		echo '		<select id="bill_country_id">';
		$query="SELECT * FROM shop_countries ORDER BY ordering;";
		$results=mysql_query($query, $dbshop) or error(__FILE__, __LINE__, $query.'<br />'.mysql_error($dbshop));
		while($row=mysql_fetch_array($results))
		{
			if ($_SESSION["bill_country_id"]==$row["id_country"]) $selected=' selected="selected"'; else $selected='';
			echo '<option'.$selected.' value="'.$row["id_country"].'">'.$row["country"].'</option>';
		}
		echo '		</select>';
		echo '</td>';
		echo '</tr>';
		echo '	<tr>';
		echo '		<td colspan="2">';
		echo '			<input class="formbutton" type="button" value="Speichern" onclick="bill_save();" />';
		echo '			<input class="formbutton" type="button" value="Abbrechen" onclick="bill_cancel();" />';
		echo '		</td>';
		echo '	</tr>';
		echo '</table>';
	}
	
	//BILL SAVE
	if ($_GET["action"]=="bill_save")
	{
		session_start();
		$_SESSION["bill_company"]=$_GET["bill_company"];
		$_SESSION["bill_gender"]=$_GET["bill_gender"];
		$_SESSION["bill_title"]=$_GET["bill_title"];
		$_SESSION["bill_firstname"]=$_GET["bill_firstname"];
		$_SESSION["bill_lastname"]=$_GET["bill_lastname"];
		$_SESSION["bill_street"]=$_GET["bill_street"];
		$_SESSION["bill_number"]=$_GET["bill_number"];
		$_SESSION["bill_additional"]=$_GET["bill_additional"];
		$_SESSION["bill_zip"]=$_GET["bill_zip"];
		$_SESSION["bill_city"]=$_GET["bill_city"];
		$_SESSION["bill_country_id"]=$_GET["bill_country_id"];
		$query="SELECT * FROM shop_countries WHERE id_country=".$_GET["bill_country_id"].";";
		$results=mysql_query($query, $dbshop) or error(__FILE__, __LINE__, $query.'<br />'.mysql_error($dbshop));
		$row=mysql_fetch_array($results);
		$_SESSION["bill_country"]=$row["country"];
	}
	
	//COUNTRY SELECTION
	if ($_GET["action"]=="country_selection")
	{
		session_start();
		$_SESSION["ship_country_id"]=$_GET["ship_country_id"];
		$query="SELECT * FROM shop_countries WHERE id_country=".$_GET["ship_country_id"].";";
		$results=mysql_query($query, $dbshop) or error(__FILE__, __LINE__, $query.'<br />'.mysql_error($dbshop));
		$row=mysql_fetch_array($results);
		$_SESSION["ship_country"]=$row["country"];
		$_SESSION["id_payment"]="";
		$_SESSION["id_shipping"]="";
	}
	
	//SHIP EDIT
	if ($_GET["action"]=="ship_edit")
	{
		session_start();
		echo '<table class="hover" style="position:absolute; left:50%; top:50%; width:320px; height:500px; margin-left:-160px; margin-top:-250px; background:#ffffff; text-align:left;">';
		echo '	<tr>';
		echo '		<th colspan="2">';
		echo '			<span style="display:inline; float:left;">Lieferanschrift</span>';
		echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="ship_cancel();" alt="Schließen" title="Schließen" />';
		echo '		</th>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Firma").'</td>';
		echo '		<td><input type="" id="ship_company" value="'.$_SESSION["ship_company"].'" /></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Anrede").'</td>';
		echo '		<td>';
		echo '			<select id="ship_gender">';
		if($_SESSION["ship_gender"]==0) $selected=' selected="selected"'; else $selected='';
		echo '				<option'.$selected.' value="0">'.t("Herr").'</option>';
		if($_SESSION["ship_gender"]==1) $selected=' selected="selected"'; else $selected='';
		echo '				<option'.$selected.' value="1">'.t("Frau").'</option>';
		echo '			</select';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Titel").'</td>';
		echo '		<td><input type="" id="ship_title" value="'.$_SESSION["ship_title"].'" /></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Vorname").'</td>';
		echo '		<td><input type="" id="ship_firstname" value="'.$_SESSION["ship_firstname"].'" /></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Nachname").'</td>';
		echo '		<td><input type="" id="ship_lastname" value="'.$_SESSION["ship_lastname"].'" /></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Straße").'</td>';
		echo '		<td><input type="" id="ship_street" value="'.$_SESSION["ship_street"].'" /></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Hausnummer").'</td>';
		echo '		<td><input type="" id="ship_number" value="'.$_SESSION["ship_number"].'" /></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Adresszusatz").'</td>';
		echo '		<td><input type="" id="ship_additional" value="'.$_SESSION["ship_additional"].'" /></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Postleitzahl").'</td>';
		echo '		<td><input type="" id="ship_zip" value="'.$_SESSION["ship_zip"].'" /></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Ort").'</td>';
		echo '		<td><input type="" id="ship_city" value="'.$_SESSION["ship_city"].'" /></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Land").'</td>';
		echo '		<td>';
		echo '		<select id="ship_country_id">';
		$query="SELECT * FROM shop_countries ORDER BY ordering;";
		$results=mysql_query($query, $dbshop) or error(__FILE__, __LINE__, $query.'<br />'.mysql_error($dbshop));
		while($row=mysql_fetch_array($results))
		{
			if ($_SESSION["ship_country_id"]==$row["id_country"]) $selected=' selected="selected"'; else $selected='';
			echo '<option'.$selected.' value="'.$row["id_country"].'">'.$row["country"].'</option>';
		}
		echo '		</select>';
		echo '</td>';
		echo '</tr>';
		echo '	<tr>';
		echo '		<td colspan="2">';
		echo '			<input class="formbutton" type="button" value="Speichern" onclick="ship_save();" />';
		echo '			<input class="formbutton" type="button" value="Abbrechen" onclick="ship_cancel();" />';
		echo '		</td>';
		echo '	</tr>';
		echo '</table>';
	}
	
	//SHIP SAVE
	if ($_GET["action"]=="ship_save")
	{
		session_start();
		$_SESSION["ship_company"]=$_GET["ship_company"];
		$_SESSION["ship_gender"]=$_GET["ship_gender"];
		$_SESSION["ship_title"]=$_GET["ship_title"];
		$_SESSION["ship_firstname"]=$_GET["ship_firstname"];
		$_SESSION["ship_lastname"]=$_GET["ship_lastname"];
		$_SESSION["ship_street"]=$_GET["ship_street"];
		$_SESSION["ship_number"]=$_GET["ship_number"];
		$_SESSION["ship_additional"]=$_GET["ship_additional"];
		$_SESSION["ship_zip"]=$_GET["ship_zip"];
		$_SESSION["ship_city"]=$_GET["ship_city"];
		$_SESSION["ship_country_id"]=$_GET["ship_country_id"];
		$query="SELECT * FROM shop_countries WHERE id_country=".$_GET["ship_country_id"].";";
		$results=mysql_query($query, $dbshop) or error(__FILE__, __LINE__, $query.'<br />'.mysql_error($dbshop));
		$row=mysql_fetch_array($results);
		$_SESSION["ship_country"]=$row["country"];
		$_SESSION["id_payment"]="";
		$_SESSION["id_shipping"]="";
	}




	$results=q("SELECT * FROM shop_items_mocom WHERE id_item='".$_GET["id_item"]."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
	$row=mysql_fetch_array($results);
	$_GET["id_category"]=$row["category_id"];

	//header
	$right_column=true;
	include("templates/".TEMPLATE."/header.php");

	include("modules/cms_leftcolumn.php");
	echo '<div id="mid_right_column">';

	//Gewerbskunde?
	$gewerblich=gewerblich($_SESSION["id_user"]);

	//Hotline-Banner
	if ($_GET["lang"]=="de")
	{
		echo '<div align="center"><img src="images/mocom_header.jpg" alt="Sonderpostenverkauf" title="Sonderpostenverkauf"/><div><br />';
	}

	//title
	$results=q("SELECT * FROM shop_items_mocom AS a, shop_items AS b WHERE a.id_item='".$_GET["id_item"]."' AND a.id_item=b.id_item;", $dbshop, __FILE__, __LINE__);
	$row=mysql_fetch_array($results);
	$artnr=$row["MPN"];
	echo '<h1>'.$row["title"].'</h1>';
	$title=$row["title"];

	
	//price
	echo '<div style="width:200px; display:inline; float:right;">';
	echo '	<span style="width:100px; font-size:10px; font-weight:bold; font-style:italic; color:#ff0000;">';
	echo 'AKTIONSPREIS!';
	echo '	</span><br />';
	echo '	<span style="width:100px; font-size:30px; font-weight:bold; font-style:italic; color:#ff0000;">';	

	echo '		€ '.number_format(get_price($row["id_item"]), 2);
	echo '	</span>';
	echo '<span style="font-size:10px;">';
	echo '	<br />'.t("zzgl. Versandkosten").'</span>';
	echo '	<br /><form onsubmit="return cart_add('.$row["id_item"].');"><input id="article'.$row["id_item"].'" type="text" size="1" value="1" /></form>';
	echo '	<input type="button" onclick="return cart_add('.$row["id_item"].');" value="'.t("In den Warenkorb").'" name="form_button" />';
	echo '<br />'.itemstatus($_GET["id_item"]);
	echo '</div>';
	
	
	//small images
	echo '<div style="width:104px; margin:2px; border:1px solid #cccccc; padding:0px; float:left;">';
	$results3=q("SELECT * FROM shop_items_files WHERE item_id='".$_GET["id_item"]."';", $dbshop, __FILE__, __LINE__);
	while($row3=mysql_fetch_array($results3))
	{
		$results2=q("SELECT * FROM cms_files WHERE id_file='".$row3["file_id"]."';", $dbweb, __FILE__, __LINE__);
		$row2=mysql_fetch_array($results2);
		$filename='files/'.floor(bcdiv($row2["id_file"], 1000)).'/'.$row2["id_file"].'.'.$row2["extension"];
		echo '<img style="width:100px; margin:2px; border:0; padding:0;" src="'.$filename.'" alt="'.$title.'" title="'.$title.'" onmouseover="document.getElementById(\'bigimage\').src=this.src" />';
	}
	echo '</div>';
	//big image
	echo '<div style="width:400px; margin:2px; border:1px solid #cccccc; padding:0px; float:left;">';
	$results3=q("SELECT * FROM shop_items_files WHERE item_id='".$_GET["id_item"]."';", $dbshop, __FILE__, __LINE__);
	if (mysql_num_rows($results3)>0)
	{
		$row3=mysql_fetch_array($results3);
		$results2=q("SELECT * FROM cms_files WHERE id_file='".$row3["file_id"]."';", $dbweb, __FILE__, __LINE__);
		$row2=mysql_fetch_array($results2);
		$filename='files/'.floor(bcdiv($row2["id_file"], 1000)).'/'.$row2["id_file"].'.'.$row2["extension"];
		echo '	<img style="width:400px;" src="'.$filename.'" alt="'.$title.'" title="'.$title.'" id="bigimage" />';
	}
	else
	{
		echo '	<img style="width:400px;" src="images/artikel/00000.jpg" alt="'.$title.'" title="'.$title.'" />';
	}
	echo '</div>';
	
	//description
	echo $row["description"];
	
//	echo '<div><input type="checkbox" name="form_agbs" /><a href="gtct.php?lang='.$_GET["lang"].'">'.t("Hiermit erkläre ich mich mit den Allgemeinen Geschäftsbedingungen der mocom GmbH einverstanden.").'</a></div>';

	echo '</div>';
	
	echo '<table class="hover">';
	echo '<tr>';
	echo '	<td colspan="3">Nettogesamtwert</td>';
	echo '	<td>€ '.number_format($total, 2).'</td>';
	echo '</tr>';
	$ust=(UST/100)*$total;
	$total=((100+UST)/100)*$total;

	//Umsatzsteuer
	echo '<tr>';
	echo '	<td colspan="3">gesetzliche Umsatzsteuer '.UST.'%</td>';
	echo '	<td>€ '.number_format($ust, 2).'</td>';
	echo '</tr>';


	//total
	echo '<tr>';
	echo '	<td colspan="3" style="font-weight:bold;">'.t("Bruttogesamtwert").'</td>';
	echo '	<td style="font-weight:bold;">€ '.number_format($total, 2).'</td>';
	echo '</tr>';
	echo '<tr><td colspan="4"><input type="checkbox" name="form_agbs" /><a href="gtct.php?lang='.$_GET["lang"].'">'.t("Hiermit erkläre ich mich mit den Allgemeinen Geschäftsbedingungen einverstanden.").'</a></td></tr>';
		echo '<tr>';
		echo '	<td colspan="4">';
		echo '		<input style="display:inline; float:right;" type="submit" name="form_button" value="'.t("Bestellung abschicken").'" />';
		echo '	</td>';
		echo '</tr>';
	echo '</table>';

	//additional information
	echo '<table class="hover" style="margin:20px 0px 0px 0px;">';
	echo '<tr>';
	echo '	<th colspan="4">'.t("Zusätzliche Informationen").'</th>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>';
	echo '		<b>'.t("Eigene Bestellnummer").'</b>';
	echo '		<br /><input type="text" id="ordernr" value="" />';
	echo '		<br /><i>'.t("Falls Sie ein eigenes Buchungssystem verwenden, können Sie zur späteren Sendungseinordnung hier Ihre Bestellnummer angeben.").'</i>';
	echo '	</td>';
	echo '	<td colspan="3">';
	echo '		<b>'.t("Anmerkung zur Bestellung").'</b>';
	echo '		<br /><textarea style="width:400px; height:70px;" id="comment"></textarea>';
	echo '	</td>';
	echo '</tr>';
	echo '</table>';
	
	echo '<table class="hover">';
//		echo '<tr><th colspan="4">'.t("Erreichbarkeit").'</th></tr>';
	echo '<tr>';
	if ($_SESSION["usermail"]=="") echo '<td id="empty"><b>'.t("E-Mail").'</b><br /><input type="text" id="usermail" value="'.$_SESSION["usermail"].'" /></td>';
	else echo ' <td><b>'.t("E-Mail").'</b><br /><input type="text" id="usermail" value="'.$_SESSION["usermail"].'" /></td>';
	if ($_SESSION["userphone"]=="") echo ' <td id="empty"><b>'.t("Telefon").'</b><br /><input type="text" id="userphone" value="'.$_SESSION["userphone"].'" /></td>';
	else echo ' <td><b>'.t("Telefon").'</b><br /><input type="text" id="userphone" value="'.$_SESSION["userphone"].'" /></td>';
	echo '	<td><b>'.t("Telefax").'</b><br /><input type="text" id="userfax" value="'.$_SESSION["userfax"].'" /></td>';
	echo '	<td><b>'.t("Mobiltelefon").'</b><br /><input type="text" id="usermobile" value="'.$_SESSION["usermobile"].'" /></td>';
	echo '</tr>';
	echo '</table>';
	
	
	//addresses
	echo '<table class="hover" style="margin:20px 0px 0px 0px;">';
	echo '<tr>';
	echo '	<th colspan="2" width="50%">'.t("Rechnungsanschrift").'</th>';
	echo '	<th colspan="2">'.t("Lieferanschrift").'</th>';
	echo '</tr>';
	echo '<tr>';
	if ($_SESSION["bill_firstname"]=="") echo '<td colspan="2" id="empty">';
	elseif ($_SESSION["bill_lastname"]=="") echo '<td colspan="2" id="empty">';
	elseif ($_SESSION["bill_zip"]=="") echo '<td colspan="2" id="empty">';
	elseif ($_SESSION["bill_city"]=="") echo '<td colspan="2" id="empty">';
	elseif ($_SESSION["bill_street"]=="") echo '<td colspan="2" id="empty">';
	elseif ($_SESSION["bill_number"]=="") echo '<td colspan="2" id="empty">';
	elseif ($_SESSION["bill_country"]=="") echo '<td colspan="2" id="empty">';
	else echo ' <td colspan="2">';
	if ($_SESSION["bill_company"]!="") echo $_SESSION["bill_company"];
	echo '<br />';
	if (isset($_SESSION["bill_gender"]))
	{
		if ($_SESSION["bill_gender"]==0) echo t("Herr").' ';
		elseif($_SESSION["bill_gender"]==1) echo t("Frau").' ';
	}
	echo $_SESSION["bill_title"];
	echo '<br />';
	echo $_SESSION["bill_firstname"].' ';
	echo $_SESSION["bill_lastname"];
	echo '<br />';
	echo $_SESSION["bill_street"].' '.$_SESSION["bill_number"];
	echo '<br />';
	echo $_SESSION["bill_additional"];
	echo '<br />';
	echo $_SESSION["bill_zip"].' ';
	echo $_SESSION["bill_city"];
	echo '<br /> ';
	echo $_SESSION["bill_country"];
	echo '<br /> ';
	echo '		<br /><a href="javascript:bill_edit();">'.t("Ändern").'</a>';
	echo '	</td>';
	
	echo '	<td>';
	if ($_SESSION["ship_company"]!="") echo $_SESSION["ship_company"];
	echo '<br />';
	if (isset($_SESSION["ship_gender"]))
	{
		if ($_SESSION["ship_gender"]==0) echo t("Herr").' ';
		elseif($_SESSION["ship_gender"]==1) echo t("Frau").' ';
	}
	echo $_SESSION["ship_title"];
	echo '<br />';
	echo $_SESSION["ship_firstname"].' ';
	echo $_SESSION["ship_lastname"];
	echo '<br />';
	echo $_SESSION["ship_street"].' '.$_SESSION["ship_number"];
	echo '<br />';
	echo $_SESSION["ship_additional"];
	echo '<br />';
	echo $_SESSION["ship_zip"].' ';
	echo $_SESSION["ship_city"];
	echo '<br /> ';
	echo $_SESSION["ship_country"];
	echo '<br /> ';
	echo '		<br /><a href="javascript:ship_edit();">'.t("Ändern").'</a>';
	echo '	</td>';
	echo '</tr>';
	echo '</table>';

	echo '<input type="hidden" name="lang" value="'.$_GET["lang"].'" />';
	echo '</form>';

	
		
	//BILL WINDOW
	echo '<div id="bill_window" class="popup" style="display:none;">';
	echo '</div>';
	
	//BILL WINDOW
	echo '<div id="ship_window" class="popup" style="display:none;">';
	echo '</div>';


	include("templates/".TEMPLATE."/footer.php");
?>