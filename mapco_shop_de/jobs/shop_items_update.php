<?php
	include("../config.php");
		
	if ($_GET["action"]=="reload")
	{
		echo '<script language="javascript">window.setTimeout("window.location.href=\'http://www.mapco.de/jobs/shop_items_update.php?action=reload\'", 300000); </script>';	
	}

	$results=q("SELECT * FROM shop_items WHERE active=1 AND lastmod<".(time()-1200)." ORDER BY lastmod LIMIT 250;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$ch = curl_init();
//		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept-language:de-de", "Accept: application/xml")); 
//		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept-language:de-de", "Accept: application/json")); 
		curl_setopt($ch, CURLOPT_URL, "http://www.mapco.de/jobs/update_artnr.php?id_item=".$row["id_item"]."&auto=1");
//		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//		curl_setopt($ch, CURLOPT_USERPWD, $username.":".$password);
		$data = curl_exec($ch);
		curl_close($ch);
		
		echo $data;
	}

?>