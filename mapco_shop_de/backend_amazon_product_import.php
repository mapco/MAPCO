<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	if ( isset($_FILES["file"]) )
	{
		//clear table
		q("DELETE FROM amazon_browsenodes WHERE marketplace_id=".$_POST["id_marketplace"].";", $dbshop, __FILE__, __LINE__);

		//cache vehicles file
		$browsenodes=array();
		$handle=fopen($_FILES["file"]["tmp_name"], "r");
		$line=fgetcsv($handle, 4096, ";");
		if( $_POST["id_marketplace"]==1 )
		{
			while($line=fgetcsv($handle, 4096, ";"))
			{
				$line[1]=utf8_encode($line[1]);
				$count=substr_count($line[1], "/");
				if( strrpos($line[1], "/") === false ) $Path=$line[1]; else $Path=substr($line[1], strrpos($line[1], "/")+1);
				for($i=1; $i<$count; $i++) $Path="&nbsp;&nbsp;".$Path;
				$browsenodes[]="(".$_POST["id_marketplace"].", 1, ".$line[0].", '".mysqli_real_escape_string($dbshop, $line[1])."', '".mysqli_real_escape_string($dbshop, $Path)."')";
			}
		}
		fclose($handle);
		
		
		//build query;
		$query="INSERT INTO amazon_browsenodes (marketplace_id, Lang_Id, BrowseNodeId, Path, Category) VALUES ".implode(", ", $browsenodes).";";
		
		q($query, $dbshop, __FILE__, __LINE__);
		
		echo 'Kategorien erfolgreich  importiert.';
		exit;
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
	$results=q("SELECT * FROM amazon_marketplaces;", $dbshop, __FILE__, __LINE__);
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