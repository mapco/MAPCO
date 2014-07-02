<?php
	
	//GARTs to XML
	$i=0;
	$gart=array();
	$gart_name=array();
	$results=q("SELECT GART FROM shop_items GROUP BY GART ORDER BY GART;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
/*
		$results2=q("SELECT * FROM shop_items_keywords WHERE GART=".$row["GART"]." AND language_id='en' ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)==0 )
		{
			$results2=q("SELECT * FROM shop_items_keywords WHERE GART=".$row["GART"]." AND language_id='de' ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			$gart[$i]=$row["GART"];
			$gart_name[$i]=$row2["keyword"];
			$i++;
		}
		else
		{
			$row2=mysqli_fetch_array($results2);
			$gart[$i]=$row["GART"];
			$gart_name[$i]=$row2["keyword"];
			$i++;
		}
*/
		$results2=q("SELECT BezNr FROM t_320 WHERE GenArtNr=".$row["GART"].";", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results2)>0 )
		{
			$row2=mysqli_fetch_array($results2);
			$query="SELECT Bez FROM t_030 WHERE SprachNr='001' AND BezNr=".$row2["BezNr"].";";
			$results2=q($query, $dbshop, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			$gart[$i]=$row["GART"];
			$gart_name[$i]=$row2["Bez"];
			$i++;
		}
	}

	array_multisort($gart_name, $gart);
	echo '<?xml version="1.0" encoding="utf-8"?>';
	echo '<GenericArticleNumbers>';
	for($i=0; $i<sizeof($gart); $i++)
	{
		echo '	<GART Name="'.$gart_name[$i].'">'.$gart[$i].'</GART>';
//		echo '	<GART Name="'.utf8_encode(htmlentities(utf8_decode($gart_name[$i]))).'">'.$gart[$i].'</GART>';
	}
	echo '</GenericArticleNumbers>';

?>