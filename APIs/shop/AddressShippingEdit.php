<?php

	include("../functions/cms_t.php");

	$shipping_address["adr_id"]='';
	$shipping_address["company"]='';
	$shipping_address["gender"]='';
	$shipping_address["title"]='';
	$shipping_address["firstname"]='';
	$shipping_address["lastname"]='';
	$shipping_address["street"]='';
	$shipping_address["number"]='';
	$shipping_address["additional"]='';
	$shipping_address["zip"]='';
	$shipping_address["city"]='';
	if (isset($_SESSION["ship_country_id"])) $shipping_address["country_id"]=$_SESSION["ship_country_id"];
	else $shipping_address["country_id"]='';
	if (isset($_SESSION["ship_country"])) $shipping_address["country"]=$_SESSION["ship_country"];
	$shipping_address["country"]='';
	$shipping_address["standard"]='';

	if(isset($_POST["ship_delete"]) and $_POST["ship_delete"]!=0)
	{
		q("UPDATE shop_bill_adr SET active_ship_adr=0, standard_ship_adr=0 WHERE adr_id=".$_POST["ship_delete"].";", $dbshop, __FILE__, __LINE__);
		if($_POST["ship_delete"]==$_SESSION["ship_adr_id"])
		{
			unset($_SESSION["ship_adr_id"]);
			$_SESSION["ship_company"]='';
			$_SESSION["ship_gender"]='';
			$_SESSION["ship_title"]='';
			$_SESSION["ship_firstname"]='';
			$_SESSION["ship_lastname"]='';
			$_SESSION["ship_street"]='';
			$_SESSION["ship_number"]='';
			$_SESSION["ship_additional"]='';
			$_SESSION["ship_zip"]='';
			$_SESSION["ship_city"]='';
			$_SESSION["ship_country_id"]=1;
			$_SESSION["ship_country"]=t("Deutschland");
			$_SESSION["ship_standard"]='';
		}
		unset($shipping_address["adr_id"]);
		$shipping_address["company"]='';
		$shipping_address["gender"]='';
		$shipping_address["title"]='';
		$shipping_address["firstname"]='';
		$shipping_address["lastname"]='';
		$shipping_address["street"]='';
		$shipping_address["number"]='';
		$shipping_address["additional"]='';
		$shipping_address["zip"]='';
		$shipping_address["city"]='';
		$shipping_address["country_id"]=1;
		$shipping_address["country"]='';
		$shipping_address["standard"]='';
	}
	elseif(isset($_POST["ship_select"]))
	{
		$shipping_address["adr_id"]=$_POST["ship_select"];
		if($_POST["ship_select"]==0)
		{ 
			$shipping_address["company"]='';
			$shipping_address["gender"]='';
			$shipping_address["title"]='';
			$shipping_address["firstname"]='';
			$shipping_address["lastname"]='';
			$shipping_address["street"]='';
			$shipping_address["number"]='';
			$shipping_address["additional"]='';
			$shipping_address["zip"]='';
			$shipping_address["city"]='';
			$shipping_address["country_id"]=1;
			$shipping_address["country"]='';
			$shipping_address["standard"]='';
		}
	}
	else
	{
		if(isset($_SESSION["ship_adr_id"]))
		{
			$shipping_address["adr_id"]=$_SESSION["ship_adr_id"];
		}		
	}
	
	echo '<table id="ship_edit" style="text-align:left; width:669px;"">';
	echo '	<tr>';
	echo '		<td>'.t("Adresse").'</td>';
	echo '		<td colspan="3">';
	echo '		<select id="ship_adr_id" onchange="ship_select()" style="width:480px; float:left;">';
	echo '			<option selected="selected" value="0">'.t("Neue Lieferanschrift").'...</option>';
	
	$results=q("SELECT * FROM shop_bill_adr WHERE user_id=".$_SESSION["id_user"]." and active_ship_adr=1 ORDER BY adr_id;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		if ($row["adr_id"]==$shipping_address["adr_id"]) $selected=' selected="selected"';
		elseif (!isset($shipping_address["adr_id"]) and $row["standard_ship_adr"]==1) $selected=' selected="selected"'; 
		else $selected='';
		if ($row["company"]!="") $ship_adr_txt=$row["company"].', '; else $ship_adr_txt='';
		if ($row["firstname"]!="" or $row["lastname"]!="")
		{
			$ship_adr_txt.=$row["title"].' ';
			$ship_adr_txt.=$row["firstname"].' ';
			$ship_adr_txt.=$row["lastname"].', ';				
		}
		$ship_adr_txt.=$row["street"].' '.$row["number"].', ';
		if ($row["additional"]!="") $ship_adr_txt.=$row["additional"].', ';
		$ship_adr_txt.=$row["zip"].' '.$row["city"].', '.$row["country"];

		if ($selected!='')
		{
			$shipping_address["adr_id"]=$row["adr_id"];
			$shipping_address["company"]=$row["company"];
			$shipping_address["gender"]=$row["gender"];
			$shipping_address["title"]=$row["title"];
			$shipping_address["firstname"]=$row["firstname"];
			$shipping_address["lastname"]=$row["lastname"];
			$shipping_address["street"]=$row["street"];
			$shipping_address["number"]=$row["number"];
			$shipping_address["additional"]=$row["additional"];
			$shipping_address["zip"]=$row["zip"];
			$shipping_address["city"]=$row["city"];
			$shipping_address["country_id"]=$row["country_id"];
			$shipping_address["country"]=$row["country"];
			$shipping_address["standard"]=$row["standard_ship_adr"];
		}

		echo '		<option'.$selected.' value="'.$row["adr_id"].'">'.$ship_adr_txt.'</option>';
	}
	echo '		</select>';
	if(isset($shipping_address["adr_id"]) and $shipping_address["adr_id"]>0)
	{
		echo '		<img style="margin:0; border:0; padding:5px 0px; cursor:pointer; display:inline; float:right;" src="'.PATH.'images/icons/16x16/remove.png" onclick="ship_delete();" alt="'.t("Anschrift löschen").'" title="'.t("Anschrift löschen").'" />';
	}
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td style="width:150px;">'.t("Firma").'</td>';
	echo '		<td colspan="3"><input type="text" id="ship_company" value="'.$shipping_address["company"].'" style="width:475px;" /><b style="color:red;"> * </b></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td style="width:150px;">'.t("Anrede").'</td>';
	echo '		<td style="width:150px;">';
	echo '			<select id="ship_gender" style="width:145px;">';
	if($shipping_address["gender"]==0) $selected=' selected="selected"'; else $selected='';
	echo '				<option'.$selected.' value="0">'.t("Herr").'</option>';
	if($shipping_address["gender"]==1) $selected=' selected="selected"'; else $selected='';
	echo '				<option'.$selected.' value="1">'.t("Frau").'</option>';
	echo '			</select>';
	echo '		</td>';
	echo '		<td style="width:150px;">'.t("Titel").'</td>';
	echo '		<td style="width:150px;"><input type="text" id="ship_title" value="'.$shipping_address["title"].'" style="width:140px;" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>'.t("Vorname").'</td>';
	echo '		<td><input type="text" id="ship_firstname" value="'.$shipping_address["firstname"].'" style="width:140px;" /><b style="color:red;"> * </b></td>';
	echo '		<td>'.t("Nachname").'</td>';
	echo '		<td><input type="text" id="ship_lastname" value="'.$shipping_address["lastname"].'" style="width:140px;" /><b style="color:red;"> * </b></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>'.t("Straße").'</td>';
	echo '		<td><input type="text" id="ship_street" value="'.$shipping_address["street"].'" style="width:140px;" /><b style="color:red;"> * </b></td>';
	echo '		<td>'.t("Hausnummer").'</td>';
	echo '		<td><input type="text" id="ship_number" value="'.$shipping_address["number"].'" style="width:140px;" /><b style="color:red;"> * </b></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>'.t("Adresszusatz").'</td>';
	echo '		<td colspan="3"><input type="text" id="ship_additional" value="'.$shipping_address["additional"].'" style="width:475px;" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>'.t("Postleitzahl").'</td>';
	echo '		<td><input type="text" id="ship_zip" value="'.$shipping_address["zip"].'" style="width:140px;" /><b style="color:red;"> * </b></td>';
	echo '		<td>'.t("Ort").'</td>';
	echo '		<td><input type="text" id="ship_city" value="'.$shipping_address["city"].'" style="width:140px;" /><b style="color:red;"> * </b></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>'.t("Land").'</td>';
	echo '		<td colspan="3">';
	if(!isset($_POST["ship_country_call"])) $_POST["ship_country_call"]=$shipping_address["country_id"];
	echo '		<input type="hidden" id="ship_country_call" value="'.$_POST["ship_country_call"].'" />';
	echo '		<select id="ship_country_id" style="width:145px;">';

	$shop_countries=array();
	$results2=q("SELECT country_id FROM shop_payment WHERE shop_id=".$_SESSION["id_shop"].";", $dbshop, __FILE__, __LINE__);
	while($row2=mysqli_fetch_array($results2))
	{
		$shop_countries[$row2["country_id"]]=$row2["country_id"];
	}
	$results2=q("SELECT * FROM shop_countries ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
	while($row2=mysqli_fetch_array($results2))
	{
		if (isset($shop_countries[$row2["id_country"]]) or $_SESSION["id_shop"]!=2)
		{
			if ($shipping_address["country_id"]==$row2["id_country"]) $selected=' selected="selected"'; else $selected='';
			echo '<option'.$selected.' value="'.$row2["id_country"].'">'.$row2["country"].'</option>';
		}
	}
	echo '		</select>';
	echo '		</td>';
	echo '</tr>';
	echo '	<tr>';
	echo '		<td colspan="4">';
	if (isset($shipping_address["standard"]) and $shipping_address["standard"]==1) $checked=' checked="checked"'; 
	else $checked='';
	echo '			<form>';
	echo '				<input type="checkbox" id="ship_standard" value="1" '.$checked.'>';
	echo 				t("Als Standard Lieferanschrift definieren").'!';
	echo '			</form>';
	echo '		</td>';
	echo '</tr>';
	echo '	<tr>';
	echo '		<td colspan="4" style="color:red; font-size:12px;">';
	echo '			* '.t("Pflichtfelder").' ('.t("Firma oder Vor- und Nachname").')';
	echo '		</td>';
	echo '</tr>';
	echo '</table>';

?>