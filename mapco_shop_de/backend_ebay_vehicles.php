<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	if ( isset($_FILES["file"]) )
	{
		//clear table
		q("DELETE FROM ebay_vehicles WHERE marketplace_id=".$_POST["id_marketplace"].";", $dbshop, __FILE__, __LINE__);

		//cache vehicles file
		$vehicles=array();
		$handle=fopen($_FILES["file"]["tmp_name"], "r");
		$line=fgetcsv($handle, 4096, ";");
		if( $_POST["id_marketplace"]==1 )
		{
			while($line=fgetcsv($handle, 4096, ";"))
			{
				$vehicles[]="(".$_POST["id_marketplace"].", ".$line[0].", '".mysqli_real_escape_string($dbshop, $line[1])."', '".mysqli_real_escape_string($dbshop, $line[2])."', '".mysqli_real_escape_string($dbshop, $line[3])."', '".mysqli_real_escape_string($dbshop, $line[4])."', '".mysqli_real_escape_string($dbshop, $line[5])."', '".mysqli_real_escape_string($dbshop, $line[6])."', '".mysqli_real_escape_string($dbshop, $line[7])."', '".mysqli_real_escape_string($dbshop, $line[8])."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].")";
			}
		}
		else
		{
			while($line=fgetcsv($handle, 4096, ";"))
			{
				$vehicles[]="(".$_POST["id_marketplace"].", ".$line[7].", '".mysqli_real_escape_string($dbshop, $line[0])."', '".mysqli_real_escape_string($dbshop, $line[1])." ".mysqli_real_escape_string($dbshop, $line[3])."', '".mysqli_real_escape_string($dbshop, $line[4])."', '".mysqli_real_escape_string($dbshop, $line[2])."', '".mysqli_real_escape_string($dbshop, $line[5])."', '".mysqli_real_escape_string($dbshop, $line[6])."', '', '".mysqli_real_escape_string($dbshop, $line[8])."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].")";
			}
		}
		fclose($handle);
		
		
		//build query;
		$query="INSERT INTO ebay_vehicles (marketplace_id, KType, Make, Model, Type, Platform, ProductionPeriod, Engine, HSN_TSN, `Update`, firstmod, firstmod_user, lastmod, lastmod_user) VALUES ".implode(", ", $vehicles).";";
		
		q($query, $dbshop, __FILE__, __LINE__);
		
		echo 'Fahrzeugliste erfolgreich importiert.';
		exit;
/*		
		$results=q("SELECT * FROM ebay_vehicles WHERE KType=".$line[0].";", $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows($results)==0 )
		{
			q("INSERT INTO ebay_vehicles (KType, Make, Model, Type, Platform, ProductionPeriod, Engine, HSN_TSN, `Update`, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$line[0].", '".mysqli_real_escape_string($dbshop, $line[1])."', '".mysqli_real_escape_string($dbshop, $line[2])."', '".mysqli_real_escape_string($dbshop, $line[3])."', '".mysqli_real_escape_string($dbshop, $line[4])."', '".mysqli_real_escape_string($dbshop, $line[5])."', '".mysqli_real_escape_string($dbshop, $line[6])."', '".mysqli_real_escape_string($dbshop, $line[7])."', '".mysqli_real_escape_string($dbshop, $line[8])."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
		}
		else
		{
		}
//			echo 'artnr['.$i.']=\''.$line[0]."';\n";
//			echo 'vorgabe['.$i.']='.str_replace(",", ".", $line[2]).";\n";
//			echo 'coss['.$i.']='.str_replace(",", ".", $line[3]).";\n";
			$i++;
			if ($i==5) $i++;



		echo 'Fahrzeugliste erfolgreich importiert.';
*/
	}



	echo '<div style="position:relative;" id="progressbarWrapper" style="width:300px; height: 30px;" class="ui-widget-default">';
	echo '	<div id="progressbar"></div>';
	echo '	<div style="position:absolute; left:0px; top:5px; width:100%; height:25px; text-align:center;" id="progressText"></div>';
	echo '</div>';

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_ebay_index.php">eBay</a>';
	echo ' > Fahrzeugverwendungsliste';
	echo '</p>';

	echo '<h1>eBay-Fahrzeugverwendungsliste</h1>';
	echo '<form method="post" enctype="multipart/form-data">';
	echo '	Marktplatz: <select name="id_marketplace">';
	$results=q("SELECT * FROM ebay_marketplaces;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '<option value="'.$row["id_marketplace"].'">'.$row["title"].'</option>';
	}
	echo '	</select><br />';
	echo '	<input type="file" name="file" />';
	echo '	<input type="submit" value="Hochladen" />';
	echo '</form>';


	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>