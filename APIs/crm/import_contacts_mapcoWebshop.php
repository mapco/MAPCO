<?php

	$startzeit=time();

//KUNDENSTAMMDATEN ZIEHEN
	//cms_users
	$cms_users=array();
	$res=q("SELECT * FROM cms_users;", $dbweb, __LINE__, __FILE__);
	while ($row=mysqli_fetch_array($res))
	{
		$cms_users[$row["username"]]=$row["id_user"];
		$customer_master_data_mail[$row["id_user"]]=$row["usermail"];
	}

	//customer_master_data
	$res=q("SELECT * FROM kunde;", $dbshop, __LINE__, __FILE__);
	while ($row=mysqli_fetch_array($res))
	{
		if (isset($cms_users[$row["KUND_NR"]]))
		{
			$account_user_id[$cms_users[$row["KUND_NR"]]]=$cms_users[$row["KUND_NR"]];
		
			$customer_master_data_anschrift1[$cms_users[$row["KUND_NR"]]]=$row["ANSCHR_1"];
			$customer_master_data_anschrift2[$cms_users[$row["KUND_NR"]]]=$row["ANSCHR_2"];
			$customer_master_data_anschrift3[$cms_users[$row["KUND_NR"]]]=$row["ANSCHR_3"];
			$customer_master_data_postfach[$cms_users[$row["KUND_NR"]]]=$row["POSTFACH"];
			$customer_master_data_strasse[$cms_users[$row["KUND_NR"]]]=$row["STRASSE"];
			$customer_master_data_zip[$cms_users[$row["KUND_NR"]]]=$row["PLZ"];
			$customer_master_data_city[$cms_users[$row["KUND_NR"]]]=$row["ORT"];
			$customer_master_data_country[$cms_users[$row["KUND_NR"]]]=$row["LAND"];
			$customer_master_data_PG[$cms_users[$row["KUND_NR"]]]=$row["PREISGR"];
			$customer_master_data_Frachtpauschale[$cms_users[$row["KUND_NR"]]]=$row["FRACHTFREI"];
			$customer_master_data_gewerblich[$cms_users[$row["KUND_NR"]]]=$row["GEWERBE"];
			$customer_master_data_KUND_NR[$cms_users[$row["KUND_NR"]]]=$row["GEWERBE"];
			
		}
	}
	
//KUNDENDATEN AUS MAPCO-SHOP Orders ziehen
	$res=q("SELECT * FROM shop_orders order by lastmod desc;", $dbshop, __LINE__, __FILE__);
