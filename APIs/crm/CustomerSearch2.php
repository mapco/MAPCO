<?php

	check_man_params(array("mode" => "textNN"));

	if ($_POST["mode"]=="find_customer")
	{
		$required=array("shop_id" => "numericNN", "qry_string" => "textNN");
		check_man_params($required);
	
	
		if ($_POST["shop_id"]==1 || $_POST["shop_id"]==3 || $_POST["shop_id"]==5 || $_POST["shop_id"]==7 || $_POST["shop_id"]==8) $in_shopid="1,3,5,7,8";
		if ($_POST["shop_id"]==2 || $_POST["shop_id"]==4 || $_POST["shop_id"]==6) $in_shopid="2,4,6";
	
		$qry_string=explode(' ', strtolower($_POST["qry_string"]));
		
		//CUT SHORT STRINGs
		for ($i=0; $i<sizeof($qry_string); $i++)
		{
			if (strlen($qry_string[$i])<3) unset($qry_string[$i]);
		}
	
		$sizeof_qry_string=sizeof($qry_string);
	
		$haystack=array();
		//SEARCH FOR ADDRESS IN SHOP_ORDERS
		$res_adr=q("SELECT * FROM shop_orders WHERE shop_id IN (".$in_shopid.");", $dbshop, __FILE__, __LINE__);
		while ($row_adr=mysql_fetch_array($res_adr))
		{
			$haystack_address=strtolower($row_adr["bill_company"])." ".strtolower($row_adr["bill_firstname"])." ".strtolower($row_adr["bill_lastname"])." ".strtolower($row_adr["bill_street"])." ".strtolower($row_adr["bill_number"])." ".strtolower($row_adr["bill_additional"])." ".strtolower($row_adr["bill_zip"])." ".strtolower($row_adr["bill_city"])." ".strtolower($row_adr["bill_country"])." ".strtolower($row_adr["bill_country_code"])." ".strtolower($row_adr["usermail"])." ".strtolower($row_adr["userphone"])." ".strtolower($row_adr["usermobile"])." ".strtolower($row_adr["userfax"]);
	
			$match=0;
			for ($i=0; $i<$sizeof_qry_string; $i++)
			{
				if (strpos(" ".$haystack_address, $qry_string[$i])>0) {$match++;}
			}
			
			if ($match>0)
			{
	//			echo $haystack_address."<br />";
				
				if (isset($haystack[$row_adr["customer_id"]]))
				{
					$haystack[$row_adr["customer_id"]][sizeof($haystack[$row_adr["customer_id"]])]=$haystack_address;
				}
				else
				{
					$haystack[$row_shop_bill_adr["customer_id"]][0]=$haystack_address;
				}
			}
		}
	
		//search cms_users
		$res_account=q("SELECT * FROM cms_users WHERE shop_id IN (".$in_shopid.");", $dbweb, __FILE__, __LINE__);
		while ($row_account=mysql_fetch_array($res_account))
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
	
		//search for users from crm_customer_accounts
		$res_account=q("SELECT * FROM crm_customer_accounts3 WHERE shop_id IN (".$in_shopid.");", $dbweb, __FILE__, __LINE__);
		while ($row_account=mysql_fetch_array($res_account))
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
	}
	
	if ($_POST["mode"]=="show_customer")
	{
		check_man_params(array("user_id" => "numericNN"));
		$customer=array($_POST["user_id"] =>$_POST["user_id"]);
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
		while ($row_cms_user=mysql_fetch_array($res_cms_user))
		{
			$name=$row_cms_user["name"];
			$username=$row_cms_user["username"];
		}
		$xmldata.='		<name><![CDATA['.$name.']]></name>'."\n";
		$xmldata.='		<username><![CDATA['.$username.']]></username>'."\n";
	
		$xmldata.='		<addresses>'."\n";
		//GET ADDRESS
		$checkstrings=array();
		$res_adr=q("SELECT * FROM shop_orders WHERE customer_id = ".$userid.";", $dbshop, __FILE__, __LINE__);
		while ($row_adr=mysql_fetch_array($res_adr))
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

?>