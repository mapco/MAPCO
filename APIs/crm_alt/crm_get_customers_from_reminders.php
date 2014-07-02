<?php
	if ( !isset($_POST["id_user"]) )
	{
		echo '<crm_get_customers_from_remindersResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>User nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine UserID angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_get_customers_from_remindersResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["mode"]) )
	{
		echo '<crm_get_customers_from_remindersResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Anzeigemodus nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Anzeigemodus angegeben werden. (all | now | later)</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_get_customers_from_remindersResponse>'."\n";
		exit;
	}


	//GET CUSTOMER DATA
	$res_customer=q("SELECT * FROM crm_customers; ", $dbweb, __FILE__, __LINE__);
	while ($row_customer=mysql_fetch_array($res_customer))
	{
		if ($row_customer["company"]=="")
		{
			$crm_customer_name[$row_customer["id_crm_customer"]]=$row_customer["name"];
		}
		else
		{
			$crm_customer_name[$row_customer["id_crm_customer"]]=$row_customer["company"]." ".$row_customer["name"];
		}
	
		$crm_customer_street1[$row_customer["id_crm_customer"]]=$row_customer["street1"];
		$crm_customer_street2[$row_customer["id_crm_customer"]]=$row_customer["street2"];
		$crm_customer_zip[$row_customer["id_crm_customer"]]=$row_customer["zip"];
		$crm_customer_city[$row_customer["id_crm_customer"]]=$row_customer["city"];
		$crm_customer_country[$row_customer["id_crm_customer"]]=$row_customer["country"];
	}
	

	//GET NOTES
	$res_notes=q("SELECT customer_id FROM crm_customer_notes;", $dbweb, __FILE__, __LINE__);
	while ($row_notes=mysql_fetch_array($res_notes))
	{
		if (isset($notes[$row_notes["customer_id"]]))
		{
			$notes[$row_notes["customer_id"]]++;
		}
		else 
		{
			$notes[$row_notes["customer_id"]]=1;
		}
	}
	//GET COMMUNICATIONS
	$res_comm=q("SELECT * FROM crm_communications;", $dbweb, __FILE__, __LINE__);
	while ($row_comm=mysql_fetch_array($res_comm))
	{
		if (isset($communications[$row_comm["customer_id"]]))
		{
			$communications[$row_comm["customer_id"]]++;
		}
		else 
		{
			$communications[$row_comm["customer_id"]]=1;
		}
		if ($row_comm["reminder"]!=0)
		{
			if (isset($reminder[$row_comm["customer_id"]]))
			{
				if ($reminder[$row_comm["customer_id"]]<=$row_comm["reminder"]) $reminder[$row_comm["customer_id"]]=$row_comm["reminder"];
			}
			else
			{
				$reminder[$row_comm["customer_id"]]=$row_comm["reminder"];
			}
		}
	}

	$lists=array();
	$res_lists=q("SELECT * FROM crm_costumer_lists;", $dbweb, __FILE__, __LINE__);
	while ($row_lists=mysql_fetch_array($res_lists))
	{
		$lists[$row_lists["id_list"]]=$row_lists["title"];
	}
	
	$list_customers=array();
	$res_lists_customer=q("SELECT * FROM crm_costumer_lists_customers WHERE firstmod_user=".$_SESSION["id_user"].";",$dbweb, __FILE__, __LINE__);
	while($row_lists_customer=mysql_fetch_array($res_lists_customer))
	{
		if (isset($list_customers[$row_lists_customer["customer_id"]]))
		{
			$list_customers[$row_lists_customer["customer_id"]][sizeof($list_customers[$row_lists_customer["customer_id"]])]=$row_lists_customer["list_id"];
		}
		else
		{
			$list_customers[$row_lists_customer["customer_id"]][0]=$row_lists_customer["list_id"];
		}

	}

	$today=number_format(date("Ymd"));

	$res=q("SELECT * FROM crm_communications WHERE firstmod_user = ".$_POST["id_user"]." AND NOT reminder = 0;", $dbweb, __FILE__, __LINE__);
	$counter=0;
	
	if (mysql_num_rows($res)>0)
	{
		$xmldata="";
	
		while($row=mysql_fetch_array($res))
		{
			
			if (($_POST["mode"]=="now" && $today>number_format(date("Ymd", $row["reminder"]))) || ($_POST["mode"]=="later" && $today<=number_format(date("Ymd", $row["reminder"]))) || $_POST["mode"]=="all" )	
			{
			
				$counter++;
				$xmldata.="<customer>";
				$xmldata.="<customer_id>".$row["customer_id"]."</customer_id>";
				if (isset($reminder[$row["customer_id"]]))
				{
					$xmldata.="<reminder>".date("d.m.Y H:i", $reminder[$row["customer_id"]])."</reminder>";
				}
				else 
				{
					$xmldata.="<reminder></reminder>";
				}
				$xmldata.="<name><![CDATA[".$crm_customer_name[$row["customer_id"]]."]]></name>";
				$xmldata.="<street1><![CDATA[".$crm_customer_street1[$row["customer_id"]]."]]></street1>";
				$xmldata.="<street2><![CDATA[".$crm_customer_street2[$row["customer_id"]]."]]></street2>";
				$xmldata.="<zip><![CDATA[".$crm_customer_zip[$row["customer_id"]]."]]></zip>";
				$xmldata.="<city><![CDATA[".$crm_customer_city[$row["customer_id"]]."]]></city>";
				$xmldata.="<country><![CDATA[".$crm_customer_country[$row["customer_id"]]."]]></country>";
				if (isset($notes[$row["customer_id"]]))
				{
					$xmldata.="<notes>".$notes[$row["customer_id"]]."</notes>";
				}
				else
				{
					$xmldata.="<notes>0</notes>";
				}
				if (isset($communications[$row["customer_id"]]))
				{
					$xmldata.="<communications>".$communications[$row["customer_id"]]."</communications>";
				}
				else 
				{
					$xmldata.="<communications>0</communications>";
				}
				$xmldata.="<inlists>\n";
				if (isset($list_customers[$row["customer_id"]]))
				{
					for($i=0; $i<sizeof($list_customers[$row["customer_id"]]); $i++)
					{
						$xmldata.="<inlist>".$lists[$list_customers[$row["customer_id"]][$i]]."</inlist>\n";
					}
				}
				$xmldata.="</inlists>\n";
	
				$xmldata.="</customer>";
			}
		}
	}
		
echo "<crm_get_customers_from_remindersResponse>\n";
echo "<Ack>Success</Ack>\n";
echo "<count>".$counter."</count>\n";
echo "<listowner>0</listowner>\n";
	echo $xmldata;
echo "</crm_get_customers_from_remindersResponse>";

?>