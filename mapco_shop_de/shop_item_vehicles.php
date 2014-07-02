<?php
	include("config.php");
	include("functions/cms_url_encode.php");
	include("functions/mapco_baujahr.php");	

	$results=q("SELECT * FROM shop_items_".mysqli_real_escape_string($dbshop, $_GET["lang"])." WHERE id_item='".mysqli_real_escape_string($dbshop, $_GET["id_item"])."';", $dbshop, __FILE__, __LINE__);
	$shop_items_lang=mysqli_fetch_array($results);
	$title='MAPCO '.$shop_items_lang["title"].' günstig online kaufen';
	include("templates/".TEMPLATE."/header.php");

	//TABS
	include("shop_item_menu.php");

	//vehicle applications
	$vapps=array();
	$vapps_criteria=array();
	$results=q("SELECT vehicle_id, criteria FROM shop_items_vehicles WHERE item_id=".$_GET["id_item"]." AND language_id=1;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$vapps[$row["vehicle_id"]]=$row["vehicle_id"];
		$vapps_criteria[$row["vehicle_id"]]=$row["criteria"];
	}
	
	echo '<h2>'.t("Fahrzeugzuordnungen", $_GET["lang"]).'</h2>'."\n";
	echo '<table class="hover">'."\n";
	echo '<tr>'."\n";
	echo '	<th>'.t("Fahrzeug", $_GET["lang"]).'</th>'."\n";
	echo '	<th width="120">'.t("Baujahr", $_GET["lang"]).'</th>'."\n";
	echo '	<th width="100">'.t("Leistung", $_GET["lang"]).'</th>'."\n";
	echo '	<th width="80">'.t("Hubraum", $_GET["lang"]).'</th>'."\n";
	echo '	<th width="80">'.t("KBA-Nr.", $_GET["lang"]).'</th>'."\n";
	echo '</tr>'."\n";
	$results=q("SELECT * FROM vehicles_".$_GET["lang"]." WHERE id_vehicle IN (".implode(", ", $vapps).") ORDER BY BEZ1, BEZ2, BEZ3 LIMIT 20;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if ( isset($vapps[$row["id_vehicle"]]) )
		{
			echo '<tr>';
			echo '	<td>';
			echo '    <a href="'.PATHLANG.'fahrzeugsuche/'.$row["id_vehicle"].'/'.url_encode($row["BEZ1"].' '.$row["BEZ2"].' '.$row["BEZ3"]).'" title="'.$row["BEZ1"].' '.$row["BEZ2"].' '.$row["BEZ3"].'">';
			echo        $row["BEZ1"].' '.$row["BEZ2"].' '.$row["BEZ3"];
			echo '    </a>';
			echo '    <br /><span style="color:#FF0000"><i>'.$vapps_criteria[$row["id_vehicle"]].'</i></span>'."\n";
			echo '  </td>';
			echo '  <td>'.baujahr($row["BJvon"]).' - '.baujahr($row["BJbis"]).'</td>';
			echo '  <td>'.($row["kW"]*1).'KW ('.($row["PS"]*1).'PS)</td>';
			echo '  <td>'.number_format($row["ccmTech"]).'ccm</td>';
			$kba_txt='';
			if(strpos($row["KBA"], ",") === FALSE) $kba_txt=substr($row["KBA"], 0, 4).'-'.substr($row["KBA"], 4, 3);
			else
			{
				$kbas=explode(", ", $row["KBA"]);
				foreach($kbas as $kba)
				{
					if($kba_txt=='') $kba_txt=substr($kba, 0, 4).'-'.substr($kba, 4, 3);
					else $kba_txt.= ', '.substr($kba, 0, 4).'-'.substr($kba, 4, 3);
				}
			}
			echo '  <td>'.$kba_txt.'</td>';
			echo '</tr>';
		}
	}
	echo '</table>'."\n";

	include("templates/".TEMPLATE."/footer.php");
?>