//	$res=q("SELECT * FROM shop_orders where customer_id = 5011 limit 100;", $dbshop, __LINE__, __FILE__);
	while ($row=mysqli_fetch_array($res))
	{
		$account_user_id[$row["customer_id"]]=$row["customer_id"];
	//BILL ADDRESS
		if (!$row["bill_lastname"]=="" && !$row["bill_street"]=="" && !$row["bill_city"]=="") //CHECK AUF GÜLTIGE ADRESSE
		{
			if (!isset($bill_company[$row["customer_id"]]))
			{ 
				$bill_company[$row["customer_id"]][0]=$row["bill_company"];
				$bill_firstname[$row["customer_id"]][0]=$row["bill_firstname"];
				$bill_lastname[$row["customer_id"]][0]=$row["bill_lastname"];
				$bill_street[$row["customer_id"]][0]=$row["bill_street"];
				$bill_number[$row["customer_id"]][0]=$row["bill_number"];
				$bill_additional[$row["customer_id"]][0]=$row["bill_additional"];
				$bill_zip[$row["customer_id"]][0]=$row["bill_zip"];
				$bill_city[$row["customer_id"]][0]=$row["bill_city"];
				$bill_country[$row["customer_id"]][0]=$row["bill_country"];
				
			}
			else
			{
				$address_known=false;
				for ($i=0; $i<sizeof($bill_company[$row["customer_id"]]); $i++)
				{
					$issame = true;
					if ($bill_company[$row["customer_id"]][$i]!=$row["bill_company"]) $issame=false;
					if ($bill_firstname[$row["customer_id"]][$i]!=$row["bill_firstname"]) $issame=false;
					if ($bill_lastname[$row["customer_id"]][$i]!=$row["bill_lastname"]) $issame=false;
					if ($bill_street[$row["customer_id"]][$i]!=$row["bill_street"]) $issame=false;
					if ($bill_number[$row["customer_id"]][$i]!=$row["bill_number"]) $issame=false;
					if ($bill_additional[$row["customer_id"]][$i]!=$row["bill_additional"]) $issame=false;
					if ($bill_zip[$row["customer_id"]][$i]!=$row["bill_zip"]) $issame=false;
					if ($bill_city[$row["customer_id"]][$i]!=$row["bill_city"]) $issame=false;
					if ($bill_country[$row["customer_id"]][$i]!=$row["bill_country"]) $issame=false;
					
					if($issame) $address_known=true;
					
				}
				if (!$address_known)
				{
					$index=sizeof($bill_company[$row["customer_id"]]);
					
					$bill_company[$row["customer_id"]][$index]=$row["bill_company"];
					$bill_firstname[$row["customer_id"]][$index]=$row["bill_firstname"];
					$bill_lastname[$row["customer_id"]][$index]=$row["bill_lastname"];
					$bill_street[$row["customer_id"]][$index]=$row["bill_street"];
					$bill_number[$row["customer_id"]][$index]=$row["bill_number"];
					$bill_additional[$row["customer_id"]][$index]=$row["bill_additional"];
					$bill_zip[$row["customer_id"]][$index]=$row["bill_zip"];
					$bill_city[$row["customer_id"]][$index]=$row["bill_city"];
					$bill_country[$row["customer_id"]][$index]=$row["bill_country"];
					
				}
			}
		}
	//SHIP ADRESS
		if (!$row["ship_lastname"]=="" && !$row["ship_street"]=="" && !$row["ship_city"]=="") //CHECK AUF GÜLTIGE ADRESSE
		{
			if (!isset($ship_company[$row["customer_id"]]))
			{ 
				$ship_company[$row["customer_id"]][0]=$row["ship_company"];
				$ship_firstname[$row["customer_id"]][0]=$row["ship_firstname"];
				$ship_lastname[$row["customer_id"]][0]=$row["ship_lastname"];
				$ship_street[$row["customer_id"]][0]=$row["ship_street"];
				$ship_number[$row["customer_id"]][0]=$row["ship_number"];
				$ship_additional[$row["customer_id"]][0]=$row["ship_additional"];
				$ship_zip[$row["customer_id"]][0]=$row["ship_zip"];
				$ship_city[$row["customer_id"]][0]=$row["ship_city"];
				$ship_country[$row["customer_id"]][0]=$row["ship_country"];
				
			}
			else
			{
				$address_known=false;
				for ($i=0; $i<sizeof($ship_company[$row["customer_id"]]); $i++)
				{
					$issame = true;
					if ($ship_company[$row["customer_id"]][$i]!=$row["ship_company"]) $issame=false;
					if ($ship_firstname[$row["customer_id"]][$i]!=$row["ship_firstname"]) $issame=false;
					if ($ship_lastname[$row["customer_id"]][$i]!=$row["ship_lastname"]) $issame=false;
					if ($ship_street[$row["customer_id"]][$i]!=$row["ship_street"]) $issame=false;
					if ($ship_number[$row["customer_id"]][$i]!=$row["ship_number"]) $issame=false;
					if ($ship_additional[$row["customer_id"]][$i]!=$row["ship_additional"]) $issame=false;
					if ($ship_zip[$row["customer_id"]][$i]!=$row["ship_zip"]) $issame=false;
					if ($ship_city[$row["customer_id"]][$i]!=$row["ship_city"]) $issame=false;
					if ($ship_country[$row["customer_id"]][$i]!=$row["ship_country"]) $issame=false;
					
					if($issame) $address_known=true;
					
				}
				if (!$address_known)
				{
					$index=sizeof($ship_company[$row["customer_id"]]);
					
					$ship_company[$row["customer_id"]][$index]=$row["ship_company"];
					$ship_firstname[$row["customer_id"]][$index]=$row["ship_firstname"];
					$ship_lastname[$row["customer_id"]][$index]=$row["ship_lastname"];
					$ship_street[$row["customer_id"]][$index]=$row["ship_street"];
					$ship_number[$row["customer_id"]][$index]=$row["ship_number"];
					$ship_additional[$row["customer_id"]][$index]=$row["ship_additional"];
					$ship_zip[$row["customer_id"]][$index]=$row["ship_zip"];
					$ship_city[$row["customer_id"]][$index]=$row["ship_city"];
					$ship_country[$row["customer_id"]][$index]=$row["ship_country"];
					
				}
			}
		}
	//PHONE
		if (!$row["userphone"]=="" && !$row["userphone"]=="0")
		{
			if (!isset($phone[$row["customer_id"]]))
			{ 
				$phone[$row["customer_id"]][0]=$row["userphone"];
			}
			else
			{
				$phone_known=false;
				for ($i=0; $i<sizeof($phone[$row["customer_id"]]); $i++)
				{
					$issame = true;
					if ($phone[$row["customer_id"]][$i]!=$row["userphone"]) $issame=false;
					if($issame) $phone_known=true;
				}
				if (!$phone_known)
				{
					$index=sizeof($phone[$row["customer_id"]]);
					$phone[$row["customer_id"]][$index]=$row["userphone"];
				}
			}
		}
	//MOBILE
		if (!$row["usermobile"]=="" && !$row["usermobile"]=="0")
		{
			if (!isset($mobile[$row["customer_id"]]))
			{ 
				$mobile[$row["customer_id"]][0]=$row["usermobile"];
			}
			else
			{
				$mobile_known=false;
				for ($i=0; $i<sizeof($mobile[$row["customer_id"]]); $i++)
				{
					$issame = true;
					if ($mobile[$row["customer_id"]][$i]!=$row["usermobile"]) $issame=false;
					if($issame) $mobile_known=true;
				}
				if (!$mobile_known)
				{
					$index=sizeof($mobile[$row["customer_id"]]);
					$mobile[$row["customer_id"]][$index]=$row["usermobile"];
				}
			}
		}
	//FAX
		if (!$row["userfax"]=="" && !$row["userfax"]=="0")
		{
			if (!isset($fax[$row["customer_id"]]))
			{ 
				$fax[$row["customer_id"]][0]=$row["userfax"];
			}
			else
			{
				$fax_known=false;
				for ($i=0; $i<sizeof($fax[$row["customer_id"]]); $i++)
				{
					$issame = true;
					if ($fax[$row["customer_id"]][$i]!=$row["userfax"]) $issame=false;
					if($issame) $fax_known=true;
				}
				if (!$fax_known)
				{
					$index=sizeof($fax[$row["customer_id"]]);
					$fax[$row["customer_id"]][$index]=$row["userfax"];
				}
			}
		}
	//MAIL
		if (!$row["usermail"]=="" && strpos($row["usermail"], "@"))
		{
			if (!isset($mail[$row["customer_id"]]))
			{ 
				$mail[$row["customer_id"]][0]=$row["usermail"];
			}
			else
			{
				$mail_known=false;
				for ($i=0; $i<sizeof($mail[$row["customer_id"]]); $i++)
				{
					$issame = true;
					if ($mail[$row["customer_id"]][$i]!=$row["usermail"]) $issame=false;
					if($issame) $mail_known=true;
				}
				if (!$mail_known)
				{
					$index=sizeof($mail[$row["customer_id"]]);
					$mail[$row["customer_id"]][$index]=$row["usermail"];
				}
			}
		}
		
	} // WHILE ROW
