<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	if ( isset($_FILES["file"]) )
	{
		//clear table
		q("TRUNCATE cms_zipcodes;", $dbweb, __FILE__, __LINE__);

		//cache vehicles file
		$vehicles=array();
		$handle=fopen($_FILES["file"]["tmp_name"], "r");
//		$line=fgetcsv($handle, 4096, ";");
		while($line=fgetcsv($handle, 4096, ";"))
		{
			$zipcodes[]="(".$line[0].", '".mysqli_real_escape_string($dbweb, $line[1])."', '".mysqli_real_escape_string($dbweb, $line[2])."', '".mysqli_real_escape_string($dbweb, $line[3])."')";
		}
		fclose($handle);
		
		//build query;
		$query="INSERT INTO cms_zipcodes (zipcode, name, latitude, longitude) VALUES ".implode(", ", $zipcodes).";";
		
		q($query, $dbweb, __FILE__, __LINE__);
		
		echo '<div class="success">Postleitzahlen erfolgreich importiert.</div>';
	}

	echo '<div style="position:relative;" id="progressbarWrapper" style="width:300px; height: 30px;" class="ui-widget-default">';
	echo '	<div id="progressbar"></div>';
	echo '	<div style="position:absolute; left:0px; top:5px; width:100%; height:25px; text-align:center;" id="progressText"></div>';
	echo '</div>';

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_admin_index.php">Administration</a>';
	echo ' > Postleitzahlen';
	echo '</p>';

	echo '<h1>Postleitzahlen importieren</h1>';
	echo '<form method="post" enctype="multipart/form-data">';
	echo '	<input type="file" name="file" />';
	echo '	<input type="submit" value="Hochladen" />';
	echo '</form>';
	
	//TEST
	post(PATH."soa/", array("API" => "cms", "Action" => "ZipcodeDistance", "Zipcode1" => "14482", "Zipcode2" => "14822"));

	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>