<?php
	include("config.php");
	include("functions/cms_createPassword.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	set_time_limit(0);
	$start=time();
	
	
	//GET SHOP ORDERS
	$res_shop=q("SELECT * FROM shop_orders WHERE NOT foreign_OrderID ='';", $dbshop, __FILE__, __LINE__);
	while ($row_shop=mysqli_fetch_array($res_shop)) 
	{
		$shop[$row_shop["foreign_OrderID"]]=$row_shop["crm_customer_id"];
	}

	
	//GET existing CMS users
	$CMS=array();
	$res_CMS=q("SELECT * FROM cms_users WHERE shop_id = 2;" , $dbweb, __FILE__, __LINE__);
	while ($row_CMS=mysqli_fetch_array($res_CMS))
	{
		$CMS[$row_CMS["username"]]=$row_CMS["id_user"];
	}
	
	$countries=array();
	$res_countries=q("SELECT * FROM shop_countries;", $dbshop, __FILE__, __LINE__);
	while ($row_countries=mysqli_fetch_array($res_countries))
	{
		$countries[$row_countries["country_code"]]=$row_countries["id_country"];
	}

	$mail=array();
	$res_mail=q("SELECT * FROM crm_numbers2 WHERE number_type = 7;", $dbweb, __FILE__, __LINE__);
	while ($row_mail=mysqli_fetch_array($res_mail))
	{
			$mail[$row_mail["crm_customer_id"]]=$row_mail["number"];
	}
	$userid=array();
	$res_userid=q("SELECT * FROM crm_customer_accounts2 WHERE account = 3 OR account = 4;", $dbweb, __FILE__, __LINE__);
	while ($row_userid=mysqli_fetch_array($res_userid))
	{
		$userid[$row_userid["crm_customer_id"]]=$row_userid["account_user_id"];
	}
	$usermail=array();
	//matching mail and account
	
	while (list ($key, $val) = each ($mail))
	{
		if (isset($userid[$key])) $usermail[$userid[$key]]=$val;
	}
	
	$items=array();
	
	$res_items=q("SELECT * FROM ebay_orders_items WHERE account_id=2;", $dbshop, __FILE__, __LINE__);
	while($row_items=mysqli_fetch_array($res_items))
	{
		$items[$row_items["OrderID"]]=$row_items;
	}
	$orders=array();
	$res_orders=q("SELECT * FROM ebay_orders WHERE account_id=2;", $dbshop, __FILE__, __LINE__);
	while($row_orders=mysqli_fetch_array($res_orders))
	{
		$orders[$row_orders["OrderID"]]=$row_orders;
		$orders[$row_orders["OrderID"]]["usermail"]=$items[$row_orders["OrderID"]]["BuyerEmail"];
		if (isset($shop[$row_orders["OrderID"]])) 
		{
			$orders[$row_orders["OrderID"]]["crm_customer_id"]=$shop[$row_orders["OrderID"]];
		}
		else
		{
			$orders[$row_orders["OrderID"]]["crm_customer_id"]=0;
		}
		
		if ($orders[$row_orders["OrderID"]]["usermail"]=="Invalid Request")
		{
			if (isset($usermail[$orders[$row_orders["OrderID"]]["BuyerUserID"]]))
			{
				$orders[$row_orders["OrderID"]]["usermail"]=$usermail[$orders[$row_orders["OrderID"]]["BuyerUserID"]];
			//	echo "<b>************************".$orders[$row_orders["OrderID"]]["usermail"]."</b><br />";
			}
			else
			{
				//echo "<i>".$orders[$row_orders["OrderID"]]["usermail"]."</i><br />";
			}
		}
		else
		{
			//echo $orders[$row_orders["OrderID"]]["usermail"]."<br />";
		}
	}

//CRM DATEN GENERIEREN

	$cms_user = array();
	$cms_user_mail=array();
	$cms_user_user_id=array();
	$cms_user_address_id=array();
	
	$knowncounter=0;
	foreach ($orders as $order)
	{
	//	echo "ORDERID: ".$order["OrderID"]."<br />";
		$user=0;
		$address_id=0;
		$user_id=0;
		$mail=0;
		//check if address is known
		if (isset($cms_user_address_id[$order["ShippingAddressAddressID"]]) && $order["ShippingAddressAddressID"]!="") 
		{
			$user=$cms_user_address_id[$order["ShippingAddressAddressID"]];
			$address_id=$cms_user_address_id[$order["ShippingAddressAddressID"]];
			//echo "KNOWN address".$cms_user_address_id[$order["ShippingAddressAddressID"]]."<br />";
			$knowncounter++;
		}
		
		//check if userid is known
		if (isset($cms_user_user_id[$order["BuyerUserID"]])) 
		{
			
			if ($user == 0)
			{
				$user=$cms_user_user_id[$order["BuyerUserID"]];
			}
			$user_id=$cms_user_user_id[$order["BuyerUserID"]];
			echo "KNOWN UserID".$order["BuyerUserID"]."<br />";
		}
		//check if mail is known
		if ($order["usermail"]!="Invalid Request" && $order["usermail"]!="")
		{
			if (isset($cms_user_user_id[$order["usermail"]])) 
			{
				if ($user == 0)
				{
					$user=$cms_user_user_id[$order["usermail"]];
				}
				$mail=$cms_user_user_id[$order["usermail"]];
				echo "KNOWN Mail".$order["usermail"]."<br />";
			}
			
		}
		
		//CONVERT ADDRESS
		
			if (strpos($order["ShippingAddressName"]," ")===false)
			{
				$bill_firstname=substr($order["ShippingAddressName"], 0, strpos($order["ShippingAddressName"],"."));
				
				$bill_lastname=substr($order["ShippingAddressName"], strpos($order["ShippingAddressName"],".")+1);
			}
			else
			{
				$bill_firstname=substr($order["ShippingAddressName"], 0, strpos($order["ShippingAddressName"]," "));
				
				$bill_lastname=substr($order["ShippingAddressName"], strpos($order["ShippingAddressName"]," ")+1);
			}
			
			if ($bill_firstname=="")
			{
				$bill_lastname=$order["ShippingAddressName"];
			}
				
			$has_number=false;		
			$pos=0;
			for ($i=strlen($order["ShippingAddressStreet1"])-1; $i>-1; $i--)
			{
				if ((is_numeric(substr($order["ShippingAddressStreet1"],$i, 1)) || substr($order["ShippingAddressStreet1"],$i, 1)=="/") && $pos==0)
				{
					if (!$has_number) $has_number=true;
				}
				else
				{
					if ($has_number && $pos==0) $pos=$i;
				}
			}
			if($pos==0)
			{
				$bill_street1=$order["ShippingAddressStreet1"];
				$bill_streetNumber="0";
			}
			else
			{
				$bill_street1=trim(substr($order["ShippingAddressStreet1"], 0, $pos+1));	
				$bill_streetNumber=trim(substr($order["ShippingAddressStreet1"], $pos+1));
			}



		if ($user == 0)
		{
			$i=sizeof($cms_user);
			//Accounts
			$cms_user[$i]["account"][0]["BuyerUserID"]=$order["BuyerUserID"];
			$cms_user[$i]["account"][0]["shop_id"]=$order["account_id"]+2;
			$cms_user[$i]["crm_customer_id"]=$order["crm_customer_id"];
			
			$cms_user_user_id["BuyerUserID"]=$i;
			//Address
			if ($order["ShippingAddressAddressID"]!="")
			{
				$cms_user[$i]["address"][0]["foreign_address_id"]=$order["ShippingAddressAddressID"];
				$cms_user[$i]["address"][0]["shop_id"]=$order["account_id"]+2;
				$cms_user[$i]["address"][0]["firstname"]=$bill_firstname;
				$cms_user[$i]["address"][0]["lastname"]=$bill_lastname;
				$cms_user[$i]["address"][0]["street"]=$bill_street1;
				$cms_user[$i]["address"][0]["number"]=$bill_streetNumber;
				$cms_user[$i]["address"][0]["additional"]=$order["ShippingAddressStreet2"];
				$cms_user[$i]["address"][0]["zip"]=$order["ShippingAddressPostalCode"];
				$cms_user[$i]["address"][0]["city"]=$order["ShippingAddressCityName"];
				$cms_user[$i]["address"][0]["country"]=$order["ShippingAddressCountryName"];
				$cms_user[$i]["address"][0]["country_id"]=$countries[$order["ShippingAddressCountry"]];
				
				$cms_user_address_id[$order["ShippingAddressAddressID"]]=$i;
			}
			//NUMBERS
			if ($order["ShippingAddressPhone"]!="" && $order["ShippingAddressPhone"]!="Invalid Request" )
			{
				$cms_user[$i]["number"][0]["phone"]=$order["ShippingAddressPhone"];
			}
			if ($order["usermail"]!="" && $order["usermail"]!="Invalid Request" )
			{
				$cms_user[$i]["number"][0]["mail"]=$order["usermail"];
				$cms_user_mail[$order["usermail"]]=$i;
			}
			$cms_user[$i]["number"][0]["shop_id"]=$order["account_id"]+2;
			

		}
		else
		// IS KNOWN
		{
			$i=$user;
			if ($user_id==0)
			{
				$j=sizeof($cms_user[$i]["account"]);
				$cms_user[$i]["account"][$j]["BuyerUserID"]=$order["BuyerUserID"];
				$cms_user[$i]["account"][$j]["shop_id"]=$order["account_id"]+2;
				$cms_user_user_id["BuyerUserID"]=$i;
			}
			if ($address_id==0)
			{
				if ($order["ShippingAddressAddressID"]!="")
				{ 
					$j=sizeof($cms_user[$i]["address"]);
					//Address
					$cms_user[$i]["address"][$j]["foreign_address_id"]=$order["ShippingAddressAddressID"];
					$cms_user[$i]["address"][$j]["shop_id"]=($order["account_id"]+2);
					$cms_user[$i]["address"][$j]["firstname"]=$bill_firstname;
					$cms_user[$i]["address"][$j]["lastname"]=$bill_lastname;
					$cms_user[$i]["address"][$j]["street"]=$bill_street1;
					$cms_user[$i]["address"][$j]["number"]=$bill_streetNumber;
					$cms_user[$i]["address"][$j]["additional"]=$order["ShippingAddressStreet2"];
					$cms_user[$i]["address"][$j]["zip"]=$order["ShippingAddressPostalCode"];
					$cms_user[$i]["address"][$j]["city"]=$order["ShippingAddressCityName"];
					$cms_user[$i]["address"][$j]["country"]=$order["ShippingAddressCountryName"];
					$cms_user[$i]["address"][$j]["country_id"]=$countries[$order["ShippingAddressCountry"]];
					$cms_user_address_id[$order["ShippingAddressAddressID"]]=$i;
				}
			}
			if ($user_id==0)
			{
				$j=sizeof($cms_user[$i]["number"]);
				if ($order["ShippingAddressPhone"]!="" && $order["ShippingAddressPhone"]!="Invalid Request" )
				{
					$cms_user[$i]["number"][$j]["phone"]=$order["ShippingAddressPhone"];
				}
				if ($order["usermail"]!="" && $order["usermail"]!="Invalid Request" )
				{
					$cms_user[$i]["number"][$j]["mail"]=$order["usermail"];
					$cms_user_mail[$order["usermail"]]=$i;
				}
				$cms_user[$i]["number"][$j]["shop_id"]=$order["account_id"]+2;
			}
			
		}
	}
	
	echo sizeof($cms_user)."Datensätze";
	$cms_user_=$cms_user;
	unset ($cms_user);
	$i=0;
	foreach ($cms_user_ as $tmp)
	{
		$cms_user[$i]=$tmp;
		$i++;
	}
	//GET DATENSATZ
	$res=q("SELECT datensatz FROM tmp;", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($res);
	$datensatz=$row["datensatz"];
	
	for ($i=$datensatz; $i<sizeof($cms_user); $i++)
	{
		echo "<b>Datensatz ".$i."</b><br />";
		if ($cms_user[$i]["crm_customer_id"]!=0) echo "<b>CRM ID ".$cms_user[$i]["crm_customer_id"]."</b><br />";
		

		
		
		//CMS_USER ANLEGEN
			//check if username exists
			
			//EBAY USERNAME -> CMS USERNAME
		$cms_username="";
		for ($j=0; $j<sizeof($cms_user[$i]["account"]); $j++)
		{
			if (!isset($CMS[$cms_user[$i]["account"][$j]["BuyerUserID"]]) && $cms_username=="" )
			{
				$cms_username=$cms_user[$i]["account"][$j]["BuyerUserID"];
			}
		}
		
		if ($cms_username=="")
		{
			if (isset($cms_user[$i]["number"][0]["mail"])) $cms_username=$cms_user[$i]["number"][0]["mail"];
		}
		
		if ($cms_username=="" && isset($cms_user[$i]["address"]))
		{
			for ($j=0; $j<sizeof($cms_user[$i]["address"]); $j++)
			{
				$tmp=$cms_user[$i]["address"][$j]["lastname"];
				if (!isset($CMS[$tmp]) && $cms_username=="") $cms_username=$tmp;
			}
			if ($cms_username=="")
			{
				$counter=0;
				$tmp=$cms_user[$i]["address"][0]["lastname"];
				while (isset($CMS[$tmp]))
				{
					$counter++;
					$tmp=$cms_user[$i]["address"][0]["lastname"].$counter;
				}
				$cms_username=$tmp;
				
			}
		}
		$cms_mail="";
		for ($j=0; $j<sizeof($cms_user[$i]["number"]); $j++)
		{
			if (isset($cms_user[$i]["number"][$j]["mail"]) & $cms_mail=="")
			{
				$cms_mail=$cms_user[$i]["number"][$j]["mail"]." ";
			}
		}
		
if ($cms_username!="")
{
		//INSERT CMS_USER
		$res=q("INSERT INTO cms_users (shop_id, site_id, username, usermail, password, userrole_id, language_id, active, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (2, 2, '".mysqli_real_escape_string($dbweb, $cms_username)."', '".mysqli_real_escape_string($dbweb, $cms_mail)."', '".mysqli_real_escape_string($dbweb, createPassword(8))."', 5,1,1, ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
		$id_cms_user=mysqli_insert_id($dbweb);
		echo "INSERT".$id_cms_user."<br />";
$CMS[$cms_username]=$id_cms_user;


	//UPDATE SHOP ORDERS
	if ($cms_user[$i]["crm_customer_id"]!=0)
	{
		q("UPDATE shop_orders SET customer_id = ".$id_cms_user." WHERE crm_customer_id = ".$cms_user[$i]["crm_customer_id"].";", $dbshop, __FILE__, __LINE__);
		echo "UPDATED SHOP ORDERS: ".mysqli_affected_rows($dbshop)."<br />";
	}
		//ACCOUNTs
	//	echo "<b>UserIDs: </b>";
		for ($j=0; $j<sizeof($cms_user[$i]["account"]); $j++)
		{
	//		echo $cms_user[$i]["account"][$j]["shop_id"]." ";
			$res=q("INSERT INTO crm_customer_accounts3 (cms_user_id, shop_id, shop_user_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$id_cms_user.", ".$cms_user[$i]["account"][$j]["shop_id"].", '".$cms_user[$i]["account"][$j]["BuyerUserID"]."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
		}
		//ADDRESS
	//	echo "<br />";
	//	echo "<b>Name: </b>";
		if (isset($cms_user[$i]["address"]))
		{
			for ($j=0; $j<sizeof($cms_user[$i]["address"]); $j++)
			{
			//	echo $cms_user[$i]["address"][$j]["firstname"]." ".$cms_user[$i]["address"][$j]["lastname"]."|";
				$res=q("INSERT INTO shop_bill_adr (user_id, shop_id, foreign_address_id, firstname, lastname, street, number, additional, zip, city, country, country_id, standard, active) VALUES (".$id_cms_user.", '".mysqli_real_escape_string($dbshop, $cms_user[$i]["address"][$j]["shop_id"])."', '".mysqli_real_escape_string($dbshop, $cms_user[$i]["address"][$j]["foreign_address_id"])."', '".mysqli_real_escape_string($dbshop, $cms_user[$i]["address"][$j]["firstname"])."', '".mysqli_real_escape_string($dbshop, $cms_user[$i]["address"][$j]["lastname"])."', '".mysqli_real_escape_string($dbshop, $cms_user[$i]["address"][$j]["street"])."', '".mysqli_real_escape_string($dbshop, $cms_user[$i]["address"][$j]["number"])."', '".mysqli_real_escape_string($dbshop, $cms_user[$i]["address"][$j]["additional"])."', '".mysqli_real_escape_string($dbshop, $cms_user[$i]["address"][$j]["zip"])."', '".mysqli_real_escape_string($dbshop, $cms_user[$i]["address"][$j]["city"])."', '".mysqli_real_escape_string($dbshop, $cms_user[$i]["address"][$j]["country"])."', ".$cms_user[$i]["address"][$j]["country_id"].", 1,1 );", $dbshop, __FILE__, __LINE__);
			}
			
		}
	//	echo "<br />";
		
		//MAIL
	//	echo "<b>Mail: </b>";
		for ($j=0; $j<sizeof($cms_user[$i]["number"]); $j++)
		{
			if (isset($cms_user[$i]["number"][$j]["mail"]))
			{
			//	echo $cms_user[$i]["number"][$j]["mail"]." ";
				$res=q("INSERT INTO crm_numbers3 (cms_user_id, shop_id, number_type, number, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$id_cms_user.", ".$cms_user[$i]["number"][$j]["shop_id"].", 7, '".$cms_user[$i]["number"][$j]["mail"]."',".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
			}
		}

		//PHONE
		for ($j=0; $j<sizeof($cms_user[$i]["number"]); $j++)
		{
			if (isset($cms_user[$i]["number"][$j]["phone"]))
			{
				//echo $cms_user[$i]["number"][$j]["phone"]." ";
				$res=q("INSERT INTO crm_numbers3 (cms_user_id, shop_id, number_type, number, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$id_cms_user.", ".$cms_user[$i]["number"][$j]["shop_id"].", 1, '".$cms_user[$i]["number"][$j]["phone"]."',".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
			}
		}
}
	//	echo "<br /><br />";
	q("UPDATE tmp SET datensatz = ".$i.";", $dbweb, __FILE__, __LINE__);
	}

	
//############################################################################################################


	echo "Skriptlaufzeit: ".(time()-$start);
	echo "KNOWNCOUNTER: ".$knowncounter;
	
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>
