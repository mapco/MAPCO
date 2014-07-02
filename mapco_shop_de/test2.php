<?php

   	include("config.php");
	
	
	$results=q("SELECT * FROM shop_items_files;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$file[$row["file_id"]]=$row["file_id"];
	}
	
	$results3=q("SELECT * FROM cms_files_labels WHERE label_id=8;", $dbweb, __FILE__, __LINE__);
	while($row3=mysqli_fetch_array($results3))
	{	
		$labels[$row3["file_id"]]=$row3["file_id"];		
	}

	
	$results=q("SELECT * FROM cms_files WHERE original_id = 0 AND firstmod> 1321520400;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{	
		$results2=q("SELECT * FROM cms_files WHERE original_id = ".$row["id_file"].";", $dbweb, __FILE__, __LINE__);
		while($row2=mysqli_fetch_array($results2))
		{	
			if (!isset($file[$row2["id_file"]]) and !isset($labels[$row2["id_file"]]))
			{
//				echo $row2["id_file"].' / '.$row2["filename"].'<br />';
				$name = substr($row2["filename"], strlen($row2["filename"])-1, 1);
				if (!is_numeric($name) and $name!="S")
				{
					echo $artnr = substr($row2["filename"], 0, strlen($row2["filename"])-1);
					echo '<br />';
				}
				else
				{
					echo $artnr = $row2["filename"];
					echo '<br />';
				}
				$artnr = str_replace("_", "/", $artnr);
				$results4=q("SELECT * FROM shop_items WHERE MPN = '".$artnr."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
				$row4=mysqli_fetch_array($results4);
				q("INSERT INTO shop_items_files (item_id, file_id) VALUES (".$row4["id_item"].", ".$row2["id_file"].");", $dbshop, __FILE__, __LINE__);

			}
		}
	}
	
?>