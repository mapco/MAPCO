<?php

	check_man_params(array( "user_id" => "numeric" ));
	
	$user_id=$_POST["user_id"];

	$dbweb;
	$dbshop;
	$ip;
	
	$country="";
	$origin="";
	
	//read country from bill adress
	$results = q("SELECT country FROM shop_bill_adr WHERE user_id='".$user_id."' LIMIT 1;", $dbshop, __FILE__, __LINE__); 
	if(mysqli_num_rows($results) > 0)
	{ 
		$row = mysqli_fetch_array($results); 
		$country=$row["country"];
	}
	
	if ($country=="")
	{
		//read username
		$results = q("SELECT username FROM cms_users WHERE id_user=".$user_id." LIMIT 1;", $dbweb, __FILE__, __LINE__);
		$row = mysqli_fetch_array($results);
		$username=$row["username"];
	
		//read country from IDIMS
		$results = q("SELECT a.LAND FROM kunde AS a, fa_user_login AS b WHERE b.nic='".$username."' AND a.ADR_ID=b.adrid LIMIT 1;", $dbshop, __FILE__, __LINE__); 
		if(mysqli_num_rows($results) == 0)
		{ 
			$origin=ip2country($ip);
		}
		else
		{
			$row = mysqli_fetch_array($results);
			$country=$row["LAND"];
		}
	}
	
	if ($country!="")
	{ 
		$results = q("SELECT country_code FROM shop_countries WHERE country='".$country."' LIMIT 1;", $dbshop, __FILE__, __LINE__); 
		if(mysqli_num_rows($results) == 0)
		{ 
			$origin=ip2country($ip);
		}
		else
		{ 
			$row = mysqli_fetch_array($results); 
			$origin=$row["country_code"];
		}
	}
	
	//write origin
	q("UPDATE cms_users SET origin='".$origin."' WHERE id_user=".$user_id.";", $dbweb, __FILE__, __LINE__); 
//	return($origin);
	$xml='<origin><![CDATA['.$origin.']]></origin>';
	echo $xml;

?>