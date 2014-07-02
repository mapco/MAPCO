<?php
	include("config.php");
	include("functions/cms_t.php");
	$login_required=true;
	
	include("templates/".TEMPLATE."/header.php");
	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	include("functions/mapco_gewerblich.php");

	echo '<div id="mid_column">';

	//Nutzerdaten auslesen
	$results=q("SELECT * FROM cms_users WHERE id_user=".$_SESSION["id_user"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);

	//UPDATE
	if (isset($_POST["update"]))
	{
	  	//Update Ansprechpartner
		
		if ($_POST["firstname"]==t("Vorname")."...") $_POST["firstname"]="";	
	  	if ($_POST["lastname"]==t("Nachname")."...") $_POST["lastname"]="";	
		if ($_POST["gender"]!=$row["gender"] or $_POST["firstname"]!=$row["firstname"] or $_POST["lastname"]!=$row["lastname"])
		{
			q("UPDATE cms_users SET gender='".$_POST["gender"]."', firstname='".$_POST["firstname"]."', lastname='".$_POST["lastname"]."' WHERE id_user=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
		}
		
		//Update Passwort und Nettopreis verstecken
		if(!isset($_POST["hide_price"])) $_POST["hide_price"]=0;
		if(isset($_POST["pass"]) and $_POST["pass"]!="")
		{
			//**********Verschlüsseln**************
			$pw=md5($_POST["pass"]);
			$pw=md5($pw.$row["user_salt"]);
			$pw=md5($pw.PEPPER);
			q("UPDATE cms_users SET language_id=".$_POST["language_id"].", password='".$pw."', hide_price='".$_POST["hide_price"]."' WHERE id_user=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
			//*************************************
			//q("UPDATE cms_users SET language_id=".$_POST["language_id"].", password='".$_POST["pass"]."', hide_price='".$_POST["hide_price"]."' WHERE id_user=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
		}
		else
		{
			q("UPDATE cms_users SET language_id=".$_POST["language_id"].", hide_price='".$_POST["hide_price"]."' WHERE id_user=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
			
		}

		$results2=q("SELECT * FROM cms_languages WHERE id_language=".$_POST["language_id"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		if ($_SESSION["lang"]!=$row2["code"])
		{
			$_SESSION["lang"]=$row2["code"];
			header("HTTP/1.1 303 See Other");
			header("location: ".PATH.$_SESSION["lang"].'/'.$_GET["url"]);
			exit;
		}
		
		//Update Newsletter
		if(isset($_POST["receive_news"]) and $_POST["receive_news"]==1)
		{
			q("UPDATE cms_users SET newsletter=1 WHERE id_user=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
		}
		else
		{
			q("UPDATE cms_users SET newsletter=0 WHERE id_user=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
		}

		echo '<div class="success">'.t("Die Einstellungen wurden erfolgreich gespeichert.", __FILE__, __LINE__).'</div>';

		//gespeicherte Nutzerdaten neu auslesen
		$results=q("SELECT * FROM cms_users WHERE id_user=".$_SESSION["id_user"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
	}

	//PATH
	echo '<p>';
	echo '<a href="'.PATHLANG.'online-shop/mein-konto/">Mein Konto</a>';
	echo ' > '.t("Benutzerkonto");
	echo '</p>';

	$pl=array(0 => t("Bruttopreisliste"), 1 => t("Werksverkaufsliste", __FILE__, __LINE__), 2 => t("Werksverkaufsliste", __FILE__, __LINE__), 3 => t("Blau", __FILE__, __LINE__), 4 => t("Grün", __FILE__, __LINE__), 5 => t("Gelb", __FILE__, __LINE__), 6 => t("Orange", __FILE__, __LINE__), 7 => t("Rot", __FILE__, __LINE__));

	//Preisgruppe
	$gewerblich=gewerblich($_SESSION["id_user"]);
	$preisgr=2;
	$results2=q("SELECT * FROM kunde WHERE ADR_ID='".$row["idims_adr_id"]."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($results2)>0)
	{
		$row2=mysqli_fetch_array($results2);
		$preisgr=$row2["PREISGR"];
	}
	
	//general user information
	echo '<h1>'.t("Allgemeine Angaben", __FILE__, __LINE__).'</h1>';
	echo '<form method="post">';
	echo '<table class="hover">';
	echo '	<tr>';
	echo '		<th>'.t("Beschreibung", __FILE__, __LINE__).'</th>';
	echo '		<th>'.t("Wert", __FILE__, __LINE__).'</th>';
	echo '	</tr>';

	//Ansprechpartner
	echo '<tr>';
	echo '	<td>'.t("Ansprechpartner", __FILE__, __LINE__).'</td>';
	echo '	<td>';
	echo '		<select name="gender">';
					if($row["gender"]==0) $selected=' selected="selected"'; else $selected='';
	echo '			<option'.$selected.'  value="0">'.t("Herr").'</option>';
					if($row["gender"]==1) $selected=' selected="selected"'; else $selected='';
	echo '			<option'.$selected.' value="1">'.t("Frau").'</option>';
	echo '		</select>';
				if(!isset($row["firstname"]) or $row["firstname"]=="") $firstname_value=t("Vorname")."...";
				else $firstname_value=$row["firstname"];
	echo '   	<input type="text" name="firstname" value="'.$firstname_value.'" onfocus="if (this.value==\''.t("Vorname").'...'.'\') this.value=\'\';" onblur="if (this.value==\'\') this.value=\''.t("Vorname").'...'.'\';" />';
	echo '		 ';
				if(!isset($row["lastname"]) or $row["lastname"]=="") $lastname_value=t("Nachname")."...";
				else $lastname_value=$row["lastname"];
	echo '   	<input type="text" name="lastname" value="'.$lastname_value.'" onfocus="if (this.value==\''.t("Nachname").'...'.'\') this.value=\'\';" onblur="if (this.value==\'\') this.value=\''.t("Nachname").'...'.'\';" />';
	echo '	</td>';
	echo '</tr>';

	//Kundennummer
	echo '<tr>';
	echo '	<td>'.t("Benutzername", __FILE__, __LINE__).'</td>';
	echo '	<td>'.$row["username"].'</td>';
	echo '</tr>';

	//Passwort
	if (!$gewerblich) 
	{
		echo '<tr>';
		echo '	<td>'.t("Passwort", __FILE__, __LINE__).'</td>';
		echo '	<td><input type="password" name="pass" value="'.$row["password"].'" /></td>';
		echo '</tr>';
	}

	//E-Mail
	echo '<tr>';
	echo '	<td>'.t("E-Mail", __FILE__, __LINE__).'</td>';
	echo '	<td>'.$row["usermail"].'</td>';
	echo '</tr>';

	//Sprache
	echo '<tr>';
	echo '	<td>'.t("Sprache", __FILE__, __LINE__).'</td>';
	echo '	<td>';
	echo '		<select name="language_id">';
	$results2=q("SELECT * FROM cms_languages ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
	while($row2=mysqli_fetch_array($results2))
	{
		if ($row2["id_language"]==$row["language_id"]) $selected=' selected="selected"'; else $selected='';
		echo '<option'.$selected.' value="'.$row2["id_language"].'">'.t($row2["language"], __FILE__, __LINE__).'</option>';
	}
	echo '		</select>';
	echo '	</td>';
	echo '</tr>';

	//Kundentyp
	echo '<tr>';
	echo '	<td>'.t("Kundentyp", __FILE__, __LINE__).'</td>';
	if ($gewerblich) $type=t("Gewerbskunde", __FILE__, __LINE__); else $type=t("Endverbraucher", __FILE__, __LINE__).'<a href="'.PATHLANG.'gewerberegistrierung/"> ('.t("Gewerbekunde werden").')</a>';
	echo '	<td>'.$type.'</td>';
	echo '</tr>';

	//Preisliste
	echo '<tr>';
	echo '	<td>'.t("Preisliste", __FILE__, __LINE__).'</td>';
	echo '	<td>'.$pl[$preisgr].'</td>';
	echo '</tr>';
	
	//Newsletter Anmeldung
	if ($row["newsletter"]>0) $checked='checked="checked"';
	else $checked='';
	echo '<tr>';
	echo '	<td>'.t("Newsletter", __FILE__, __LINE__).'</td>';
	echo '	<td><input type="checkbox" name="receive_news" value="1" '.$checked.' /> '.t("Ja ich möchte den MAPCO-Newsletter empfangen", __FILE__, __LINE__).'!</td>';
	echo '</tr>';
	
	//Herbstaktion 2013
//	if(!$gewerblich and time()>=1382306400 and time()<=1385333999)
		{
			$user_deposit=0;
			$results2=q("SELECT * FROM shop_user_deposit WHERE user_id=".$_SESSION["id_user"].";", $dbshop, __FILE__, __LINE__);
			if(mysqli_num_rows($results2)>0)
			{
				$row2=mysqli_fetch_array($results2);
				$user_deposit=$row2["deposit"];
			}
			round($user_deposit, 2);
			if($user_deposit>0)
			{	
				echo '<tr>';
				echo '	<td>'.t("Guthaben Rabattaktion", __FILE__, __LINE__).'</td>';
				echo '	<td>'.number_format($user_deposit, 2).' €</td>';
				echo '</tr>';
			}
		}
	//Ende Herbstaktion
	
	//Preisanzeige
	if ($gewerblich) 
	{
		if ($row["hide_price"]==1) $checked='checked="checked"';
		else $checked='';
		echo '<tr>';
		echo '	<td>'.t("Preisanzeige", __FILE__, __LINE__).'</td>';
		echo '	<td><input type="checkbox" name="hide_price" value="1" '.$checked.' /> '.t("Nettopreis verstecken", __FILE__, __LINE__).'</td>';
		echo '</tr>';
	}
	
	echo '<tr>';
	echo '	<td colspan="2"><input style="float:right;" type="submit" name="update" value="'.t("Speichern", __FILE__, __LINE__).'" /></td>';
	echo '</tr>';
	echo '</table>';
	echo '</form>';

	echo '</div>';

	include("templates/".TEMPLATE."/cms_rightcolumn.php");
	include("templates/".TEMPLATE."/footer.php");
?>