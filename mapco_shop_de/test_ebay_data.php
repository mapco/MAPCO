<?php
	include("config.php");
/*
	// CHECK FOR UNIQUE TRANSACTIONIDs IN shop_orders_items
	$items = array();
	$duplicate = array();
	$res = q("SELECT * FROM shop_orders_items WHERE NOT foreign_transactionID = '' AND order_id > 1720000 ORDER BY order_id;", $dbshop, __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($res))
	{
		if (isset($items[$row["foreign_transactionID"]]))
		{
			$items[$row["foreign_transactionID"]]++;
			$duplicate[$row["foreign_transactionID"]]=$items[$row["foreign_transactionID"]];
			
		}
		else
		{
			$items[$row["foreign_transactionID"]]=1;
		}
	}
	
	foreach ($duplicate as $transactionID => $rest)
	{
		
		echo $transactionID."~".$duplicate[$transactionID]."<br />";
	}
*/
/*
//SET DATA FOR CMS_USERS_SITES_TEST
	$shopsite=array();
	$shopsite[1]=1;
	$shopsite[2]=2;
	$shopsite[3]=1;
	$shopsite[4]=2;
	$shopsite[5]=1;
	$shopsite[6]=2;
	$shopsite[7]=7;
	$shopsite[8]=1;
	$shopsite[9]=8;
	$shopsite[10]=9;
	$shopsite[11]=10;
	$shopsite[12]=11;
	$shopsite[13]=12;
	$shopsite[14]=13;
	$shopsite[15]=14;
	$shopsite[16]=15;
	$shopsite[17]=16;

	$res_user = q("SELECT * FROM crm_customer_accounts3;", $dbweb, __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($res_user))
	{
		
		//q("INSERT INTO cms_users_sites_test (user_id, site_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$row["id_user"].", ".$shopsite[$row["shop_id"]].", ".time().", 1, ".time().", 1);", $dbweb, __FILE__, __LINE__);
		q("UPDATE crm_customer_accounts3 SET site_id = ".$shopsite[$row["shop_id"]].", shop_type=2 WHERE id_customer_account = ".$row["id_customer_account"].";", $dbweb, __FILE__, __LINE__);

	}
	
	echo "fertig".$row["id_user"];
	
*/
/*
//GET ORDERS WITH NO BILL ADRESS ID
$res = q ("SELECT * FROM shop_orders WHERE shop_id IN (3,4,5) AND bill_adr_id = 0;", $dbshop, __FILE__, __LINE__);
echo "Orders ohne BillAdrID: ".mysqli_num_rows($res)."<br />";
$res = q ("SELECT * FROM shop_orders WHERE shop_id IN (3,4,5) AND bill_adr_id = 0 AND NOT bill_street ='' AND NOT customer_id = 0;", $dbshop, __FILE__, __LINE__);
echo "Orders ohne BillAdrID die eine Adresse haben: ".mysqli_num_rows($res)."<br />";
$userids=array();
$userids2=array();
$f_orderid=array();
while ($row = mysqli_fetch_array($res))
{
	$userids[]=$row["customer_id"];
	$userids2[$row["customer_id"]]=1;
	
	echo "UserID".$row["customer_id"];
	
	//GET F_ID from EBAY
	$res_f_adr = q("SELECT * FROM ebay_orders WHERE OrderID = '".$row["foreign_OrderID"]."';", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_f_adr)>0)
	{
		$row_f_adr = mysqli_fetch_array($res_f_adr);
		echo " F_adrID: ".$row_f_adr["ShippingAddressAddressID"];
		
		$res_shop_adr = q("SELECT * FROM shop_bill_adr WHERE user_id = ".$row["customer_id"]." AND foreign_address_id ='".$row_f_adr["ShippingAddressAddressID"]."';", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_shop_adr)>0)
		{
			$row_shop_adr = mysqli_fetch_array($res_shop_adr);
			echo " SHOP_BILL_ADR_ID: ".$row_shop_adr["adr_id"];
			if (mysqli_num_rows($res_shop_adr)>1) {
				echo "MULTIPLE<br />"; }
			 else {
				//  q("UPDATE shop_orders SET bill_adr_id = ".$row_shop_adr["adr_id"]." WHERE id_order = ".$row["id_order"].";", $dbshop, __FILE__, __LINE__);
			 		echo "UPDATED!!!!!!!!!!!!";
					echo "<br />";
			 
			 
			 }
		}
		else echo "<br />";
	}
	else echo "<br />";
}
echo "DAVON ".sizeof($userids2)." verschiedene User <br />";
	
$res_adr = q("SELECT * FROM shop_bill_adr WHERE user_id IN (".implode(",", $userids).") AND NOT foreign_address_id = '';", $dbshop, __FILE__, __LINE__);
echo "ES existieren ".mysqli_num_rows($res_adr)." Adresse <br />";
*/

