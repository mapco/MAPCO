<?php

	check_man_params(array("userid" => "numericNN"));

	//GET USER SITES
	$user_sites = array();
	$res_user_sites = q("SELECT * FROM cms_users_sites WHERE user_id = ".$_POST["userid"], $dbweb, __FILE__, __LINE__);
	while ($row_user_sites = mysqli_fetch_assoc($res_user_sites))
	{
		$user_sites[]=$row_user_sites["site_id"];
	}

	//GET SHOPS FOR SITEs
	$shops = array();
	if (sizeof($user_sites)>0)
	{
		$res_shops=q("SELECT * FROM shop_shops WHERE site_id IN (".implode(",", $user_sites).") AND active = 1", $dbshop, __FILE__, __LINE__);
		while ($row_shops=mysqli_fetch_assoc($res_shops))
		{ 
			$shops[$row_shops["id_shop"]] = $row_shops["title"];
			//GET CHILDSHOPS
			$res_shops2=q("SELECT * FROM shop_shops WHERE parent_shop_id = ".$row_shops["id_shop"]." AND active = 1", $dbshop, __FILE__, __LINE__);
			while ($row_shops2 = mysqli_fetch_assoc($res_shops2))
			{
					$shops[$row_shops2["id_shop"]] = $row_shops2["title"];
			}
		}
	}


//SERVICE RESPONSE

	foreach( $shops as $id => $title)
	{
		echo '<usershop>'."\n";
		echo '	<usershop_id>'.$id.'</usershop_id>'."\n";
		echo '	<usershop_title>'.$title.'</usershop_title>'."\n";
		echo '</usershop>'."\n";
	}

?>