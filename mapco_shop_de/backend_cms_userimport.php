<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_cms_index.php">Content Management</a>';
	echo ' > Benutzerimport';
	echo '</p>';


	//IMPORT
	$i=0;
	$results=q("SELECT * FROM cms_users;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$kundnr[$row["username"]]=$row["username"];
	}

	$i=0;
	$results=q("SELECT * FROM fa_user_login;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		if (!isset($kundnr[$row["nic"]]))
		{
			q("INSERT INTO cms_users (shop_id, site_id, username, usermail, password, lastlogin, lastvisit, session_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(1, 1, '".$row["nic"]."', '".$row["reg_mail"]."', '".$row["pass"]."', 0, 0, '', 0, 0, 0, 0);", $dbweb, __FILE__, __LINE__);
			$i++;
			echo $i.': '.$row["nic"].' wurde als neuer Benutzer angelegt.<br />';
		}
	}
	echo $i.' neue Benutzer gefunden und importiert.<hr />';
	
	//UPDATE
	$i=0;
	$results=q("SELECT * FROM cms_users;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$password[$row["username"]]=$row["password"];
	}

	$i=0;
	$results=q("SELECT * FROM fa_user_login;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		if ($password[$row["nic"]]!=$row["pass"])
		{
			echo $password[$row["nic"]].' <--> '.$row["pass"].'<br />';
			q("UPDATE cms_users SET password='".$row["pass"]."' WHERE username='".$row["nic"]."';", $dbweb, __FILE__, __LINE__);
			$i++;
			echo $i.': '.$row["nic"].' wurde aktualisiert.<br />';
		}
	}
	echo $i.' Benutzerprofile wurden aktualisiert.';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>