//FIND ORDERS WITH NO ITEMS
/*
$items = array();
$res_items = q ("SELECT order_id FROM shop_orders_items GROUP BY order_id;", $dbshop, __FILE__, __LINE__);
while ($row_items = mysqli_fetch_array($res_items))
{
	$items[$row_items["order_id"]]=1;
}
$orders = array();
$counter=0;
$res_orders = q ("SELECT id_order FROM shop_orders WHERE NOT ordertype_id = 6;", $dbshop, __FILE__, __LINE__);
while ($row_orders = mysqli_fetch_array($res_orders))
{
	if (!isset($items[$row_orders["id_order"]])  ) 
	{
		echo $row_orders["id_order"]."<br />";
		$counter++;
	}
}
echo "COUNTER: ".$counter;
*/

/*
//GET ORDERS WITH CUSTOMER ID = 0
$res = q("SELECT * FROM shop_orders WHERE NOT customer_id = 0;", $dbshop, __FILE__, __LINE__);
echo mysqli_num_rows($res);
$users = array();
$counter=0;
while ($row = mysqli_fetch_array($res))
{
	if (!isset($users[$row["customer_id"]][$row["shop_id"]]))
	{
		$users[$row["customer_id"]][$row["shop_id"]]=1;
	}
	else
	{
		$users[$row["customer_id"]][$row["shop_id"]]++;
	}
}

foreach ($users as $user => $shopid)
{
	if (sizeof($shopid)>1)
	{
		echo "USERID: ".$user;
		foreach ($shopid as $shop => $counts)
		{
			echo " Shop: <b>".$shop."</b>*".$counts;
		}
		echo "<br />";
		
		$counter++;
	}
}

echo "ANZAHL: ".$counter;
*/
/*
//GET SHOPS && SITE IDs
$site_shop = array();
$res_shop = q("SELECT * FROM shop_shops WHERE NOT site_id =0 AND active = 1;", $dbshop, __FILE__, __LINE__);
while ($row_shops = mysqli_fetch_array($res_shop))
{
	$site_shop[$row_shops["site_id"]]=$row_shops["id_shop"];
}

//GET USERS
$users=array();
$res_users=q("SELECT * FROM cms_users_sites;", $dbweb, __FILE__, __LINE__);
while ($row_users = mysqli_fetch_array($res_users))
{
	if (isset($users[$row_users["user_id"]]) && $users[$row_users["user_id"]]!=$row_users["site_id"]) 
	{
//		echo "MULTISITE: ".$row_users["user_id"]."<br />";
		$users[$row_users["user_id"]]=0;
	}
	else
	{
		$users[$row_users["user_id"]]=$row_users["site_id"];
		
	}
}
echo "+++++++++++++++++++++++++++++++++++++++++++++++++<br />";
$counter=0;
$customer_err=array();
$res = q("SELECT * FROM shop_orders WHERE NOT customer_id = 0 and shop_id = 1 ORDER BY firstmod", $dbshop, __FILE__, __LINE__);
echo mysqli_num_rows($res);
while ($row = mysqli_fetch_array($res))
{
	if ($row["shop_id"] != $site_shop[$users[$row["customer_id"]]] && $users[$row["customer_id"]]!=0)
	{
		echo "USER: ".$row["customer_id"]." OrderID ".$row["id_order"]." SHOP_ID alt: ".$row["shop_id"]." ShopID neu: ".$site_shop[$users[$row["customer_id"]]]."<br />";
//		q("UPDATE shop_orders SET shop_id = ".$site_shop[$users[$row["customer_id"]]]." WHERE id_order = ".$row["id_order"].";", $dbshop, __FILE__, __LINE__);
		$counter++;
		$customer_err[$row["customer_id"]]=1;
	}
}
echo "ANZAHL: ".$counter."<br />";

foreach ($customer_err as $user => $userdata)
{
	echo $user."<br />";
}

*/


//$_SESSION["id_user"]=70390;
/*
$res = q("SELECT * FROM payment_notification_messages3 WHERE id = 22116", $dbshop, __FILE__, __LINE__);
$row = mysqli_fetch_assoc($res);

$text = array();

$text = explode("&", $row["message"]);

for ($i=0; $i<sizeof($text); $i++)
{
	echo $text[$i]."<br />";
}
*/

//include("templates/".TEMPLATE_BACKEND."/footer.php");
$transactions=array();
$counter=0;
$res = q("SELECT * FROM shop_orders_items WHERE NOT foreign_transactionID=''", $dbshop, __FILE__, __LINE__);
while ($row = mysqli_fetch_assoc($res))
{
	if (isset($transactions[$row["foreign_transactionID"]]) )
	{
		echo $row["order_id"]." ".$row["foreign_transactionID"]."<br />";
		$counter++;
	}
	else
	{
			$transactions[$row["foreign_transactionID"]]= $row["order_id"];
	}
}
echo $counter;



?>	

