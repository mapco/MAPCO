<?php
	include("../config.php");

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
	
	
	
	/*
	
	//clear tables
	q("TRUNCATE TABLE cms_users;", $dbweb, __FILE__, __LINE__);
	$results=q("SELECT * FROM fa_user_login;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		q("INSERT INTO cms_users (username, usermail, password, lastlogin, lastvisit, session_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".$row["nic"]."', '".$row["reg_mail"]."', '".$row["pass"]."', 0, 0, '', 0, 0, 0, 0);", $dbweb, __FILE__, __LINE__);
	}
	*/
?>