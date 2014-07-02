<?php

	$required=array("site_id" => "numericNN", "lastname" => "textNN");
	
	check_man_params($required);

	include("../functions/cms_createPassword.php");

	//GET USER_IDs from cms_users_sites WHERE SITE_ID = $site_id
	$site_userids = array();
	$res_site_userids = q("SELECT * FROM cms_users_sites WHERE site_id = ".$_POST["site_id"].";", $dbweb, __FILE__, __LINE__);
	while ($row_site_userids = mysqli_fetch_array($res_site_userids))
	{
		$site_userids[$row_site_userids["user_id"]] = $row_site_userids["site_id"];
	}
	
	//GET existing CMS users
	$CMS=array();
	$usertokens=array();
	$res_CMS=q("SELECT * FROM cms_users;" , $dbweb, __FILE__, __LINE__);
	while ($row_CMS=mysqli_fetch_array($res_CMS))
	{
		if (isset($site_userids[$row_CMS["id_user"]]))
		{
			$CMS[strtolower($row_CMS["username"])]=$row_CMS["id_user"];
		}
		$usertokens[$row_CMS["usertoken"]] = 1;
	}

	
	
	//FIND UNIQUE USERNAME

	$cms_username="";
	
	if ($_POST["usermail"]!="" && $_POST["usermail"]!="Invalid Request") 
	{
		if (!isset($CMS[strtolower($_POST["usermail"])])) $cms_username=$_POST["usermail"];
	}
	
	if ($cms_username=="")
	{
		if (!isset( $CMS[strtolower(str_replace(" ", "", $_POST["lastname"]."_".$_POST["firstname"]))] ))
		{
			$cms_username=strtolower(str_replace(" ", "", $_POST["lastname"]."_".$_POST["firstname"]));
		}
		else
		{
			$counter=1;
			$tmp=strtolower(str_replace(" ", "", $_POST["lastname"]."_".$_POST["firstname"]))."_".(string)$counter;
			while (isset($CMS[$tmp]))
			{
				$counter++;
				$tmp=strtolower(str_replace(" ", "", $_POST["lastname"]."_".$_POST["firstname"]))."_".(string)$counter;
			}
			$cms_username=$tmp;
		}
	}
	
	//CREATE PASSWORD
	$salt=createPassword(32);
	$pw=createPassword(8);
	$pw=md5($pw);
	$pw=md5($pw.$salt);
	$pw=md5($pw.PEPPER);
	
	//CREATE USERTOKEN
	$usertoken = createPassword(50);
	while (isset($usertokens[$usertoken])) { $usertoken = createPassword(50);}


	// CMS USER ANLEGEN
	$res_ins=q("INSERT INTO cms_users (
		username, 
		usermail, 
		firstname,
		lastname,  
		password, 
		user_token, 
		user_salt,
		userrole_id, 
		language_id, 
		active, 
		firstmod, 
		firstmod_user, 
		lastmod, 
		lastmod_user
	) VALUES (
		'".mysqli_real_escape_string($dbweb,$cms_username)."', 
		'".mysqli_real_escape_string($dbweb,$_POST["usermail"])."', 
		'".mysqli_real_escape_string($dbweb,trim($_POST["firstname"]))."',
		'".mysqli_real_escape_string($dbweb,trim($_POST["lastname"]))."',
		'".mysqli_real_escape_string($dbweb,$pw)."', 
		'".mysqli_real_escape_string($dbweb,$usertoken)."',
		'".mysqli_real_escape_string($dbweb,$salt)."',
		5,
		1,
		1, 
		".time().", 
		1, 
		".time().", 
		1
	);", $dbweb, __FILE__, __LINE__);

	$cms_user_id=mysqli_insert_id($dbweb);
	
	//VERKNÜPFUNG mit cms_users_sites
	$res_ins=q("INSERT INTO cms_users_sites (user_id, site_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$cms_user_id.", ".$_POST["site_id"].", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);

	
	
	echo '<customer_id>'.$cms_user_id.'</customer_id>';

	
?>