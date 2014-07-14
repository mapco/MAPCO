<?php
	if ($_GET["lang"]=="" or $_GET["lang"]=="undefined") $_GET["lang"]="de";

	//äöü
	if (!$skip)
	{
		include_once("../config.php");
	}

	if ($_GET["id_model"]!="")
	{
		$results=q("SELECT * FROM vehicles_".$_GET["lang"]." WHERE Exclude=0 AND KModNr=".$_GET["id_model"].";", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$_GET["id_manufacturer"]=$row["KHerNr"];
	}

	if ($_GET["id_vehicle"]!="")
	{
		$results=q("SELECT * FROM vehicles_".$_GET["lang"]." WHERE Exclude=0 AND id_vehicle=".$_GET["id_vehicle"].";", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$_GET["id_model"]=$row["KModNr"];
		$_GET["id_manufacturer"]=$row["KHerNr"];
	}

/*
	echo 'Hersteller: '.$_GET["id_manufacturer"].'<br />';
	echo 'Modell: '.$_GET["id_model"].'<br />';
	echo 'Motor: '.$_GET["id_vehicle"].'<br />';
	echo '<hr />';
*/

	echo '<h1>Fahrzeugsuche</h1>';
	echo '<form>';
	echo '<table>';
	
	echo '	<tr><td>';
	echo '<select style="width:160px;" name="id_manufacturer" onchange="select_manufacturer(this.value)">';
	echo '<option>Hersteller wählen...</option>';
	$results=q("SELECT * FROM vehicles_".$_GET["lang"]." WHERE Exclude=0 GROUP BY KHerNr ORDER BY BEZ1;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		if ($_GET["id_manufacturer"]==$row["KHerNr"]) $selected=' selected="selected"'; else $selected='';
		echo '<option'.$selected.' value="'.$row["KHerNr"].'">'.$row["BEZ1"].'</option>';
	}
	echo '</select>';
	echo '	</td></tr>';


	if ($_GET["id_manufacturer"]!="")
	{
		echo '	<tr><td>';
		echo '<select style="width:160px;" name="id_model" onchange="select_model(this.value)">';
		$results=q("SELECT * FROM vehicles_".$_GET["lang"]." WHERE Exclude=0 AND KHerNr='".$_GET["id_manufacturer"]."' GROUP BY KModNr ORDER BY BEZ2;", $dbshop, __FILE__, __LINE__);
		echo '<option>Modell wählen...</option>';
		while($row=mysqli_fetch_array($results))
		{
			if ($_GET["id_model"]==$row["KModNr"]) $selected=' selected="selected"'; else $selected='';
			echo '<option'.$selected.' value="'.$row["KModNr"].'">'.$row["BEZ2"].'</option>';
		}
		echo '</select>';
		echo '	</td></tr>';
	}
	
	if ($_GET["id_model"]!="")
	{
		echo '	<tr><td>';
		$results=q("SELECT * FROM vehicles_".$_GET["lang"]." WHERE Exclude=0 AND KModNr=".$_GET["id_model"]." ORDER BY BEZ3;", $dbshop, __FILE__, __LINE__);
		echo '<select style="width:160px;" name="id_vehicle" onchange="select_vehicle(this.value)">';
		echo '<option>Typ wählen...</option>';
		while($row=mysqli_fetch_array($results))
		{
			if ($_GET["id_vehicle"]==$row["id_vehicle"]) $selected=' selected="selected"'; else $selected='';
			echo '<option'.$selected.' value="'.$row["id_vehicle"].'">'.$row["BEZ3"].'</option>';
//			echo '<option'.$selected.' value="'.$row["KTypNr"].'">'.$row["Bez"].' ('.baujahr($row["BJVon"]).' - '.baujahr($row["BJBis"]).')</option>';
		}
		echo '</select>';
		echo '	</td></tr>';
	}

	//add to carfleet
	if ($_GET["id_vehicle"]!="")
	{
		echo '	<tr><td><a href="user_carfleet.php?add_vehicle='.$_GET["id_vehicle"].'" />Zum Fuhrpark hinzufügen</a></td></tr>';
	}
	
	echo '</table>';
	echo '</form>';
?>