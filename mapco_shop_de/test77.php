<?php

	include("config.php");
    
    $results=q("SELECT * FROM mapco_gart_export ORDER BY GART;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		q("INSERT INTO ebay_categories (GART, account_id, StoreCategory, StoreCategory2) VALUES(".$row["GART"].", 1, ".$row["StoreCategory"].", ".$row["StoreCategory2"].");", $dbshop, __FILE__, __LINE__);
		q("INSERT INTO ebay_categories (GART, account_id, StoreCategory, StoreCategory2) VALUES(".$row["GART"].", 2, ".$row["StoreCategoryAP"].", ".$row["StoreCategoryAP2"].");", $dbshop, __FILE__, __LINE__);
	}

?>