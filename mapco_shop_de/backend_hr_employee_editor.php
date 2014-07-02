<?php
	include("config.php");
	$leftmenu=true;
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_hr_index.php">Personalverwaltung</a>';
	echo ' > <a href="backend_hr_employees.php">Mitarbeiter</a>';
	echo ' > Editor';
	echo '</p>';


	//Upload
	if (isset($_POST["upload_submit"]))
	{
		if ($_FILES["file"]["name"]=="")
		{
			echo '<div class="failure">Bitte erst eine Datei auswählen!</div>';
		}
		else 
		{
			if ($_FILES["file"]["type"]!="image/jpeg")
			{
				echo '<div class="failure">Es sind nur Bild-Dateien im JPG Format erlaubt!</div>';
			}
			else
			{	
				$results=q("SELECT * FROM hr_employees WHERE id_employee=".$_GET["id_employee"].";", $dbweb, __FILE__, __LINE__);
				$row=mysqli_fetch_array($results);
				$filename="images/employees/".substr($row["mail"], 0, strpos($row["mail"], "@"))."2.jpg";
				$file="images/employees/".substr($row["mail"], 0, strpos($row["mail"], "@")).".jpg";
				move_uploaded_file($_FILES['file']['tmp_name'], $filename);
				unset($FILES);
				require_once('modules/phpThumb/phpthumb.class.php');
				$phpThumb = new phpThumb();
				$phpThumb->setSourceFilename('../../'.$filename);
				$phpThumb->w = 100;
				$phpThumb->h = 150;
				$phpThumb->aoe = 1; //vergrößere kleinere fotos
				$phpThumb->config_output_format = 'jpeg';
				$phpThumb->config_error_die_on_error = false;
				if ($phpThumb->GenerateThumbnail())
				{
					if (!$phpThumb->RenderToFile('../../'.$file))
					{
						echo 'ERROR: '.implode("<br />", $phpThumb->debugmessages);
					}
				}
				else
				{
					echo 'ERROR: '.implode("<br />", $phpThumb->debugmessages);
				}
	//				echo '<div class="success">Der Upload war erfolgreich!</div>';
				}
		}
	}

	//CREATE
	if (isset($_POST["create"]))
    {
		if ($_POST["firstname"]=="" or $_POST["lastname"]=="") echo '<div class="failure">Die Felder Vorname und Nachname dürfen nicht leer sein</div>';
		else
        {
			q("INSERT INTO hr_employees (firstname, middlename, lastname, position, department, street, street_nr, zip, city, country, phone, fax, mobile, mail, user_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".addslashes(stripslashes($_POST["firstname"]))."', '".addslashes(stripslashes($_POST["middlename"]))."', '".addslashes(stripslashes($_POST["lastname"]))."', '".addslashes(stripslashes($_POST["position"]))."', '".addslashes(stripslashes($_POST["department"]))."', '".addslashes(stripslashes($_POST["street"]))."', '".addslashes(stripslashes($_POST["street_nr"]))."', '".addslashes(stripslashes($_POST["zip"]))."', '".addslashes(stripslashes($_POST["city"]))."', '".addslashes(stripslashes($_POST["country"]))."', '".addslashes(stripslashes($_POST["phone"]))."', '".addslashes(stripslashes($_POST["fax"]))."', '".addslashes(stripslashes($_POST["mobile"]))."', '".addslashes(stripslashes($_POST["mail"]))."', 0, '".time()."', '".$_SESSION["id_user"]."', '".time()."', '".$_SESSION["id_user"]."');", $dbweb, __FILE__, __LINE__);
			$_GET["id_employee"]=mysqli_insert_id($dbweb);
			echo '<div class="success">Mitarbeiterprofil erfolgreich angelegt!</div>';
        }
	}

	//UPDATE
	if (isset($_POST["update"]))
    {
		if ($_POST["firstname"]=="" or $_POST["lastname"]=="") echo '<div class="failure">Die Felder Vorname und Nachname dürfen nicht leer sein</div>';
		else
        {
			q("UPDATE hr_employees
						 SET firstname='".addslashes(stripslashes($_POST["firstname"]))."',
						 	 middlename='".addslashes(stripslashes($_POST["middlename"]))."',
						 	 lastname='".addslashes(stripslashes($_POST["lastname"]))."',
						 	 position='".addslashes(stripslashes($_POST["position"]))."',
						 	 department='".addslashes(stripslashes($_POST["department"]))."',
						 	 street='".addslashes(stripslashes($_POST["street"]))."',
						 	 street_nr='".addslashes(stripslashes($_POST["street_nr"]))."',
						 	 zip='".addslashes(stripslashes($_POST["zip"]))."',
						 	 city='".addslashes(stripslashes($_POST["city"]))."',
						 	 country='".addslashes(stripslashes($_POST["country"]))."',
						 	 phone='".addslashes(stripslashes($_POST["phone"]))."',
						 	 fax='".addslashes(stripslashes($_POST["fax"]))."',
						 	 mobile='".addslashes(stripslashes($_POST["mobile"]))."',
						 	 mail='".addslashes(stripslashes($_POST["mail"]))."',
						 	 user_id='".$_POST["user_id"]."',
						 	 lastmod='".time()."',
						 	 lastmod_user='".$_SESSION["id_user"]."'
						 WHERE id_employee=".$_GET["id_employee"].";", $dbweb, __FILE__, __LINE__);
			echo '<div class="success">Mitarbeiterprofil erfolgreich aktualisiert!</div>';
        }
    }

	//READ
	if (isset($_GET["id_employee"]))
	{
		$results=q("SELECT * FROM hr_employees WHERE id_employee=".$_GET["id_employee"].";", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		if ($_POST["firstname"]=="") $_POST["firstname"]=$row["firstname"];
		if ($_POST["middlename"]=="") $_POST["middlename"]=$row["middlename"];
		if ($_POST["lastname"]=="") $_POST["lastname"]=$row["lastname"];
		if ($_POST["position"]=="") $_POST["position"]=$row["position"];
		if ($_POST["department"]=="") $_POST["department"]=$row["department"];
		if ($_POST["street"]=="") $_POST["street"]=$row["street"];
		if ($_POST["street_nr"]=="") $_POST["street_nr"]=$row["street_nr"];
		if ($_POST["zip"]=="") $_POST["zip"]=$row["zip"];
		if ($_POST["city"]=="") $_POST["city"]=$row["city"];
		if ($_POST["country"]=="") $_POST["country"]=$row["country"];
		if ($_POST["phone"]=="") $_POST["phone"]=$row["phone"];
		if ($_POST["fax"]=="") $_POST["fax"]=$row["fax"];
		if ($_POST["mobile"]=="") $_POST["mobile"]=$row["mobile"];
		if ($_POST["mail"]=="") $_POST["mail"]=$row["mail"];
		if ($_POST["user_id"]=="") $_POST["user_id"]=$row["user_id"];
	}

	//EDITOR
	echo '<h1>Mitarbeiter-Editor</h1>';
	if (isset($_GET["id_employee"]))
	{
		echo '<form action="backend_hr_employee_editor.php?id_employee='.$_GET["id_employee"].'" method="post">';
	}
	else
	{
		echo '<form action="backend_hr_employee_editor.php" method="post">';
	}

	//image
	echo '<table style="margin-right:10px; float:left;">';
    echo '	<tr><th colspan="2">Foto</th></tr>';
	echo '	<tr><td>';
	if ($_GET["id_employee"]>0)
	{
		$results=q("SELECT * FROM hr_employees WHERE id_employee=".$_GET["id_employee"].";", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
	//	echo '<a href="javascript:popup(\'modules/backend_hr_employee_img_upload.php?id_employee='.$_GET["id_employee"].'\',500,150);">';
		echo '<a href="javascript:showhide(\'img_upload\');">';
		$mail="images/employees/".substr($_POST["mail"], 0, strpos($_POST["mail"], "@")).'.jpg';
		if (file_exists($mail)) echo '<img src="'.$mail.'?'.rand(0, 999999).'" />';
		else echo '<img src="images/employees/0employee.jpg" />';
		echo '</a>';
	}
	else echo '<img src="images/employees/0employee.jpg" />';
	echo '</td></tr></table>';

	echo '<table style="float:left;">';
    echo '	<tr><th colspan="2">Allgemeine Angaben</th></tr>';
	echo '	<tr>';
	echo '		<td>Vorname</td>';
	echo '		<td><input style="width:200px;" type="text" name="firstname" value="'.$_POST["firstname"].'" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>2. Vorname</td>';
	echo '		<td><input style="width:200px;" type="text" name="middlename" value="'.$_POST["middlename"].'" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Nachname</td>';
	echo '		<td><input style="width:200px;" type="text" name="lastname" value="'.$_POST["lastname"].'" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Position</td>';
	echo '		<td><input style="width:200px;" type="text" name="position" value="'.$_POST["position"].'" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Abteilung</td>';
	echo '		<td><input style="width:200px;" type="text" name="department" value="'.$_POST["department"].'" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Straße und Nr</td>';
	echo '		<td>';
	echo '			<input style="width:200px;" type="text" name="street" value="'.$_POST["street"].'" />';
	echo '			<input style="width:30px;" type="text" name="street_nr" value="'.$_POST["street_nr"].'" />';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>PLZ</td>';
	echo '		<td><input style="width:200px;" type="text" name="zip" value="'.$_POST["zip"].'" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Ort</td>';
	echo '		<td><input style="width:200px;" type="text" name="city" value="'.$_POST["city"].'" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Land</td>';
	echo '		<td><input style="width:200px;" type="text" name="country" value="'.$_POST["country"].'" /></td>';
	echo '	</tr>';
    echo '	<tr><th colspan="2">Kommunikation</th></tr>';
	echo '	<tr>';
	echo '		<td>Telefon</td>';
	echo '		<td><input style="width:200px;" type="text" name="phone" value="'.$_POST["phone"].'" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Telefax</td>';
	echo '		<td><input style="width:200px;" type="text" name="fax" value="'.$_POST["fax"].'" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Mobil</td>';
	echo '		<td><input style="width:200px;" type="text" name="mobile" value="'.$_POST["mobile"].'" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>E-Mail</td>';
	echo '		<td><input style="width:200px;" type="text" name="mail" value="'.$_POST["mail"].'" /></td>';
	echo '	</tr>';
	/*
    echo '	<tr><th colspan="2">Benutzerprofil</th></tr>';
	echo '	<tr>';
	echo '		<td>Benutzer</td>';
	echo '		<td>';
	echo '			<select name="user_id">';
	echo '				<option'.$selected.' value="0">kein Benutzerprofil</option>';
	$results=q("SELECT * FROM cms_users ORDER BY username;", $dbweb, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($results))
	{
		if ($_POST["user_id"]==$row["id_user"]) $selected=' selected="selected"'; else $selected='';
		echo '				<option'.$selected.' value="'.$row["id_user"].'">'.$row["username"].'</option>';
	}
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	*/
	if (isset($_GET["id_employee"]))
	{
		echo '	<tr><td colspan="2"><input class="formbutton" type="submit" name="update" value="Mitarbeiter aktualisieren" /></td></tr>';
	}
	else
	{
		echo '	<tr><td colspan="2"><input class="formbutton" type="submit" name="create" value="Mitarbeiter anlegen" /></td></tr>';
	}
	echo '</table>';

	echo '</form>';


	//IMAGE UPLOAD WINDOW
	echo '<div id="img_upload" class="popup" style="display:none;">';
	echo '<form action="?id_employee='.$_GET["id_employee"].'" method="post" enctype="multipart/form-data">';
	echo '<table style="position:absolute; left:50%; top:50%; width:320px; height:150px; margin-left:-160px; margin-top:-75px; background:#ffffff;">';
	echo '	<tr>';
	echo '		<th colspan="2">';
	echo '			<span style="display:inline; float:left;">Foto hochladen</span>';
	echo '			<img style="margin:0; border:0; padding:0; cursor:pointer; display:inline; float:right;" src="images/icons/16x16/remove.png" onclick="showhide(\'img_upload\');" alt="Schließen" title="Schließen" />';
	echo '		</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Datei</td>';
	echo '		<td>';
	echo '			<input type="file" name="file">';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2"><input class="formbutton" type="submit" name="upload_submit" value="Hochladen"></td>';
	echo '	</tr>';
	echo '</table>';
	echo '</form>';
	echo '</div>';


	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>