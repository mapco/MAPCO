<?php
	/*****************************************************************
	 * set gross weight for items who have net weight and dimensions *
	 *****************************************************************/
	include("config.php");
	
	$i=0;
	$results=q("SELECT * FROM shop_items WHERE PackageLength>0 AND PackageHeight>0 AND PackageWidth>0 AND ItemWeight>0;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$i++;
		$PackageLength=$row["PackageLength"]/10;
		$PackageHeight=$row["PackageHeight"]/10;
		$PackageWidth=$row["PackageWidth"]/10;
		$GrossWeight=0.0000194*$PackageLength*$PackageHeight*$PackageWidth*1000;
		echo $GrossWeight.'<br />';
		$GrossWeight=round($GrossWeight)+$row["ItemWeight"];
		echo $GrossWeight.'<br />';
		echo $i." ".$GrossWeight." ".$row["MPN"]."<br />";
		q("UPDATE shop_items SET GrossWeight='".$GrossWeight."' WHERE id_item=".$row["id_item"].";", $dbshop, __FILE__, __LINE__);
	}
	
?>