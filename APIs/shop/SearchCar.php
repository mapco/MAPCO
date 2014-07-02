<?php
	include("../functions/cms_t.php");
	include("../functions/mapco_baujahr.php");
	

	if (isset($_POST["id_model"]) && $_POST["id_model"]!="")
	{
		$results=mysql_query("SELECT * FROM vehicles_".$_SESSION["lang"]." WHERE Exclude=0 AND KModNr=".$_POST["id_model"].";", $dbshop) or die("ERROR #193: ".mysql_error($dbshop));
		$row=mysql_fetch_array($results);
		$_GET["id_manufacturer"]=$row["KHerNr"];
	}

	if (isset($_POST["id_vehicle"]) && $_POST["id_vehicle"]>0)
	{
		$results=mysql_query("SELECT * FROM vehicles_".$_SESSION["lang"]." WHERE Exclude=0 AND id_vehicle=".$_POST["id_vehicle"].";", $dbshop) or die("ERROR #193: ".mysql_error($dbshop));
		$row=mysql_fetch_array($results);
		$_POST["id_model"]=$row["KModNr"];
		$_POST["id_manufacturer"]=$row["KHerNr"];
	}

	echo '<div class="suche_head">';
	echo '<h2 style="text-align:center">Fahrzeugauswahl</h2>';
	echo '</div>';

	echo '<div class="pkw_suche_body" style="display:inline">';
	echo '<form>';
	echo '<table>';
	
	echo '	<tr><td>';
	echo '<select style="margin:10px 12px 0px 0px; height:25px; width:330px; border-color:red; border-width:2px; background-color:#FA8;" name="id_manufacturer" onchange="select_manufacturer(this.value)">';
		echo '<option value="0">'.t("Hersteller wählen").'...</option>';
	$results=mysql_query("SELECT * FROM vehicles_".$_SESSION["lang"]." WHERE Exclude=0 GROUP BY KHerNr ORDER BY BEZ1;", $dbshop) or die("ERROR #193: ".mysql_error($dbshop));
	while($row=mysql_fetch_array($results))
	{
		if ($_POST["id_manufacturer"]==$row["KHerNr"]) $selected=' selected="selected"'; else $selected='';
		echo '<option'.$selected.' value="'.$row["KHerNr"].'">'.$row["BEZ1"].'</option>';
	}
	echo '</select>';
	echo '	</td></tr>';
	echo '	<tr><td>';

	if (isset($_POST["id_manufacturer"]) && $_POST["id_manufacturer"]!="")
	{
		echo '<select style="margin:10px 12px 0px 0px; height:25px; width:330px; border-color:red; border-width:2px; background-color:#FA8;" name="id_model" onchange="select_model(this.value)">';
	}
	else
	{
		echo '<select style="margin:10px 12px 0px 0px; height:25px; width:330px; border-color:red; border-width:2px;" name="id_model" onfocus="this.blur()">';
	}
		$results=mysql_query("SELECT * FROM vehicles_".$_SESSION["lang"]." WHERE Exclude=0 AND KHerNr='".$_POST["id_manufacturer"]."' GROUP BY KModNr ORDER BY BEZ2;", $dbshop) or die("ERROR #193: ".mysql_error($dbshop));
		echo '<option value="0">'.t("Modell wählen").'...</option>';
		while($row=mysql_fetch_array($results))
		{
			if ($_GET["id_model"]==$row["KModNr"]) $selected=' selected="selected"'; else $selected='';
			echo '<option'.$selected.' value="'.$row["KModNr"].'">'.$row["BEZ2"].'</option>';
		}
		echo '</select>';
//	}
	echo '	</td></tr>';
	
	if (isset($_POST["id_model"]) && $_POST["id_model"]!="")
	{
		echo '	<tr><td>';
		$fid=array();
		$bez3=array();
		$bjvon=array();
		$bjbis=array();
		$leistungkw=array();
		$leistungps=array();
		$max=strlen(utf8_decode("Typ wählen..."));
		$results=mysql_query("SELECT * FROM vehicles_".$_SESSION["lang"]." WHERE Exclude=0 AND KModNr=".$_POST["id_model"]." ORDER BY BEZ3;", $dbshop) or die("ERROR #193: ".mysql_error($dbshop));
		while($row=mysql_fetch_array($results))
		{
			$fid[]=$row["id_vehicle"];
			$bez=str_replace("  ", " ", trim($row["BEZ3"]));
			$bez3[]=$bez;
			$bjvon[]=$row["BJvon"];
			$bjbis[]=$row["BJbis"];
			$leistungkw[]=($row["kW"]*1).'KW';
			$leistungps[]=($row["PS"]*1).'PS';
			if ($max<strlen($bez)) $max=strlen($bez);
		}
		$max+=3;
		echo '<select style="margin:10px 12px 0px 0px; height:25px; width:330px; border-color:red; background-color:#FA8; border-width:2px; font-family:\'Courier New\', Courier, monospace; font-size:12px border-color:#EE0000;" name="id_vehicle" onchange="select_vehicle(this.value)">';
	}
	else
	{
		echo '	<tr><td>';

		echo '<select style="margin:10px 12px 0px 0px; height:25px;width:330px; border-color:red; border-width:2px; font-family:\'Courier New\', Courier, monospace;" font-size:12px"name="id_vehicle"  onfocus="this.blur()">';
	}
		echo '<option value="0">';
		echo $text=t("Typ wählen").'...';
		for($j=0; $j<($max-strlen(utf8_decode($text))); $j++) echo '&nbsp;';
//		echo t("Baujahr");
		echo '</option>';
		for($i=0; $i<sizeof($bez3); $i++)
		{
			if ($_GET["id_vehicle"]==$fid[$i]) $selected=' selected="selected"'; else $selected='';
			echo '<option'.$selected.' value="'.$fid[$i].'">';
			echo $bez3[$i];
			for($j=0; $j<($max-strlen($bez3[$i])); $j++) echo '&nbsp;';
			echo baujahr($bjvon[$i]).' - '.baujahr($bjbis[$i]).'&nbsp;&nbsp;&nbsp;';
			echo $leistungkw[$i].' / '.$leistungps[$i];
			echo '</option>';
		}
		echo '</select>';
		echo '	</td></tr>';

	echo '</table>';
	echo '</form>';
	echo '</div>';
?>