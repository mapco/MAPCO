<?php

	//EBAY AUCTIONS
	$i=0;
	$results=q("SELECT ItemID FROM ebay_auctions WHERE account_id=".$_POST["id_account"]." AND shopitem_id=".$_POST["id_item"]." ORDER BY firstmod;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$i++;
		if ($i>1) echo ', ';
		echo '<a href="http://www.ebay.de/itm/'.$row["ItemID"].'" target="_blank">'.$row["ItemID"].'</a>';
	}

?>