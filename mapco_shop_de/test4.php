<?php
	include("config.php");
	
	q("UPDATE shop_items SET collateral=0;", $dbshop, __FILE__, __LINE__);

	$count=0;
	$results=q("SELECT * FROM t_200 WHERE ATWERT>0;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		q("UPDATE shop_items SET collateral=".$row["ATWERT"]." WHERE MPN=".$row["ArtNr"].";", $dbshop, __FILE__, __LINE__);		
		echo $row["ArtNr"].' / '.$row["ATWERT"].'<br />';
		$count++;
	}
	echo 'FERTIG! ('.$count.')';
?>