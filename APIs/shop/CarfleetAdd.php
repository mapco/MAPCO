<?php

	check_man_params(array("id_vehicle" => 	"numeric",
						   "kbanr"		=>	"text"));
	
	$car_added=0;
						   
	//is car already in carfleet?
	$results=q("SELECT * FROM shop_carfleet WHERE user_id=".$_SESSION["id_user"]." AND vehicle_id=".$_POST["id_vehicle"]." AND active=1;", $dbshop, __FILE__, __LINE__);
	if(mysqli_num_rows($results)==0)
	{
		$results2=q("INSERT INTO shop_carfleet (user_id, shop_id, vehicle_id, kbanr, active, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$_SESSION["id_user"].", ".$_SESSION["id_shop"].", ".$_POST["id_vehicle"].", '".$_POST["kbanr"]."', 1, ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
		$car_added=1;
	}
	
	echo '<car_added>'.$car_added.'</car_added>';

?>