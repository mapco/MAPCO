<?php
	/*************************
	********** SOA 2 *********
	***** Author Sven E. *****
	*** Firstmod 01.04.2014 ***
	*************************/
	
	check_man_params(
		array( 
			"id_user" => "numericNN",
			"password" => "text"
		)
	);

	$result=q("UPDATE cms_users SET password='".$_POST['password']."' WHERE id_user=".$_POST["id_user"].";", $dbweb, __FILE__, __LINE__);
	if ( mysqli_affected_rows($result) == 1 )
	{
		$message = 'Passwort erfolgreich geändert!';
	}
	elseif ( mysqli_affected_rows($result) > 1 )
	{
		$message = 'Mehr als ein Passwort geändert!';
	}
	else
	{
		$message = 'Passwortänderung fehlgeschlagen!';
	}

	print '<message>'.$message.'</message>'."\n";
?>