echo sizeof($account_user_id);


	$crm_user_ID=0;
	foreach($account_user_id as $accountuserid)
	{
		
		//CRM_CUSTOMER ANLEGEN
		$adress = array();
		if (isset($customer_master_data_anschrift1[$accountuserid]))
		{
			$crm_customer_address["company"]=$customer_master_data_anschrift1[$accountuserid].' '.$customer_master_data_anschrift2[$accountuserid];
			$crm_customer_address["name"]=$customer_master_data_anschrift3[$accountuserid];
			$crm_customer_address["street1"]=$customer_master_data_strasse[$accountuserid];
			$crm_customer_address["street2"]=$customer_master_data_postfach[$accountuserid];
			$crm_customer_address["zip"]=$customer_master_data_zip[$accountuserid];
			$crm_customer_address["city"]=$customer_master_data_city[$accountuserid];
			$crm_customer_address["country"]=$customer_master_data_country[$accountuserid];
			$crm_customer_address["gewerblich"]=$customer_master_data_gewerblich[$accountuserid];
		}
		elseif (isset($bill_company[$accountuserid])) 
		{
			$crm_customer_address["company"]=$bill_company[$accountuserid][0];
			$crm_customer_address["name"]=$bill_firstname[$accountuserid][0].' '.$bill_lastname[$accountuserid][0];
			$crm_customer_address["street1"]=$bill_street[$accountuserid][0].' '.$bill_number[$accountuserid][0];
			$crm_customer_address["street2"]=$bill_additional[$accountuserid][0];
			$crm_customer_address["zip"]=$bill_zip[$accountuserid][0];
			$crm_customer_address["city"]=$bill_city[$accountuserid][0];
			$crm_customer_address["country"]=$bill_country[$accountuserid][0];
			$crm_customer_address["gewerblich"]=0;

		}
		elseif (isset($ship_company[$accountuserid])) 
		{
			$crm_customer_address["company"]=$ship_company[$accountuserid][0];
			$crm_customer_address["name"]=$ship_firstname[$accountuserid][0].' '.$ship_lastname[$accountuserid][0];
			$crm_customer_address["street1"]=$ship_street[$accountuserid][0].' '.$ship_number[$accountuserid][0];
			$crm_customer_address["street2"]=$ship_additional[$accountuserid][0];
			$crm_customer_address["zip"]=$ship_zip[$accountuserid][0];
			$crm_customer_address["city"]=$ship_city[$accountuserid][0];
			$crm_customer_address["country"]=$ship_country[$accountuserid][0];
			$crm_customer_address["gewerblich"]=0;
		}
		if (isset($phone[$accountuserid])) $crm_customer_phone=$phone[$accountuserid][0]; else $crm_customer_phone="";
		if (isset($mobile[$accountuserid])) $crm_customer_mobile=$mobile[$accountuserid][0]; else $crm_customer_mobile="";
		if (isset($fax[$accountuserid])) $crm_customer_fax=$fax[$accountuserid][0]; else $crm_customer_fax="";
		//STANDARDMAIL
		if (isset($customer_master_data_mail[$accountuserid]))
		{
			$crm_customer_mail=$customer_master_data_mail[$accountuserid];
		}
		elseif (isset($mail[$accountuserid])) 
		{
			$crm_customer_mail=$mail[$accountuserid][0];
		}
		else $crm_customer_mail="";
	
		$res=q("INSERT INTO crm_customers (
			company, 
			name, 
			street1, 
			street2, 
			zip, 
			city, 
			country, 
			phone, 
			mobile, 
			fax, 
			mail, 
			gewerblich, 
			firstmod, 
			firstmod_user, 
			lastmod, 
			lastmod_user
		) VALUES (
			'".mysqli_real_escape_string($dbweb, $crm_customer_address["company"])."', 
			'".mysqli_real_escape_string($dbweb, $crm_customer_address["name"])."', 
			'".mysqli_real_escape_string($dbweb, $crm_customer_address["street1"])."', 
			'".mysqli_real_escape_string($dbweb, $crm_customer_address["street2"])."',
			'".mysqli_real_escape_string($dbweb, $crm_customer_address["zip"])."',
			'".mysqli_real_escape_string($dbweb, $crm_customer_address["city"])."', 
			'".mysqli_real_escape_string($dbweb, $crm_customer_address["country"])."',
			'".mysqli_real_escape_string($dbweb, $crm_customer_phone)."',
			'".mysqli_real_escape_string($dbweb, $crm_customer_mobile)."',
			'".mysqli_real_escape_string($dbweb, $crm_customer_fax)."',
			'".mysqli_real_escape_string($dbweb, $crm_customer_mail)."', 
			".$crm_customer_address["gewerblich"].", 
			".time().", ".$_SESSION["id_user"].", 
			".time().", ".$_SESSION["id_user"]."
		);", $dbweb, __FILE__, __LINE__);
		
		$crm_user_ID=mysqli_insert_id($dbweb);
		
		//Account anlegen
		$res=q("INSERT INTO crm_customer_accounts (
			crm_customer_id, 
			account, 
			account_type, 
			account_user_id, 
			firstmod, 
			firstmod_user, 
			lastmod, 
			lastmod_user
		) VALUES (
			".$crm_user_ID.", 
			1, 
			2, 
			'".mysqli_real_escape_string($dbweb, $accountuserid)."', 
			".time().", 
			".$_SESSION["id_user"].", 
			".time().", 
			".$_SESSION["id_user"]."
		);", $dbweb, __FILE__, __LINE__);
		$id_customer_account=mysqli_insert_id($dbweb);
	
		//Adressen anlegen
			//bill
		if (isset($bill_company[$accountuserid])) {
			for ($i=0; $i<sizeof($bill_company[$accountuserid]); $i++)
			{
				$res=q("INSERT INTO crm_address (
					crm_customer_id, 
					crm_customer_account_id, 
					address_type, 
					company, 
					name, 
					street1, 
					street2, 
					zip, 
					city, 
					country, 
					firstmod, 
					firstmod_user, 
					lastmod, 
					lastmod_user
				) VALUES (
					".$crm_user_ID.",
					".$id_customer_account.", 
					4, 
					'".mysqli_real_escape_string($dbweb, $bill_company[$accountuserid][$i])."', 
					'".mysqli_real_escape_string($dbweb, $bill_firstname[$accountuserid][$i].' '.$bill_lastname[$accountuserid][$i])."', 
					'".mysqli_real_escape_string($dbweb, $bill_street[$accountuserid][$i].' '.$bill_number[$accountuserid][$i])."', 
					'".mysqli_real_escape_string($dbweb, $bill_additional[$accountuserid][$i])."', 
					'".mysqli_real_escape_string($dbweb, $bill_zip[$accountuserid][$i])."', 
					'".mysqli_real_escape_string($dbweb, $bill_city[$accountuserid][$i])."', 
					'".mysqli_real_escape_string($dbweb, $bill_country[$accountuserid][$i])."', 
					".time().", 
					".$_SESSION["id_user"].", 
					".time().", 
					".$_SESSION["id_user"]."
				);", $dbweb, __FILE__, __LINE__);
			}
		}
			//SHIP
		if (isset($ship_company[$accountuserid])) {
			for ($i=0; $i<sizeof($ship_company[$accountuserid]); $i++)
			{
				$res=q("INSERT INTO crm_address (
					crm_customer_id, 
					crm_customer_account_id, 
					address_type, 
					company, 
					name, 
					street1, 
					street2, 
					zip, 
					city, 
					country, 
					firstmod, 
					firstmod_user, 
					lastmod, 
					lastmod_user
				) VALUES (
					".$crm_user_ID.", 
					".$id_customer_account.", 
					2, 
					'".mysqli_real_escape_string($dbweb, $ship_company[$accountuserid][$i])."', 
					'".mysqli_real_escape_string($dbweb, $ship_firstname[$accountuserid][$i].' '.$ship_lastname[$accountuserid][$i])."', 
					'".mysqli_real_escape_string($dbweb, $ship_street[$accountuserid][$i].' '.$ship_number[$accountuserid][$i])."', 
					'".mysqli_real_escape_string($dbweb, $ship_additional[$accountuserid][$i])."', 
					'".mysqli_real_escape_string($dbweb, $ship_zip[$accountuserid][$i])."', 
					'".mysqli_real_escape_string($dbweb, $ship_city[$accountuserid][$i])."', 
					'".mysqli_real_escape_string($dbweb, $ship_country[$accountuserid][$i])."', 
					".time().", 
					".$_SESSION["id_user"].", 
					".time().", 
					".$_SESSION["id_user"]."
				);", $dbweb, __FILE__, __LINE__);
			}
		}
			//PHONE
		if (isset($phone[$accountuserid])) {
			for ($i=0; $i<sizeof($phone[$accountuserid]); $i++)
			{
				$res=q("INSERT INTO crm_numbers (
					crm_customer_id, 
					crm_customer_account_id, 
					number_type, 
					number, 
					firstmod, 
					firstmod_user, 
					lastmod, 
					lastmod_user
				) VALUES (
					".$crm_user_ID.", 
					".$id_customer_account.", 
					2, 
					'".mysqli_real_escape_string($dbweb, $phone[$accountuserid][$i])."',
					".time().", 
					".$_SESSION["id_user"].", 
					".time().", 
					".$_SESSION["id_user"]."
				);", $dbweb, __FILE__, __LINE__);
			}
		}
			// MOBILE
		if (isset($mobile[$accountuserid])) {
			for ($i=0; $i<sizeof($mobile[$accountuserid]); $i++)
			{
				$res=q("INSERT INTO crm_numbers (
					crm_customer_id, 
					crm_customer_account_id,
					number_type, 
					number, 
					firstmod, 
					firstmod_user, 
					lastmod, 
					lastmod_user
				) VALUES (
					".$crm_user_ID.", 
					".$id_customer_account.",
					4, 
					'".mysqli_real_escape_string($dbweb, $mobile[$accountuserid][$i])."', 
					".time().", 
					".$_SESSION["id_user"].", 
					".time().", 
					".$_SESSION["id_user"]."
				);", $dbweb, __FILE__, __LINE__);
			}
		}
			// FAX
		if (isset($fax[$accountuserid])) {
			for ($i=0; $i<sizeof($fax[$accountuserid]); $i++)
			{
				$res=q("INSERT INTO crm_numbers (
					crm_customer_id, 
					crm_customer_account_id, 
					number_type, 
					number, 
					firstmod, 
					firstmod_user, 
					lastmod, 
					lastmod_user
				) VALUES (
					".$crm_user_ID.", 
					".$id_customer_account.", 
					6, 
					'".mysqli_real_escape_string($dbweb, $fax[$accountuserid][$i])."', 
					".time().", 
					".$_SESSION["id_user"].", 
					".time().", 
					".$_SESSION["id_user"]."
				);", $dbweb, __FILE__, __LINE__);
			}
		}
			// MAIL
		if (isset($mail[$accountuserid])) {
			for ($i=0; $i<sizeof($mail[$accountuserid]); $i++)
			{
				$res=q("INSERT INTO crm_numbers (
					crm_customer_id, 
					crm_customer_account_id, 
					number_type, 
					number, 
					firstmod, 
					firstmod_user, 
					lastmod, 
					lastmod_user
				) VALUES (
					".$crm_user_ID.", 
					".$id_customer_account.", 
					8, 
					'".mysqli_real_escape_string($dbweb, $mail[$accountuserid][$i])."', 
					".time().", 
					".$_SESSION["id_user"].", 
					".time().", 
					".$_SESSION["id_user"]."
				);", $dbweb, __FILE__, __LINE__);
			}
		}
	}

echo 'SKRIPTLAUFZEIT: '.(time()-$startzeit).' Sekunden';
?>
	