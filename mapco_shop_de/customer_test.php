<?php
	include("config.php");
	include("templates/".TEMPLATE."/header.php");
	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	
	echo '<div id="mid_column">';
	
	if (isset($_POST["form_submit"]))
	{
		if($_POST["form_name"]=="") echo '<div class="failure">Der Name darf nicht leer sein.</div>';
		elseif($_POST["form_strasse"]=="") echo '<div class="failure">Die Strasse darf nicht leer sein.</div>';
		elseif($_POST["form_plz"]=="") echo '<div class="failure">Die Postleitzahl darf nicht leer sein.</div>';
		elseif($_POST["form_ort"]=="") echo '<div class="failure">Der Ort darf nicht leer sein.</div>';
		elseif($_POST["form_tel"]=="") echo '<div class="failure">Die Telefonnummer darf nicht leer sein.</div>';
		elseif ($_POST["form_mail"]=="") echo '<div class="failure">Die E-Mail-Adresse darf nicht leer sein!</div>';
		elseif (strpos($_POST["form_mail"], "@")==0 or strpos($_POST["form_mail"], ".")==0) echo '<div class="failure">Sie müssen eine gültige E-Mail-Adresse angeben</div>';
//		elseif($_POST["form_mail"]=="") echo '<div class="failure">Die E-Mail darf nicht leer sein.</div>';
		elseif($_POST["form_steuer"]=="") echo '<div class="failure">Die Steuernummer darf nicht leer sein.</div>';
		elseif ($_FILES["form_file"]["tmp_name"]=="") echo '<div class="failure">Es konnte keine Datei gefunden werden. Bitte Dokument mit hochladen!!!</div>';
		else
		{
				
			$mail = '<html>';
			$mail .= '<head>';
			$mail .= '<title>Neukunde</title>';
			$mail .= '</head>';
			$mail .= '<body>';
			$mail .= '<p>Neukundenanlage:</p>';
			$mail .= '<table>';
			$mail .= '<tr><td>Name:</td><td>'.$_POST["form_name"].'</td></tr>';
			$mail .= '<tr><td>Strasse:</td><td>'.$_POST["form_strasse"].'</td></tr>';
			$mail .= '<tr><td>PLZ:</td><td>'.$_POST["form_plz"].'</td></tr>';
			$mail .= '<tr><td>Ort:</td><td>'.$_POST["form_ort"].'</td></tr>';
			$mail .= '<tr><td>Tel:</td><td>'.$_POST["form_tel"].'</td></tr>';
			$mail .= '<tr><td>Fax:</td><td>'.$_POST["form_fax"].'</td></tr>';
			$mail .= '<tr><td>E-Mail:</td><td>'.$_POST["form_mail"].'</td></tr>';
			$mail .= '<tr><td>Steuer-Nr.:</td><td>'.$_POST["form_steuer"].'</td></tr>';
			$mail .= '<tr><td colspan="2">--------------------------------------------------</td></tr>';
			if (!$_POST["form_lname"]=="")
			{
				$mail .= '<tr><td colspan="2">Lieferanschrift</td></tr>';
				$mail .= '<tr><td>Name:</td><td>'.$_POST["form_lname"].'</td></tr>';
				$mail .= '<tr><td>Strasse:</td><td>'.$_POST["form_lstrasse"].'</td></tr>';
				$mail .= '<tr><td>PLZ:</td><td>'.$_POST["form_lplz"].'</td></tr>';
				$mail .= '<tr><td>Ort:</td><td>'.$_POST["form_lort"].'</td></tr>';
				$mail .= '<tr><td>Tel:</td><td>'.$_POST["form_ltel"].'</td></tr>';
				$mail .= '<tr><td>Fax:</td><td>'.$_POST["form_lfax"].'</td></tr>';
			}
		    $mail .= '</table>';
  		  	$mail .= '</body>';
  			$mail .= '</html>';
			SendMail('habermann.jens@googlemail.com', $_POST["form_mail"], 'Neukunde', $mail, $_FILES["form_file"]["tmp_name"], $_FILES["form_file"]["name"]);
			echo '<div class="success">Vielen Dank! Ihr Account wird angelegt.</div>';
		}
	}
	
	$form  = '<form method="post" enctype="multipart/form-data">';
	$form .= '<table class="customer">';
	$form .= '	<tr><td>Name</td><td><input type="text" name="form_name" value="'.$_POST["form_name"].'" /> <span>*</span></td></tr>';
	$form .= '	<tr><td>Strasse/Nr.</td><td><input type="text" name="form_strasse" value="'.$_POST["form_strasse"].'" /> <span>*</span></td></tr>';
	$form .= '	<tr><td>PLZ</td><td><input type="text" name="form_plz" value="'.$_POST["form_plz"].'" /> <span>*</span></td></tr>';
	$form .= '	<tr><td>Ort</td><td><input type="text" name="form_ort" value="'.$_POST["form_ort"].'" /> <span>*</span></td></tr>';
	$form .= '	<tr><td>Telefon</td><td><input type="text" name="form_tel" value="'.$_POST["form_tel"].'" /> <span>*</span></td></tr>';
	$form .= '	<tr><td>Fax</td><td><input type="text" name="form_fax" value="'.$_POST["form_fax"].'" /></td></tr>';
	$form .= '	<tr><td>E-Mail</td><td><input type="text" name="form_mail" value="'.$_POST["form_mail"].'" /> <span>*</span></td></tr>';
	$form .= '	<tr><td>Steuer-Nr.</td><td><input type="text" name="form_steuer" value="'.$_POST["form_steuer"].'" /> <span>*</span></td></tr>';
	$form .= '	<tr><td>Datei (Gewerbeanmeldung)</td><td><input type="file" name="form_file" value="'.$_POST["form_file"].'" /> <span>*</span></td></tr>';
	$form .= '  <tr><td></td><td><span>*</span> = Pflichtfelder</td></tr>';
	$form .= '  <tr><td colspan="2"></td></tr>';
	$form .= '  <tr><td colspan="2"><h3>Lieferanschrift (falls abweichend)</h3></td></tr>';
	$form .= '	<tr><td>Name</td><td><input type="text" name="form_lname" value="'.$_POST["form_lname"].'" /></td></tr>';
	$form .= '	<tr><td>Strasse/Nr.</td><td><input type="text" name="form_lstrasse" value="'.$_POST["form_lstrasse"].'" /></td></tr>';
	$form .= '	<tr><td>PLZ</td><td><input type="text" name="form_lplz" value="'.$_POST["form_lplz"].'" /></td></tr>';
	$form .= '	<tr><td>Ort</td><td><input type="text" name="form_lort" value="'.$_POST["form_lort"].'" /></td></tr>';
	$form .= '	<tr><td>Telefon</td><td><input type="text" name="form_ltel" value="'.$_POST["form_ltel"].'" /></td></tr>';
	$form .= '	<tr><td>Fax</td><td><input type="text" name="form_lfax" value="'.$_POST["form_lfax"].'" /></td></tr>';
	$form .= '</table>';
	$form .= '<input type="submit" name="form_submit" />';
	$form .= '</form>';
	$text=show_article(135, 0);
	echo str_replace("<!-- FORM -->", $form, $text);
	
	echo '</div>';
	include("templates/".TEMPLATE."/cms_rightcolumn.php");
	include("templates/".TEMPLATE."/footer.php");
?>