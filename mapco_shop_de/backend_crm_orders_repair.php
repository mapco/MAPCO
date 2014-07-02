<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	
	if (isset($_POST["from_date"]) && isset($_POST["from_time"]))
	{
		$from_date=$_POST["from_date"];
		$from_time=$_POST["from_time"];
	}
	else
	{
		$from_date=date("d.m.Y", time()-(24*3600));
		$from_time=date("H:i", time()-(24*3600));
	}
	if (isset($_POST["to_date"]) && isset($_POST["to_time"]))
	{
		$to_date=$_POST["to_date"];
		$to_time=$_POST["to_time"];
	}
	else
	{
		$to_date=date("d.m.Y");
		$to_time=date("H:i");
	}

	
	
	echo '<b>Fehler anzeigen / beheben:</b><br />';
	echo '<p><form action="'.PATH.'/backend_crm_orders_repair.php" method="POST">';
	echo 'von <input type="text" name="from_date" size=10" value="'.$from_date.'" />&nbsp;<input type="text" name="from_time" size=10" value="'.$from_time.'" /><br />';
	echo 'bis <input type="text" name="to_date" size=10" value="'.$to_date.'" />&nbsp;<input type="text" name="to_time" size=10" value="'.$to_time.'" /><br />';
	echo '<input type="submit" name="show" value="Fehler nur anzeigen" />&nbsp;<input type="submit" name="repair" value="Fehler korrigieren" />';
	echo '</form></p>';
	
	
	
	if (isset($_POST["show"]) || isset($_POST["repair"]))
	{
		$from=mktime(substr($_POST["from_time"],0,strpos($_POST["from_time"],":")), substr($_POST["from_time"],strpos($_POST["from_time"],":")+1), 0, substr($_POST["from_date"],3,2), substr($_POST["from_date"],0,2), substr($_POST["from_date"], 6));
		$to=mktime(substr($_POST["to_time"],0,strpos($_POST["to_time"],":")), substr($_POST["to_time"],strpos($_POST["to_time"],":")+1), 0, substr($_POST["to_date"],3,2), substr($_POST["to_date"],0,2), substr($_POST["to_date"], 6));
		
		
		$ebay=array();
		//$res=q("SELECT * FROM ebay_orders WHERE firstmod > ".(time()-2*3600).";", $dbshop, __FILE__, __LINE__);
		$res=q("SELECT * FROM ebay_orders WHERE firstmod > ".$from." AND firstmod < ".$to." ;", $dbshop, __FILE__, __LINE__);
		while ($row=mysqli_fetch_array($res))
		{
			$ebay[$row["OrderID"]]=$row["id_order"];
		}
	
		//echo sizeof($ebay)."+";

		$shop=array();
		$res2=q("SELECT * FROM shop_orders;", $dbshop, __FILE__, __LINE__);
		while($row2=mysqli_fetch_array($res2))
		{
			if ($row2["foreign_OrderID"]!="") $shop[$row2["foreign_OrderID"]]=0;
		}
		//echo sizeof($shop)."<br />";
		
		while (list ($key, $val) = each ($ebay))
		{
			
			if (!isset($shop[$key])) 
			{
				echo $key."<br />";
				if (isset($_POST["repair"]))
				{
				echo post(PATH."soa/", array("API" => "crm", "Action" => "repair_shop_order_data", "EbayOrderID" => $val))."<br />";
				}
			}
		}
		echo "FERTIG";
	}

	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>