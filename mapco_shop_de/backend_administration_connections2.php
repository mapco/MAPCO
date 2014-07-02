<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php?lang='.$_GET["lang"].'">'.t("Backend").'</a>';
	echo ' > <a href="backend_administration_index.php?lang='.$_GET["lang"].'">'.t("Administration").'</a>';
	echo ' > Verbindungen';
	echo '</p>';
	
	echo '<h1>Verbindungen</h1>';
/*
	$results=q("SELECT * FROM ebay_orders WHERE CreatedTimeTimestamp>".mktime(0,0,0,7,1,2013)." order by CreatedTimeTimestamp ;", $dbshop, __FILE__, __LINE__);
	echo mysqli_num_rows($results);
	while($row=mysqli_fetch_array($results))
	{
		echo date("d.m.Y H:i", $row["CreatedTimeTimestamp"])."<br />";
	}
	exit;
*/
	$results=q("SELECT * FROM shop_orders WHERE shop_id=4 AND firstmod>".mktime(0,0,0,6,25,2013)." AND firstmod<".mktime(23,59,59,7,2,2013).";", $dbshop, __FILE__, __LINE__);
	$i=0;
	while( $row=mysqli_fetch_array($results) )
	{
		$results2=q("SELECT * FROM ebay_orders WHERE OrderID='".$row["foreign_OrderID"]."';", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results2)==0 )
		{
			$i++;
			echo '<br />'.$i.'. '.date("d-m-Y H:i", $row["firstmod"]).' '.$row["foreign_OrderID"].' '.$row["shipping_details"].' nicht gefunden';
		}
	}
			exit;
	echo '<br />';
	echo mysqli_num_rows($results);
	

	//all connections in the last 60 seconds
	$connection=array();
	$results=q("SELECT * FROM cms_connections;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if( isset($connection[$row["ip"]]) )
		{
			$connection[$row["ip"]]["count"]++;
		}
		else
		{
			$connection[$row["ip"]]["count"]=1;
			$connection[$row["ip"]]["ip"]=$row["ip"];
		}
	}
	rsort($connection);
	echo '<table class="hover" style="float:left;">';
	echo '	<tr>';
	echo '		<th>Nr.</th>';
	echo '		<th>IP-Adresse</th>';
	echo '		<th>Verbindungen</th>';
	echo '	</tr>';
	for($i=0; $i<sizeof($connection); $i++)
	{
		echo '<tr>';
		echo '	<td>'.($i+1).'</td>';
		echo '	<td><a href="http://www.ip-adress.com/ip_lokalisieren/'.long2ip($connection[$i]["ip"]).'" target="_blank">'.long2ip($connection[$i]["ip"]).'</a></td>';
		echo '	<td>'.$connection[$i]["count"].'</td>';
		echo '</tr>';
	}
	echo '</table>';

	//all blocked users
	echo '<table class="hover" style="float:left">';
	echo '	<tr><th>IP-Adresse</th><th>Gesperrt bis</th></tr>';
	$results=q("SELECT * FROM cms_connections_blocked;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '<tr>';
		echo '	<td><a href="http://www.ip-adress.com/ip_lokalisieren/'.long2ip($row["ip"]).'" target="_blank">'.long2ip($row["ip"]).'</a></td>';
		echo '	<td>'.date("d-m-Y H:i", $row["time"]).'</td>';
		echo '</tr>';
	}
	echo '</table>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>