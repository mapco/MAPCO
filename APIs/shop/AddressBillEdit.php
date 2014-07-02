<?php

	include("../functions/cms_t.php");

	$bill_address["adr_id"]='';
	$bill_address["company"]='';
	$bill_address["gender"]='';
	$bill_address["title"]='';
	$bill_address["firstname"]='';
	$bill_address["lastname"]='';
	$bill_address["street"]='';
	$bill_address["number"]='';
	$bill_address["additional"]='';
	$bill_address["zip"]='';
	$bill_address["city"]='';
	if (isset($_SESSION["bill_country_id"])) $bill_address["country_id"]=$_SESSION["bill_country_id"];
	else $bill_address["country_id"]='';
	if (isset($_SESSION["bill_country"])) $bill_address["country"]=$_SESSION["bill_country"];
	$bill_address["country"]='';
	$bill_address["standard"]='';

	if(isset($_POST["bill_delete"]) and $_POST["bill_delete"]!=0)
	{
		$results=q("SELECT * FROM shop_bill_adr WHERE user_id=".$_SESSION["id_user"]." and active=1 LIMIT 2;", $dbshop, __FILE__, __LINE__);
		if(mysqli_num_rows($results)>1)
		{
			q("UPDATE shop_bill_adr SET active=0, standard=0 WHERE adr_id=".$_POST["bill_delete"].";", $dbshop, __FILE__, __LINE__);
			$results=q("SELECT * FROM shop_bill_adr WHERE user_id=".$_SESSION["id_user"]." and active=1 ORDER BY adr_id ASC LIMIT 1;", $dbshop, __FILE__, __LINE__);
			$row=mysqli_fetch_array($results);
			q("UPDATE shop_bill_adr SET standard=1 WHERE adr_id=".$row["adr_id"].";", $dbshop, __FILE__, __LINE__);
			if($_POST["bill_delete"]==$_SESSION["bill_adr_id"])
			{
				$_SESSION["bill_adr_id"]=$row["adr_id"];
				$_SESSION["bill_company"]=$row["company"];
				$_SESSION["bill_gender"]=$row["gender"];
				$_SESSION["bill_title"]=$row["title"];
				$_SESSION["bill_firstname"]=$row["firstname"];
				$_SESSION["bill_lastname"]=$row["lastname"];
				$_SESSION["bill_street"]=$row["street"];
				$_SESSION["bill_number"]=$row["number"];
				$_SESSION["bill_additional"]=$row["additional"];
				$_SESSION["bill_zip"]=$row["zip"];
				$_SESSION["bill_city"]=$row["city"];
				$_SESSION["bill_country_id"]=$row["country_id"];
				$_SESSION["bill_country"]=$row["country"];
				$_SESSION["bill_standard"]=1;
			}
			$bill_address["adr_id"]=$_SESSION["bill_adr_id"];
			$bill_address["company"]=$_SESSION["bill_company"];
			$bill_address["gender"]=$_SESSION["bill_gender"];
			$bill_address["title"]=$_SESSION["bill_title"];
			$bill_address["firstname"]=$_SESSION["bill_firstname"];
			$bill_address["lastname"]=$_SESSION["bill_lastname"];
			$bill_address["street"]=$_SESSION["bill_street"];
			$bill_address["number"]=$_SESSION["bill_number"];
			$bill_address["additional"]=$_SESSION["bill_additional"];
			$bill_address["zip"]=$_SESSION["bill_zip"];
			$bill_address["city"]=$_SESSION["bill_city"];
			$bill_address["country_id"]=$_SESSION["bill_country_id"];
			$bill_address["country"]=$_SESSION["bill_country"];
			$bill_address["standard"]=$_SESSION["bill_standard"];
		}
		else
		{
			echo '<script> alert("'.t("Es muss mindestens eine Rechnungsanschrift geben").'!"); </script>';
			if(isset($_SESSION["bill_adr_id"]))
			{
				$bill_address["adr_id"]=$_SESSION["bill_adr_id"];
			}		
		}
	}
	elseif(isset($_POST["bill_select"]))
	{
		$bill_address["adr_id"]=$_POST["bill_select"];
		if($_POST["bill_select"]==0)
		{ 
			$bill_address["company"]='';
			$bill_address["gender"]='';
			$bill_address["title"]='';
			$bill_address["firstname"]='';
			$bill_address["lastname"]='';
			$bill_address["street"]='';
			$bill_address["number"]='';
			$bill_address["additional"]='';
			$bill_address["zip"]='';
			$bill_address["city"]='';
			$bill_address["country_id"]=1;
			$bill_address["country"]='';
			$bill_address["standard"]='';
		}
	}
	else
	{
		if(isset($_SESSION["bill_adr_id"]))
		{
			$bill_address["adr_id"]=$_SESSION["bill_adr_id"];
		}		
	}
	
	echo '<table id="bill_edit" style="text-align:left; width:669px;"">';
	echo '	<tr>';
	echo '		<td>'.t("Adresse").'</td>';
	echo '		<td colspan="3">';
	echo '		<select id="bill_adr_id" onchange="bill_select()" style="width:480px; float:left;">';
	echo '			<option selected="selected" value="0">'.t("Neue Rechnugsanschrift").'...</option>';
	
	$results=q("SELECT * FROM shop_bill_adr WHERE user_id=".$_SESSION["id_user"]." and active=1 ORDER BY adr_id;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		if ($row["adr_id"]==$bill_address["adr_id"]) $selected=' selected="selected"';
		elseif (!isset($bill_address["adr_id"]) and $row["standard"]==1) $selected=' selected="selected"'; 
		else $selected='';
		if ($row["company"]!="") $bill_adr_txt=$row["company"].', '; else $bill_adr_txt='';
		if ($row["firstname"]!="" or $row["lastname"]!="")
		{
			$bill_adr_txt.=$row["title"].' ';
			$bill_adr_txt.=$row["firstname"].' ';
			$bill_adr_txt.=$row["lastname"].', ';				
		}
		$bill_adr_txt.=', '.$row["street"].' '.$row["number"].', ';
		if ($row["additional"]!="") $bill_adr_txt.=$row["additional"].', ';
		$bill_adr_txt.=$row["zip"].' '.$row["city"].', '.$row["country"];

		if ($selected!='')
		{
			$bill_address["adr_id"]=$row["adr_id"];
			$bill_address["company"]=$row["company"];
			$bill_address["gender"]=$row["gender"];
			$bill_address["title"]=$row["title"];
			$bill_address["firstname"]=$row["firstname"];
			$bill_address["lastname"]=$row["lastname"];
			$bill_address["street"]=$row["street"];
			$bill_address["number"]=$row["number"];
			$bill_address["additional"]=$row["additional"];
			$bill_address["zip"]=$row["zip"];
			$bill_address["city"]=$row["city"];
			$bill_address["country_id"]=$row["country_id"];
			$bill_address["country"]=$row["country"];
			$bill_address["standard"]=$row["standard"];
		}

		echo '		<option'.$selected.' value="'.$row["adr_id"].'">'.$bill_adr_txt.'</option>';
	}
	echo '		</select>';
	if(isset($bill_address["adr_id"]) and $bill_address["adr_id"]>0)
	{
		echo '		<img style="margin:0; border:0; padding:5px 0px; cursor:pointer; display:inline; float:right;" src="'.PATH.'images/icons/16x16/remove.png" onclick="bill_delete();" alt="'.t("Anschrift löschen").'" title="'.t("Anschrift löschen").'" />';
	}
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td style="width:150px;">'.t("Firma").'</td>';
	echo '		<td colspan="3"><input type="text" id="bill_company" value="'.$bill_address["company"].'" style="width:475px;" /><b style="color:red;"> * </b></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td style="width:150px;">'.t("Anrede").'</td>';
	echo '		<td style="width:150px;">';
	echo '			<select id="bill_gender">';
	if($bill_address["gender"]==0) $selected=' selected="selected"'; else $selected='';
	echo '				<option'.$selected.' value="0">'.t("Herr").'</option>';
	if($bill_address["gender"]==1) $selected=' selected="selected"'; else $selected='';
	echo '				<option'.$selected.' value="1">'.t("Frau").'</option>';
	echo '			</select>';
	echo '		</td>';
	echo '		<td style="width:150px;">'.t("Titel").'</td>';
	echo '		<td style="width:150px;"><input type="text" id="bill_title" value="'.$bill_address["title"].'" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>'.t("Vorname").'</td>';
	echo '		<td><input type="text" id="bill_firstname" value="'.$bill_address["firstname"].'" style="width:140px;" /><b style="color:red;"> * </b></td>';
	echo '		<td>'.t("Nachname").'</td>';
	echo '		<td><input type="text" id="bill_lastname" value="'.$bill_address["lastname"].'" style="width:140px;" /><b style="color:red;"> * </b></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>'.t("Straße").'</td>';
	echo '		<td><input type="text" id="bill_street" value="'.$bill_address["street"].'" style="width:140px;" /><b style="color:red;"> * </b></td>';
	echo '		<td>'.t("Hausnummer").'</td>';
	echo '		<td><input type="text" id="bill_number" value="'.$bill_address["number"].'" style="width:140px;" /><b style="color:red;"> * </b></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>'.t("Adresszusatz").'</td>';
	echo '		<td colspan="3"><input type="text" id="bill_additional" value="'.$bill_address["additional"].'"  style="width:475px;" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>'.t("Postleitzahl").'</td>';
	echo '		<td><input type="text" id="bill_zip" value="'.$bill_address["zip"].'" style="width:140px;" /><b style="color:red;"> * </b></td>';
	echo '		<td>'.t("Ort").'</td>';
	echo '		<td><input type="text" id="bill_city" value="'.$bill_address["city"].'" style="width:140px;" /><b style="color:red;"> * </b></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>'.t("Land").'</td>';
	echo '		<td colspan="3">';
	echo '		<select id="bill_country_id">';

	$shop_countries=array();
	$results2=q("SELECT country_id FROM shop_payment WHERE shop_id=".$_SESSION["id_shop"].";", $dbshop, __FILE__, __LINE__);
	while($row2=mysqli_fetch_array($results2))
	{
		$shop_countries[$row2["country_id"]]=$row2["country_id"];
	}
	$results2=q("SELECT * FROM shop_countries ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
	while($row2=mysqli_fetch_array($results2))
	{
		if (isset($shop_countries[$row2["id_country"]]) or ($_SESSION["id_shop"]!=2 and $_SESSION["id_shop"]!=22))
		{
			if ($bill_address["country_id"]==$row2["id_country"]) $selected=' selected="selected"'; else $selected='';
			echo '<option'.$selected.' value="'.$row2["id_country"].'">'.t($row2["country"]).'</option>';
		}
		
	}
	echo '		</select>';
	echo '		</td>';
	echo '</tr>';
	echo '	<tr>';
	echo '		<td colspan="4">';
	if (isset($bill_address["standard"]) and $bill_address["standard"]==1) $checked=' checked="checked"'; 
	else $checked='';
	echo '			<form>';
	echo '				<input type="checkbox" id="bill_standard" value="1" '.$checked.'>';
	echo 				t("Als Standard Rechnungsanschrift definieren").'!';
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