<?php

	include("config.php");
	
	$_POST["id_account"]=1;
	$results=q("SELECT * FROM mapco_gart_export;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$results2=q("SELECT * FROM ebay_categories WHERE GART=".$row["GART"]." AND account_id=".$_POST["id_account"].";", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results2)>0 )
		{
			$row2=mysqli_fetch_array($results2);
			q("	UPDATE ebay_categories
				SET CategoryID='".$row["Category"]."',
					CategoryID2='".$row["Category2"]."'
				WHERE id=".$row2["id"].";", $dbshop, __FILE__, __LINE__);
		}
		else
		{
			q("	INSERT INTO ebay_categories (GART, account_id, CategoryID, CategoryID2) VALUES(".$row["GART"].", ".$_POST["id_account"].", '".$row["Category"]."', '".$row["Category2"]."');", $dbshop, __FILE__, __LINE__);
		}
	}

?>