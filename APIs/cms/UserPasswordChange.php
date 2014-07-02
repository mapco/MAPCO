<?php

	check_man_params(array( "pw" => "text"));
	
	//user-Daten holen
	$results=q("SELECT * FROM cms_users WHERE id_user=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$salt=$row["user_salt"];
	
	//übergebenes Passwort verschlüsseln
	$pw=md5($_POST["pw"]);
	$pw=md5($pw.$salt);
	$pw=md5($pw.PEPPER);
	
	$results2=q("UPDATE cms_users SET password='".$pw."' WHERE id_user=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);

?>