<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_cms_index.php">Content Management</a>';
	echo ' > <a href="backend_mapco_cup.php">MAPCO Racing Cup</a>';
	echo ' > Teilnehmer-Editor';
	echo '</p>';
	
	
	//CREATE
	if(isset($_POST["user_create"]))
	{
		if ($_POST["firstname"]=="") echo '<div class="failure">Das Feld Vorname darf nicht leer sein.</div>';
		elseif ($_POST["lastname"]=="") echo '<div class="failure">Das Feld Nachname darf nicht leer sein.</div>';
		elseif ($_POST["street"]=="") echo '<div class="failure">Das Feld Straße darf nicht leer sein.</div>';
		elseif ($_POST["street_nr"]=="") echo '<div class="failure">Das Feld Hausnummer darf nicht leer sein.</div>';
		elseif ($_POST["zip"]=="") echo '<div class="failure">Das Feld Postleitzahl darf nicht leer sein.</div>';
		elseif ($_POST["city"]=="") echo '<div class="failure">Das Feld Ort darf nicht leer sein.</div>';
		elseif ($_POST["country"]=="") echo '<div class="failure">Das Feld Land darf nicht leer sein.</div>';
		elseif ($_POST["usermail"]=="") echo '<div class="failure">Das Feld E-Mail-Adresse darf nicht leer sein.</div>';
		elseif ($_POST["time_min"]=="" or $_POST["time_sec"]=="" or $_POST["time_mil"]=="") echo '<div class="failure">Das Feld Zeit darf nicht leer sein.</div>';
		elseif ($_POST["accept"]=="") echo '<div class="failure">Sie müssen den Teilnahmebedingungen zustimmen.</div>';
		else
		{
			$query="INSERT INTO mapco_cup (company, salutation, firstname, middlename, lastname, street, street_nr, street_additional, zip, city, country, usermail, time_min, time_sec, time_mil, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".addslashes(stripslashes($_POST["company"]))."', '".addslashes(stripslashes($_POST["salutation"]))."', '".addslashes(stripslashes($_POST["firstname"]))."', '".addslashes(stripslashes($_POST["middlename"]))."', '".addslashes(stripslashes($_POST["lastname"]))."', '".addslashes(stripslashes($_POST["street"]))."', '".addslashes(stripslashes($_POST["street_nr"]))."', '".addslashes(stripslashes($_POST["street_additional"]))."', '".addslashes(stripslashes($_POST["zip"]))."', '".addslashes(stripslashes($_POST["city"]))."', '".addslashes(stripslashes($_POST["country"]))."', '".addslashes(stripslashes($_POST["usermail"]))."', '".addslashes(stripslashes($_POST["time_min"]))."', '".addslashes(stripslashes($_POST["time_sec"]))."', '".addslashes(stripslashes($_POST["time_mil"]))."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");";
			q($query, $dbweb, __FILE__, __LINE__);
			$_GET["id_user"]=mysqli_insert_id($dbweb);
			echo '<div class="success">Teilnehmer erfolgreich gespeichert.</div>';
		}
	}
	
	
	//UPDATE
	if(isset($_POST["user_update"]))
	{
		if (!($_GET["id_user"]>0)) echo '<div class="failure">Der Benutzer konnte nicht ermittel werden.</div>';
		elseif ($_POST["firstname"]=="") echo '<div class="failure">Das Feld Vorname darf nicht leer sein.</div>';
		elseif ($_POST["lastname"]=="") echo '<div class="failure">Das Feld Nachname darf nicht leer sein.</div>';
		elseif ($_POST["street"]=="") echo '<div class="failure">Das Feld Straße darf nicht leer sein.</div>';
		elseif ($_POST["street_nr"]=="") echo '<div class="failure">Das Feld Hausnummer darf nicht leer sein.</div>';
		elseif ($_POST["zip"]=="") echo '<div class="failure">Das Feld Postleitzahl darf nicht leer sein.</div>';
		elseif ($_POST["city"]=="") echo '<div class="failure">Das Feld Ort darf nicht leer sein.</div>';
		elseif ($_POST["country"]=="") echo '<div class="failure">Das Feld Land darf nicht leer sein.</div>';
		elseif ($_POST["usermail"]=="") echo '<div class="failure">Das Feld E-Mail-Adresse darf nicht leer sein.</div>';
		elseif ($_POST["time_min"]=="" or $_POST["time_sec"]=="" or $_POST["time_mil"]=="") echo '<div class="failure">Das Feld Zeit darf nicht leer sein.</div>';
		elseif ($_POST["accept"]=="") echo '<div class="failure">Sie müssen den Teilnahmebedingungen zustimmen.</div>';
		else
		{
			$query="UPDATE mapco_cup
					SET company='".addslashes(stripslashes($_POST["company"]))."',
						salutation='".addslashes(stripslashes($_POST["salutation"]))."',
						firstname='".addslashes(stripslashes($_POST["firstname"]))."',
						middlename='".addslashes(stripslashes($_POST["middlename"]))."',
						lastname='".addslashes(stripslashes($_POST["lastname"]))."',
						street='".addslashes(stripslashes($_POST["street"]))."',
						street_nr='".addslashes(stripslashes($_POST["street_nr"]))."',
						street_additional='".addslashes(stripslashes($_POST["street_additional"]))."',
						zip='".addslashes(stripslashes($_POST["zip"]))."',
						city='".addslashes(stripslashes($_POST["city"]))."',
						country='".addslashes(stripslashes($_POST["country"]))."',
						usermail='".addslashes(stripslashes($_POST["usermail"]))."',
						time_min='".addslashes(stripslashes($_POST["time_min"]))."',
						time_sec='".addslashes(stripslashes($_POST["time_sec"]))."',
						time_mil='".addslashes(stripslashes($_POST["time_mil"]))."',
						lastmod=".time().",
						lastmod_user=".$_SESSION["id_user"]."
						WHERE id_user=".$_GET["id_user"].";";
			q($query, $dbweb, __FILE__, __LINE__);
			echo '<div class="success">Teilnehmer erfolgreich aktualisiert.</div>';
		}
	}
	
	
	//READ
	if ($_GET["id_user"]>0)
	{
		$results=q("SELECT * FROM mapco_cup WHERE id_user=".$_GET["id_user"].";", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$_POST["company"]=$row["company"];
		$_POST["salutation"]=$row["salutation"];
		$_POST["firstname"]=$row["firstname"];
		$_POST["middlename"]=$row["middlename"];
		$_POST["lastname"]=$row["lastname"];
		$_POST["street"]=$row["street"];
		$_POST["street_nr"]=$row["street_nr"];
		$_POST["street_additional"]=$row["street_additional"];
		$_POST["zip"]=$row["zip"];
		$_POST["city"]=$row["city"];
		$_POST["country"]=$row["country"];
		$_POST["usermail"]=$row["usermail"];
		$_POST["time_min"]=$row["time_min"];
		$_POST["time_sec"]=$row["time_sec"];
		$_POST["time_mil"]=$row["time_mil"];
	}
	if ($_POST["country"]=="") $_POST["country"]="Deutschland";


	//VIEW
	echo '<h1>MAPCO Racing Cup</h1>';
	if ($_GET["id_user"]>0) echo '<form method="post" action="?id_user='.$_GET["id_user"].'">'; else echo '<form method="post">';
	echo '<table>';
	echo '	<tr>';
	echo '		<th colspan="2">Angaben zur Person</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Firma</td>';
	echo '		<td><input type="text" name="company" value="'.$_POST["company"].'" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Anrede</td>';
	echo '		<td><input type="text" name="salutation" value="'.$_POST["salutation"].'" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Vornamen</td>';
	echo '		<td><input type="text" name="firstname" value="'.$_POST["firstname"].'" /> <input type="text" name="middlename" value="'.$_POST["middlename"].'" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Nachname</td>';
	echo '		<td><input type="text" name="lastname" value="'.$_POST["lastname"].'" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Straße und Nr.</td>';
	echo '		<td><input type="text" name="street" value="'.$_POST["street"].'" /> <input style="width:30px;" type="text" name="street_nr" value="'.$_POST["street_nr"].'" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>PLZ und Ort</td>';
	echo '		<td><input style="width:50px;" type="text" name="zip" value="'.$_POST["zip"].'" /> <input type="text" name="city" value="'.$_POST["city"].'" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Land</td>';
	echo '		<td><input type="text" name="country" value="'.$_POST["country"].'" /></td>';
	echo '	</tr>';
	echo '</table>';
	echo '<table>';
	echo '	<tr>';
	echo '		<th colspan="2">Angaben zum Gewinnspiel</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>E-Mail-Adresse</td>';
	echo '		<td><input type="text" name="usermail" value="'.$_POST["usermail"].'" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Zeit</td>';
	echo '		<td><input style="width:30px;" type="text" name="time_min" value="'.$_POST["time_min"].'" /> : <input style="width:30px;" type="text" name="time_sec" value="'.$_POST["time_sec"].'" />´ <input style="width:30px;" type="text" name="time_mil" value="'.$_POST["time_mil"].'" />´´</td>';
	echo '	</tr>';
	if ($_GET["id_user"]>0)
	{
		echo '<input type="hidden" name="accept" value="on" />';
	}
	else
	{
		echo '<tr>';
		echo '	<td colspan="2"><input type="checkbox" name="accept" /> Ich stimme den <a target="_blank" href="cup_rules.php">Teilnahmebedingungen</a> des MAPCO Racing Cup Gewinnspieles zu.</td>';
		echo '</tr>';
	}
	echo '</table>';
	if ($_GET["id_user"])
	{
		echo '<input type="submit" name="user_update" value="Speichern" />';
	}
	else
	{
		echo '<input type="submit" name="user_create" value="Speichern" />';
	}
	echo '</form>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>