<?php
	include("../functions/cms_t.php");
	include("../functions/mapco_gewerblich.php");
		
	//$quer2y="SELECT id_user, username, usermail, name, lastlogin, userrole, country, l.language FROM cms_users AS u, cms_countries AS c, cms_languages AS l, cms_userroles AS r WHERE c.country_code=u.origin AND l.id_language=u.language_id AND r.id_userrole=u.userrole_id";	
	
	$query="SELECT id_user, username, usermail, name, lastlogin, userrole_id, language_id, origin, user_token, active FROM cms_users";
	
	if( isset($_POST["search"]) and $_POST["search"]!="" )
	{
		if( strpos($query, "WHERE") === false ) $query .= " WHERE "; else $query .= " OR ";
		$query.="username LIKE '%".mysqli_real_escape_string($dbweb,$_POST["search"])."%'";
		if( strpos($query, "WHERE") === false ) $query .= " WHERE "; else $query .= " OR ";
		$query.="name LIKE '%".mysqli_real_escape_string($dbweb,$_POST["search"])."%'";
		if( strpos($query, "WHERE") === false ) $query .= " WHERE "; else $query .= " OR ";
		$query.="usermail LIKE '%".mysqli_real_escape_string($dbweb,$_POST["search"])."%'";
	}
	
	$query .= ' ORDER BY id_user DESC';
	
	/*else
	{
		$query="SELECT u.id_user, u.username, u.usermail, u.name, u.lastlogin, r.userrole, c.country, l.language FROM cms_users AS u, cms_countries AS c, cms_languages AS l, cms_userroles AS r WHERE c.country_code=u.origin AND l.id_language=u.language_id AND r.id_userrole=u.userrole_id";	
	}*/
	if( isset($_POST["limit"]) ) $query.=" LIMIT ".$_POST["limit"];
	$query.=";";
	$results=q($query, $dbweb, __FILE__, __LINE__);
	
	if ( mysqli_num_rows($results)>0 )
	{
		$res=q("SELECT country_code, country FROM cms_countries ORDER BY ordering;", $dbweb, __FILE__, __LINE__);	
		while($row=mysqli_fetch_assoc($res))
		{
			$countries[$row['country_code']] = $row['country'];
		}
		
		$res=q("SELECT id_userrole, userrole FROM cms_userroles ORDER BY id_userrole;", $dbweb, __FILE__, __LINE__);	
		while($row=mysqli_fetch_assoc($res))
		{
			$userroles[$row['id_userrole']] = $row['userrole'];
		}
		
		$res=q("SELECT id_language, language FROM cms_languages ORDER BY ordering;", $dbweb, __FILE__, __LINE__);	
		while($row=mysqli_fetch_assoc($res))
		{
			$languages[$row['id_language']] = $row['language'];
		}

		unset($res);	
	}
	
	while($row=mysqli_fetch_assoc($results))
	{ 
		echo '<User>';
	
		$is_gewerbe = gewerblich($row['id_user']);
		if ( $is_gewerbe == true )
		{
			print '<is_gewerbe>1</is_gewerbe>';	
		}
		else
		{
			print '<is_gewerbe>0</is_gewerbe>';	
		}
			
		foreach($row as $key => $value)
		{ 
			echo '	<'.$key.'>';
			if( !is_numeric($value) ){ $tmp = '<![CDATA['.$value.']]>';	}
			else { $tmp = $value; }
			echo $tmp.'</'.$key.'>'."\n";
		}
		
		if (isset($userroles)) { print '<userrole>'.$userroles[$row['userrole_id']].'</userrole>'; }
		if (isset($languages)) { print '<language>'.$languages[$row['language_id']].'</language>'; }
		if (isset($countries)) { print '<country>'.$countries[$row['origin']].'</country>'; }
		
		echo '</User>';
	}

?>