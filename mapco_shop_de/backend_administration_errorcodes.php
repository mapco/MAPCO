<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	if ( isset($_FILES["file"]) )
	{
		//clear table
		q("DELETE FROM cms_errorcodes WHERE errortype_id=".$_POST["id_errortype"].";", $dbweb, __FILE__, __LINE__);

		//cache vehicles file
		$vehicles=array();
		$handle=fopen($_FILES["file"]["tmp_name"], "r");
		$line=fgetcsv($handle, 4096, ";");
		while($line=fgetcsv($handle, 4096, ";"))
		{
			$split=strpos($line[2], "Long error:");
			$shortMsg=substr($line[2], 12, $split-12);
			$longMsg=substr($line[2], $split+12, strlen($line[2]));
			$vehicles[]="(".$_POST["id_errortype"].", ".$line[0].", '".mysqli_real_escape_string($dbweb, $line[1])."', '".mysqli_real_escape_string($dbweb, $shortMsg)."', '".mysqli_real_escape_string($dbweb, $longMsg)."')";
		}
		fclose($handle);
		
		
		//build query;
		$query="INSERT INTO cms_errorcodes (errortype_id, errorcode, type, shortMsg, longMsg) VALUES ".implode(", ", $vehicles).";";
		
		q($query, $dbweb, __FILE__, __LINE__);
		
		echo 'Fehlermeldungen erfolgreich importiert.';
	}

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_administration_index.php">Administration</a>';
	echo ' > Fehlercodes';
	echo '</p>';

	echo '<h1>Fehlercodes</h1>';
	echo '<form method="post" enctype="multipart/form-data">';
	echo '	Fehlerquelle: <select name="id_errortype">';
	$results=q("SELECT * FROM cms_errortypes;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '<option value="'.$row["id_errortype"].'">'.$row["title"].'</option>';
	}
	echo '	</select><br />';
	echo '	<input type="file" name="file" />';
	echo '	<input type="submit" value="Hochladen" />';
	echo '</form>';


	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>