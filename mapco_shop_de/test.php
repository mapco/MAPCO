<?php
	include("config.php");
	
	$id_vehicle=2045;

	function get_part($GART, $fp)
	{
		global $parts;
		
		for($i=0; $i<sizeof($parts); $i++)
		{
			if($parts[$i]["GART"]==$GART AND ($parts[$i]["fitting_position"]==$fp or $parts[$i]["fitting_position"]=="beidseitig" or $parts[$i]["fitting_position"]=="Vorderachse"))
			{
				return($parts[$i]);
			}
		}
	}

	//get all parts
	$parts=array();
	$i=0;
	$results=q("SELECT * FROM shop_items_vehicles WHERE vehicle_id=".$id_vehicle." AND language_id=1;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$results2=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$results3=q("SELECT * FROM shop_items_de WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
		$row3=mysqli_fetch_array($results3);
		$results4=q("SELECT * FROM cms_articles_images WHERE article_id=".$row2["article_id"]." ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
		$row4=mysqli_fetch_array($results4);

		$crits=explode(";", $row3["short_description"]);
		for($j=0; $j<sizeof($crits); $j++)
		{
			$crit=explode(":", $crits[$j]);
			$name=trim($crit[0]);
			$value=trim($crit[1]);
			if($name=="Einbauseite") $parts[$i]["fitting_position"]=$value;
		}

		$parts[$i]["GART"]=$row2["GART"];
		$parts[$i]["title"]=$row3["title"];
		$parts[$i]["image"]=PATH."files/".floor($row4["file_id"]/1000)."/".$row4["file_id"].".jpg";
		$i++;
	}
	print_r($parts);
	exit;

	//get vehicle	
	$results=q("SELECT * FROM vehicles_de WHERE id_vehicle=".$id_vehicle.";", $dbshop, __FILE__, __LINE__);
	$vehicles_lang=mysqli_fetch_array($results);
	
	//show table front axle
	echo '<table border="1">';
	echo '	<tr>';
	echo '		<td colspan="6">'.$vehicles_lang["BEZ1"].'</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="6">'.$vehicles_lang["BEZ2"].' '.$vehicles_lang["BEZ3"].' '.$vehicles_lang["BJvon"].'-'.$vehicles_lang["BJbis"].'</td>';
	echo '	</tr>';
	echo '	<tr>';
	$part=get_part(914, "vorne links");
	echo '		<td>'.$part["title"].'<br /><img src="'.$part["image"].'" style="width:150px;" /></td>';
	$part=get_part(51, "VL");
	echo '		<td>'.$part["title"].'<br /><img src="'.$part["image"].'" style="width:150px;" /></td>';
	$part=get_part(284, "VL");
	echo '		<td>'.$part["title"].'<br /><img src="'.$part["image"].'" style="width:150px;" /></td>';
	$part=get_part(284, "VR");
	echo '		<td>'.$part["title"].'<br /><img src="'.$part["image"].'" style="width:150px;" /></td>';
	$part=get_part(51, "VR");
	echo '		<td>'.$part["title"].'<br /><img src="'.$part["image"].'" style="width:150px;" /></td>';
	$part=get_part(914, "VR");
	echo '	</tr>';
	echo '	<tr>';
	$part=get_part(2462, "VL");
	echo '		<td>'.$part["title"].'<br /><img src="'.$part["image"].'" style="width:150px;" /></td>';
	$part=get_part(273, "VL");
	echo '		<td>'.$part["title"].'<br /><img src="'.$part["image"].'" style="width:150px;" /></td>';
	$part=get_part(251, "VL");
	echo '		<td>'.$part["title"].'<br /><img src="'.$part["image"].'" style="width:150px;" /></td>';
	$part=get_part(251, "VR");
	echo '		<td>'.$part["title"].'<br /><img src="'.$part["image"].'" style="width:150px;" /></td>';
	$part=get_part(273, "VR");
	echo '		<td>'.$part["title"].'<br /><img src="'.$part["image"].'" style="width:150px;" /></td>';
	$part=get_part(2462, "VR");
	echo '	</tr>';
	echo '</table>';
?>