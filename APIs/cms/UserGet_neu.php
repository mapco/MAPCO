<?php
	include("../functions/cms_t.php");
	
	check_man_params(array("id_user" => "numericNN"));
	
	$query="SELECT id_user, username, usermail, name, idims_user_id, lastlogin, userrole_id, origin, user_token, language_id AS user_language FROM cms_users WHERE id_user=".$_POST['id_user'];
	if( isset($_POST["search"]) and $_POST["search"]!="" )
	{
		if( strpos($query, "WHERE") === false ) $query .= " WHERE "; else $query .= " OR ";
		$query.="username LIKE '%".mysqli_real_escape_string($dbweb,$_POST["search"])."%'";
		if( strpos($query, "WHERE") === false ) $query .= " WHERE "; else $query .= " OR ";
		$query.="name LIKE '%".mysqli_real_escape_string($dbweb,$_POST["search"])."%'";
		if( strpos($query, "WHERE") === false ) $query .= " WHERE "; else $query .= " OR ";
		$query.="usermail LIKE '%".mysqli_real_escape_string($dbweb,$_POST["search"])."%'";
	}
	if( isset($_POST["limit"]) ) $query.=" LIMIT ".$_POST["limit"];
	$query.=";";
	$results=q($query, $dbweb, __FILE__, __LINE__);
	
	$row=mysqli_fetch_assoc($results);
	echo '<User>'."\n";
	foreach($row as $key => $value)
	{ 
		echo '	<'.$key.'>';
		if( !is_numeric($value) ){ $tmp = '<![CDATA['.$value.']]>';	}
		else { $tmp = $value; }
		echo $tmp.'</'.$key.'>'."\n";
	}
	echo '</User>'."\n";
	
	//sites of user
	echo '	<Sites>'."\n";
	$results=q("SELECT * FROM cms_users_sites WHERE user_id=".$_POST["id_user"].";", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		echo '		<Site>'.$row["site_id"].'</Site>'."\n";
	}
	echo '	</Sites>'."\n";

	//languages
	$results=q("SELECT id_language, language FROM cms_languages;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		echo '	<language>'."\n";
		echo '		<language_id>'.$row["id_language"].'</language_id>'."\n";
		echo '		<language_title>'.$row["language"].'</language_title>'."\n";
		echo '	</language>'."\n";
	}

	//countries
	$results=q("SELECT id_country, country, country_code FROM cms_countries;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		echo '	<country>'."\n";
		echo '		<country_id>'.$row["id_country"].'</country_id>'."\n";
		echo '		<country_title>'.$row["country"].'</country_title>'."\n";
		echo '		<country_code>'.$row["country_code"].'</country_code>'."\n";
		echo '	</country>'."\n";
	}
	
	//userroles
	$results=q("SELECT id_userrole, userrole FROM cms_userroles;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		echo '	<userrole>'."\n";
		echo '		<user_role_id>'.$row["id_userrole"].'</user_role_id>'."\n";
		echo '		<user_role_title>'.$row["userrole"].'</user_role_title>'."\n";
		echo '	</userrole>'."\n";
	}

?>