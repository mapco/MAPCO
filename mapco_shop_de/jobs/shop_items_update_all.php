<?php
	include("../config.php");

	if($_GET["count"]>0)
	{
		$count=$_GET["count"];
		$anz = $_GET["anz"];
	}
	else 
	{
		$results=q("SELECT * FROM shop_items WHERE active=1;", $dbshop, __FILE__, __LINE__);
		$anz = (mysqli_num_rows($results)/200);
		$count=0;
	}
	$results=q("SELECT * FROM shop_items WHERE active=1 AND lastmod<".(time()-1200)." ORDER BY lastmod LIMIT 200;", $dbshop, __FILE__, __LINE__);
	if($count<$anz)
	{
		while( $row=mysqli_fetch_array($results) )
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "http://www.mapco.de/jobs/update_artnr.php?id_item=".$row["id_item"]."&auto=1");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$data = curl_exec($ch);
			curl_close($ch);		
//			echo $data;
		}
		$count++;
		sleep(5);
		header ("Location: http://www.mapco.de/jobs/shop_items_update_all.php?count=".$count."&anz=".$anz); 
		exit;

	}
	else 
	{
		echo 'Update beendet!';
		exit;
	}

?>



</body>
</html>
