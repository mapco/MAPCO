<?php
	include("../config.php");
/*
	$anz=0;
	q("DELETE FROM kunde WHERE IDIMS_ID in (16815, 17285, 18465, 18466, 18211, 17292, 9753, 18467, 18469, 18209, 18471, 14110, 18463, 18472, 18213, 18473);", $dbshop, __FILE__, __LINE__); 
	q("DELETE FROM fa_user_login WHERE kunid in (16815, 17285, 18465, 18466, 18211, 17292, 9753, 18467, 18469, 18209, 18471, 14110, 18463, 18472, 18213, 18473);", $dbshop, __FILE__, __LINE__); 
	$results=q("SELECT * FROM fa_user_login WHERE kunid in (16815, 17285, 18465, 18466, 18211, 17292, 9753, 18467, 18469, 18209, 18471, 14110, 18463, 18472, 18213, 18473);", $dbshop, __FILE__, __LINE__);
	$anz=$anz+mysqli_num_rows($results);
	$results=q("SELECT * FROM kunde WHERE IDIMS_ID in (16815, 17285, 18465, 18466, 18211, 17292, 9753, 18467, 18469, 18209, 18471, 14110, 18463, 18472, 18213, 18473);", $dbshop, __FILE__, __LINE__);
	$anz=$anz+mysqli_num_rows($results);
	
	if($anz==0)	echo 'Alle Ebay Zugaenge geloescht<br /><hr />';
	else echo $anz.' Ebay Zugaenge vorhanden<br /><hr />';

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
			q("INSERT INTO cms_users (shop_id, site_id, username, usermail, password, lastlogin, lastvisit, session_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(1, 1, '".$row["nic"]."', '".$row["reg_mail"]."', '".$row["pass"]."', 0, 0, '', ".time().", 10, ".time().", 10);", $dbweb, __FILE__, __LINE__);
			$user_id=mysqli_insert_id($dbweb);
			q("INSERT INTO cms_users_sites (user_id, site_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$user_id.", 1, ".time().", 10, ".time().", 10);", $dbweb, __FILE__, __LINE__);
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
*/	
?>