<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	if ( isset($_FILES["file"]) )
	{
		//cache garts
		$gart=array();
		$results=q("SELECT * FROM shop_items_duty_numbers;", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			$gart[$row["GART"]]=$row["duty_number"];
		}
		
		//cache shop_items
		$item=array();
		$results=q("SELECT id_item, GART, MPN, CommodityCode FROM shop_items;", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			$item[$row["MPN"]]=$row;
		}
		
		//update commodity codes
		$i=0;
		$vehicles=array();
		$handle=fopen($_FILES["file"]["tmp_name"], "r");
		while($line=fgetcsv($handle, 4096, ";"))
		{
//			if( isset($item[$line[0]]) and $item[$line[0]]["CommodityCode"]!=$line[2] )
			if( $line[2]!="" and isset($item[$line[0]]) )
			{
				$i++;
				q("UPDATE shop_items SET CommodityCode='".$line[2]."' WHERE id_item=".$item[$line[0]]["id_item"].";", $dbshop, __FILE__, __LINE__);
				if( !isset($gart[$item[$line[0]]["GART"]]) )
				{
					q("INSERT INTO shop_items_duty_numbers (GART, duty_number) VALUES('".$item[$line[0]]["GART"]."', '".$line[2]."');", $dbshop, __FILE__, __LINE__);
					$gart[$item[$line[0]]["GART"]]=$line[2];
				}
			}
		}
		
		echo '<div class="success">'.$i.' Zolltarifnummern erfolgreich importiert.</div>';
	}

	echo '<div style="position:relative;" id="progressbarWrapper" style="width:300px; height: 30px;" class="ui-widget-default">';
	echo '	<div id="progressbar"></div>';
	echo '	<div style="position:absolute; left:0px; top:5px; width:100%; height:25px; text-align:center;" id="progressText"></div>';
	echo '</div>';

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_admin_index.php">Administration</a>';
	echo ' > Zolltarifnummern';
	echo '</p>';

	echo '<h1>Zolltarifnummern importieren</h1>';
	echo '<form method="post" enctype="multipart/form-data">';
	echo '	<input type="file" name="file" />';
	echo '	<input type="submit" value="Hochladen" />';
	echo '</form>';

	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>