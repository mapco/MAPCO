<?php

	$xmldata = '';

	if ($_POST["mode"] == "customer_search")
	{
		check_man_params(array("mode" => "textNN", "ordersite_id" => "numericNN", "qry_string" => "textNN"));

		$useraddresses = array();
		$user_ids = array();

		$qry_string=explode(' ', strtolower($_POST["qry_string"]));

		//CUT SHORT STRINGs
		for ($i=0; $i<sizeof($qry_string); $i++)
		{
			if (strlen($qry_string[$i])<3) unset($qry_string[$i]);
		}
	
		$sizeof_qry_string=sizeof($qry_string);
	
		//GET SHOPS OF SITE ID
		$shops = array();
		$res_shops = q("SELECT * FROM shop_shops WHERE site_id = ".$_POST["ordersite_id"]." AND active = 1;", $dbshop, __FILE__, __LINE__);
		while ($row_shops = mysqli_fetch_assoc($res_shops))
		{
			$shops[]=$row_shops["id_shop"];
		}
		$res_shops = q("SELECT * FROM shop_shops WHERE parent_shop_id = ".$shops[0]." AND active = 1;", $dbshop, __FILE__, __LINE__);
		while ($row_shops = mysqli_fetch_assoc($res_shops))
		{
			$shops[]=$row_shops["id_shop"];
		}
		
		
//************************************************		
		if ($_POST["search_for"] == "Kundennummer")
		{
			$adr_ids = array();
			$idims_kunde = array();
			$qry_string = trim($_POST["qry_string"]);
			$res_kunde = q("SELECT * FROM kunde WHERE KUND_NR LIKE '%".$qry_string."%'", $dbshop, __FILE__, __LINE__);
			while ($row_kunde = mysqli_fetch_assoc( $res_kunde ))
			{
				$adr_ids[] = $row_kunde["ADR_ID"];	
				$idims_kunde[$row_kunde["user_id"]] = $row_kunde;
			}
			
			$cms_users = array();
			$u_ids = array();
			// SUCHE USERID AUS adrid
			if (sizeof($adr_ids)>0)
			{
				$res_cmsusers = q("SELECT * FROM cms_users WHERE idims_adr_id IN (".implode(",", $adr_ids).")", $dbweb, __FILE__, __LINE__);	
				while ($row_cmsusers = mysqli_fetch_assoc($res_cmsusers))
				{
					$user_ids[$row_cmsusers["id_user"]] = 0;
					$u_ids[] = $row_cmsusers["id_user"];
					$cms_users[$row_cmsusers["id_user"]] = $row_cmsusers;
				}
			}
			if ( sizeof($u_ids)>0 )
			{
				$res_adr = q("SELECT * FROM shop_bill_adr WHERE user_id IN (".implode(",",$u_ids).") AND shop_id IN (".implode(",", $shops).")", $dbshop, __FILE__, __LINE__);
				while($row_adr = mysqli_fetch_assoc( $res_adr ))
				{
					$useraddresses[$row_adr["user_id"]][sizeof($useraddresses[$row_adr["user_id"]])] = $row_adr;
					//$user_ids[sizeof($user_ids)] = $row_adr["user_id"];
				}
			}

		}
//***************************************************
		if ($_POST["search_for"] == "BM_UserID")
		{
			$qry_string = trim($_POST["qry_string"]);
			$res_adr = q("SELECT * FROM shop_bill_adr WHERE user_id = ".$qry_string." AND shop_id IN (".implode(",", $shops).")", $dbshop, __FILE__, __LINE__);
			while($row_adr = mysqli_fetch_assoc( $res_adr ))
			{
				$useraddresses[$row_adr["user_id"]][sizeof($useraddresses[$row_adr["user_id"]])] = $row_adr;
				$user_ids[$row_adr["user_id"]] = 0;
			}
			//GET ALL USERS FROM SITE
			$siteusers = array();
			$res_siteusers = q("SELECT * FROM cms_users_sites WHERE user_id = ".$qry_string." AND site_id = ".$_POST["ordersite_id"], $dbweb, __FILE__, __LINE__);
			while ( $row_siteusers = mysqli_fetch_assoc( $res_siteusers ) )
			{
				$user_ids[$row_siteusers["user_id"]] = 0;
			}
		}
//***************************************************		
		if ($_POST["search_for"] == "Adresse")
		{
			//SHOP DATA
			$addresses = array();
			$cms_userdata = array();
			$res_adr = q("SELECT * FROM shop_bill_adr WHERE shop_id IN (".implode(",", $shops).")", $dbshop, __FILE__, __LINE__);
			while($row_adr = mysqli_fetch_assoc( $res_adr ))
			{
				//CREATE HAYSTACK	
				$haystack = strtolower($row_adr["company"])." ".strtolower($row_adr["firstname"])." ".strtolower($row_adr["lastname"])." ".strtolower($row_adr["street"])." ".strtolower($row_adr["number"])." ".strtolower($row_adr["additional"])." ".strtolower($row_adr["zip"])." ".strtolower($row_adr["city"])." ".strtolower($row_adr["country"])." ".strtolower($row_adr["usermail"])." ".strtolower($row_adr["userphone"])." ".strtolower($row_adr["usermobile"])." ".strtolower($row_adr["userfax"]);
				$match=0;
				for ($i=0; $i<$sizeof_qry_string; $i++)
				{
					if (strpos($haystack, $qry_string[$i]) !== false) 
					{
						$match++;
					}
				}
				if ( $sizeof_qry_string == $match )
				{
					//$addresses[$row_adr["adr_id"]] = $row_adr;
					$useraddresses[$row_adr["user_id"]][sizeof($useraddresses[$row_adr["user_id"]])] = $row_adr;
					
					$user_ids[$row_adr["user_id"]] = 0;
				}
			}
			//GET ALL USERS FROM SITE
			$siteusers = array();
			$res_siteusers = q("SELECT * FROM cms_users_sites WHERE site_id = ".$_POST["ordersite_id"], $dbweb, __FILE__, __LINE__);
			while ( $row_siteusers = mysqli_fetch_assoc( $res_siteusers ) )
			{
				$siteusers[$row_siteusers["user_id"]] = 0;
			}
			$users = array();
			$res_users = q("SELECT * FROM cms_users", $dbweb, __FILE__, __LINE__);
			while ( $row_users = mysqli_fetch_assoc( $res_users ) )
			{
				if ( isset($siteusers[$row_users["id_user"]]) )
				{
					//CREATE HAYSTACK
					if ( $row_users["name"] != "")
					{
						$haystack = strtolower($row_users["name"]);
					}
					else
					{
						$haystack = strtolower(trim($row_users["firstname"]." ".$row_users["lastname"]));
					}
					$match = 0;
					for ($i=0; $i<$sizeof_qry_string; $i++)
					{
						if (strpos($haystack, $qry_string[$i]) !== false) 
						{
							$match++;
						}
					}
					if ( $sizeof_qry_string == $match )
					{
						//$addresses[$row_adr["adr_id"]] = $row_adr;
						$user_ids[$row_users["id_user"]] = 0;
					}
				}
			}
		}

		if ($_POST["search_for"] == "Mail")
		{
			//GET ALL USERS FROM SITE
			$siteusers = array();
			$res_siteusers = q("SELECT * FROM cms_users_sites WHERE site_id = ".$_POST["ordersite_id"], $dbweb, __FILE__, __LINE__);
			while ( $row_siteusers = mysqli_fetch_assoc( $res_siteusers ) )
			{
				$siteusers[$row_siteusers["user_id"]] = 0;
			}
			$users = array();
			$res_users = q("SELECT * FROM cms_users", $dbweb, __FILE__, __LINE__);
			while ( $row_users = mysqli_fetch_assoc( $res_users ) )
			{
				if ( isset($siteusers[$row_users["id_user"]]) )
				{
					//CREATE HAYSTACK
					$match = 0;
					$haystack = strtolower($row_users["usermail"]);
					for ($i=0; $i<$sizeof_qry_string; $i++)
					{
						if (strpos($haystack, $qry_string[$i]) !== false) 
						{
							$match++;
						}
					}
					if ( $sizeof_qry_string == $match )
					{
						//$addresses[$row_adr["adr_id"]] = $row_adr;
						$user_ids[$row_users["id_user"]] = 0;
					}
				}
			}
			$res_orders = q("SELECT customer_id, usermail FROM shop_orders WHERE shop_id IN (".implode(",",$shops).")", $dbshop, __FILE__, __LINE__);
			while ( $row_orders = mysqli_fetch_assoc( $res_orders ) )
			{
				//CREATE HAYSTACK
				$haystack = strtolower($row_orders["usermail"]);
				$match = 0;
				for ($i=0; $i<$sizeof_qry_string; $i++)
				{
					if (strpos($haystack, $qry_string[$i]) !== false) 
					{
						$match++;
					}
				}
				if ( $sizeof_qry_string == $match )
				{
					$user_ids[$row_orders["customer_id"]] = 0;
				}
			}
			
		}
		

//CREATE OUTPUT
print_r($user_ids);
		// GET CMS USERDATA
		if ( sizeof($user_ids) > 0 )
		{
			$_user_ids = array();
			foreach($user_ids as $u_id => $z)
			{
				$_user_ids[sizeof($_user_ids)] = $u_id;
			}
		//print_r($_user_ids);	
			$res_cms_user = q("SELECT * FROM cms_users WHERE id_user IN (".implode(",", $_user_ids).")", $dbweb, __FILE__, __LINE__);
			while ( $row_cms_user = mysqli_fetch_assoc( $res_cms_user ) )
			{
				//USERNAME
				$cms_userdata[$row_cms_user["id_user"]]["username"] = $row_cms_user["username"];
				//NAME
				if ( $row_cms_user["name"] != "" )
				{
					$cms_userdata[$row_cms_user["id_user"]]["name"] = $row_cms_user["name"];
				}
				else
				{
					$cms_userdata[$row_cms_user["id_user"]]["name"] = $row_cms_user["firstname"];
					if ($cms_userdata[$row_cms_user["id_user"]]["name"] == "")
					{
						$cms_userdata[$row_cms_user["id_user"]]["name"] = $row_cms_user["lastname"];
					}
					else
					{
						$cms_userdata[$row_cms_user["id_user"]]["name"] .= " ".$row_cms_user["lastname"];
					}
				}
				//usermail
				if ( $row_cms_user["usermail"] != "" )
				{
					$cms_userdata[$row_cms_user["id_user"]]["usermail"] = $row_cms_user["usermail"];
				}
	
			}
		}
		
		$xmldata .= '<shop_data>'."\n";
		foreach ($_user_ids as $user_id)
		{
			$xmldata .= '	<userdata>'."\n";	
			$xmldata .= '		<id_user>'.$user_id.'</id_user>'."\n";
			if ( isset($cms_userdata[$user_id]["username"]))
			{
				$xmldata .= '		<user_username>'.$cms_userdata[$user_id]["username"].'</user_username>'."\n";
			}
			if ( isset($cms_userdata[$user_id]["username"]))
			{
				$xmldata .= '		<user_name>'.$cms_userdata[$user_id]["name"].'</user_name>'."\n";
			}
			if ( isset($cms_userdata[$user_id]["usermail"]))
			{
				$xmldata .= '		<user_mail>'.$cms_userdata[$user_id]["usermail"].'</user_mail>'."\n";
			}
			
			if (isset($useraddresses[$user_id]))
			{
			//foreach ($data as $index => $address)
			foreach ($useraddresses[$user_id] as $index => $address)
			{
				$xmldata .=	'		<user_address>'."\n";
				
				$keys = array_keys($address);
				foreach ( $keys as $key )
				{
					$xmldata .= '			<'.$key.'><![CDATA['.$address[$key].']]></'.$key.'>'."\n";	
				}
				
				$xmldata .=	'		</user_address>'."\n";
			}
			}
			
			$xmldata .= '	</userdata>'."\n";
		}
		$xmldata .= '</shop_data>'."\n";
		

		echo $xmldata;
	}
	
	if ( $_POST["mode"] == "customer_show")
	{
		$xmldata .= '<customer_data>'."\n";
		
		//GET ADDRESS
		$res_adr = q("SELECT * FROM shop_bill_adr WHERE adr_id = ".$_POST["adr_id"], $dbshop, __FILE__, __LINE__);	
		while ( $row_adr = mysqli_fetch_assoc( $res_adr ) )
		{
			$keys = array_keys($row_adr);
			foreach ( $keys as $key )
			{
				$xmldata .= '	<'.$key.'><![CDATA['.$row_adr[$key].']]></'.$key.'>'."\n";	
			}
		}
		//GET MAIL
		$res_mail = q("SELECT * FROM cms_users WHERE id_user = ".$_POST["user_id"], $dbweb, __FILE__, __LINE__);
		while ( $row_mail = mysqli_fetch_assoc( $res_mail ) )
		{
			$xmldata .= '<usermail><![CDATA['.$row_mail["usermail"].']]></usermail>'."\n";	
		}
		$xmldata .= '</customer_data>'."\n";
		echo $xmldata;
	}
	
	//print_r($useraddresses);
	/*
	exit;
		//GET USERS FROM cms_users_sites
		$sites_usres=array();
		$res_sites = q("SELECT * FROM cms_users_sites WHERE site_id = ".$_POST["site_id"].";", $dbweb, __FILE__, __LINE__);
		while ($row_sites = mysqli_fetch_assoc($res_sites))
		{
			$sites_users[$row_sites["user_id"]] = 1;
		}
		//GET DATA FROM shop_bill_adr
		$adr=array();
		$res_adr = q("SELECT * FROM shop_bill_adr;", $dbshop, __FILE__, __LINE__);
		while ($row_adr = mysqli_fetch_assoc($res_adr))
		{
			$adr[$row_adr["adr_id"]] = strtolower($row_adr["company"])." ".strtolower($row_adr["firstname"])." ".strtolower($row_adr["lastname"])." ".strtolower($row_adr["street"])." ".strtolower($row_adr["number"])." ".strtolower($row_adr["additional"])." ".strtolower($row_adr["zip"])." ".strtolower($row_adr["city"])." ".strtolower($row_adr["country"])." ".strtolower($row_adr["usermail"])." ".strtolower($row_adr["userphone"])." ".strtolower($row_adr["usermobile"])." ".strtolower($row_adr["userfax"]);
		}
	
		//GET DATA FROM shop_orders
		$orders = array();
		
		$haystack = array();
		
		
		$res_orders = q("SELECT * FROM shop_orders WHERE shop_id IN (".implode(", ", $shops).");", $dbshop, __FILE__, __LINE__);

		while ($row_orders = mysqli_fetch_assoc($res_orders))
		{
			
			$haystack_address=$adr[$row_orders["bill_adr_id"]]." ".	$adr[$row_orders["ship_adr_id"]];	
			
			$haystack_address.=" ".strtolower($row_orders["usermail"])." ".strtolower($row_orders["userphone"])." ".strtolower($row_orders["usermobile"])." ".strtolower($row_orders["userfax"]);

			$match=0;
			for ($i=0; $i<$sizeof_qry_string; $i++)
			{
				if (strpos(" ".$haystack_address, $qry_string[$i])>0) {$match++;}
			}
			
			if ($match>0)
			{
			//	echo "HALLO";
	//			echo $haystack_address."<br />";
				
				if (isset($haystack[$row_orders["customer_id"]]))
				{
					$haystack[$row_adr["customer_id"]][sizeof($haystack[$row_orders["customer_id"]])]=$haystack_address;
				}
				else
				{
					$haystack[$row_orders["customer_id"]][0]=$haystack_address;
				}
			}
		}
	
		//search cms_users
		$res_account=q("SELECT * FROM cms_users;", $dbweb, __FILE__, __LINE__);
		while ($row_account=mysqli_fetch_array($res_account))
		{
			if (isset($sites_users[$row_account["id_user"]]))
			{
			
				$haystack_user=$row_account["username"]." ".$row_account["usermail"]." ".$row_account["name"];
				$match=0;
				for ($i=0; $i<$sizeof_qry_string; $i++)
				{
					if (strpos(" ".$haystack_user, $qry_string[$i])!==false) $match++;
				}
				
				if ($match>0)
				{
					if (isset($haystack[$row_account["id_user"]]))
					{
						for ($i=0; $i<sizeof($haystack[$row_account["id_user"]]); $i++)
						{
							$haystack[$row_account["id_user"]][$i].=$haystack_user;
						}
					}
					else
					{
						$haystack[$row_account["id_user"]][0]=$haystack_user;
					}
				}
			}
		}
	
		//search for users from crm_customer_accounts
		$res_account=q("SELECT * FROM crm_customer_accounts3;", $dbweb, __FILE__, __LINE__);
		while ($row_account=mysqli_fetch_array($res_account))
		{
			if (isset($sites_users[$row_account["cms_customer_id"]]))
			{

				$haystack_user=$row_account["shop_user_id"];
				$match=0;
				for ($i=0; $i<$sizeof_qry_string; $i++)
				{
					if (strpos(" ".$haystack_user, $qry_string[$i])!==false) $match++;
				}
				
				if ($match>0)
				{
					if (isset($haystack[$row_account["cms_user_id"]]))
					{
						for ($i=0; $i<sizeof($haystack[$row_account["cms_user_id"]]); $i++)
						{
							$haystack[$row_account["cms_user_id"]][$i].=$haystack_user;
						}
					}
					else
					{
						$haystack[$row_account["cms_user_id"]][0]=$haystack_user;
					}
				}
			}
		}
	
	
	$customer=array();
	
		foreach ($haystack as $userid => $haystackstring)
		{
			for ($j=0; $j<sizeof($haystackstring); $j++)
			{
				$match=0;
				for ($i=0; $i<$sizeof_qry_string; $i++)
				{
					if (strpos(" ".$haystackstring[$j], $qry_string[$i])!==false) {$match++; }
				}
				if ($match==$sizeof_qry_string && $userid!="" && $userid!=0)
				{
					$customer[$userid]=$userid;
				}
	
			}
			
		}
	
	

$xmldata="";
if (isset($customer) && sizeof($customer)>0)
{		
	foreach($customer as $userid)
	{
		$xmldata.='	<customer>'."\n";
		$xmldata.='		<user_id>'.$userid.'</user_id>'."\n";
		
		$name="";
		$username="";
		$res_cms_user=q("SELECT * FROM cms_users WHERE id_user = ".$userid.";", $dbweb, __FILE__, __LINE__);
		while ($row_cms_user=mysqli_fetch_array($res_cms_user))
		{
			$name=$row_cms_user["name"];
			$username=$row_cms_user["username"];
		}
		$xmldata.='		<name><![CDATA['.$name.']]></name>'."\n";
		$xmldata.='		<username><![CDATA['.$username.']]></username>'."\n";
	
		$xmldata.='		<addresses>'."\n";
		//GET ADDRESS
		$checkstrings=array();
		//$res_adr=q("SELECT * FROM shop_orders WHERE customer_id = ".$userid.";", $dbshop, __FILE__, __LINE__);
		$res_adr=q("SELECT * FROM shop_orders WHERE customer_id = ".$userid." GROUP BY bill_adr_id;", $dbshop, __FILE__, __LINE__);
		while ($row_adr=mysqli_fetch_array($res_adr))
		{
			$match=false;
			
			//CHECK IF ADDRESS IS ALREADY SHOWN
			$tmp=strtolower($row_adr["bill_company"])." ".strtolower($row_adr["bill_firstname"])." ".strtolower($row_adr["bill_lastname"])." ".strtolower($row_adr["bill_street"])." ".strtolower($row_adr["bill_number"])." ".strtolower($row_adr["bill_additional"])." ".strtolower($row_adr["bill_zip"])." ".strtolower($row_adr["bill_city"])." ".strtolower($row_adr["bill_country"])." ".strtolower($row_adr["bill_country_code"])." ".strtolower($row_adr["usermail"])." ".strtolower($row_adr["userphone"])." ".strtolower($row_adr["usermobile"])." ".strtolower($row_adr["userfax"]);
			for ($k=0; $k<sizeof($checkstrings);$k++)
			{
				if ($checkstrings[$k]==$tmp)
				{
					$match=true;
				}
			}
			
			if (!$match)
			{
				$checkstrings[sizeof($checkstrings)]=$tmp;
							
				$xmldata.='			<address>'."\n";
				$xmldata.='				<id_order>'.$row_adr["id_order"].'</id_order>'."\n";
				$xmldata.='				<bill_adr_id>'.$row_adr["bill_adr_id"].'</bill_adr_id>'."\n";
				$xmldata.='				<bill_company><![CDATA['.$row_adr["bill_company"].']]></bill_company>'."\n";
				$xmldata.='				<bill_firstname><![CDATA['.$row_adr["bill_firstname"].']]></bill_firstname>'."\n";
				$xmldata.='				<bill_lastname><![CDATA['.$row_adr["bill_lastname"].']]></bill_lastname>'."\n";	
				$xmldata.='				<bill_street><![CDATA['.$row_adr["bill_street"].']]></bill_street>'."\n";
				$xmldata.='				<bill_number><![CDATA['.$row_adr["bill_number"].']]></bill_number>'."\n";
				$xmldata.='				<bill_additional><![CDATA['.$row_adr["bill_additional"].']]></bill_additional>'."\n";
				$xmldata.='				<bill_zip><![CDATA['.$row_adr["bill_zip"].']]></bill_zip>'."\n";
				$xmldata.='				<bill_city><![CDATA['.$row_adr["bill_city"].']]></bill_city>'."\n";
				$xmldata.='				<bill_country><![CDATA['.$row_adr["bill_country"].']]></bill_country>'."\n";
				$xmldata.='				<bill_country_code>'.$row_adr["bill_country_code"].'</bill_country_code>'."\n";
				$xmldata.='				<usermail>'.$row_adr["usermail"].'</usermail>'."\n";
				$xmldata.='				<userphone>'.$row_adr["userphone"].'</userphone>'."\n";
				$xmldata.='				<usermobile>'.$row_adr["usermobile"].'</usermobile>'."\n";
				$xmldata.='				<userfax>'.$row_adr["country_id"].'</userfax>'."\n";
				$xmldata.='			</address>'."\n";
			}
		}
		
		$xmldata.='		</addresses>'."\n";
		$xmldata.='	</customer>'."\n";
	
	//	echo $userid."<br />";
	}
}
//SERVICE RESPONSE
echo $xmldata;
*/
?>