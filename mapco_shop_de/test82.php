<?php
	/***********************************************
	 * get all brake discs that have no /2 pendant *
	 ***********************************************/
	include("config.php");

	$items=array();
	$results=q("SELECT * FROM shop_items WHERE menuitem_id=76;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$items[]=$row["MPN"];
	}
	sort($items);
	
	$last="";
	for($i=0; $i<sizeof($items); $i++)
	{
		if ($last!=substr($items[$i], 0, 5) and $items[$i]!=substr($items[$i+1], 0, 5))
		{
			$needed[]=$items[$i];
		}
		$last=$items[$i];
//		echo $items[$i]."<br />";
	}

	for($i=0; $i<sizeof($needed); $i++)
	{
		echo $needed[$i]."<br />";
	}

	
?>