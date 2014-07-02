<?php

 //  	include("config.php");

	$zahl=0;
	echo eregi("a", "abcde");
	
/*
	$results=q("SELECT * FROM shop_items_files;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$results2=q("SELECT * FROM cms_files WHERE id_file=".$row["file_id"].";", $dbweb, __FILE__, __LINE__);
		if (mysqli_num_rows($results2) == 0)
		{
			$zahl++;
			$row2=mysqli_fetch_array($results2);
			echo $row["id"].' / '.$zahl.'<br />';
			$results3=q("DELETE FROM shop_items_files WHERE id=".$row["id"].";", $dbshop, __FILE__, __LINE__);
		}
	}
*/

?>