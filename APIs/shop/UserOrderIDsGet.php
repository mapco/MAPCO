<?php

	$required=array("user_id" => "numericNN");
	check_man_params($required);

	//GET ORDERS FROM USER
	$query = "SELECT id_order FROM shop_orders WHERE customer_id = ".$_POST["user_id"];
	
	if (isset($_POST["shop_id"])) 	
	{
		check_man_params(array("shop_id" => "numericNN"));
		
		$query.= " AND shop_id = ".$_POST["shop_id"];
	
	}

	$res=q($query, $dbshop, __FILE__, __LINE__);
	
	if (mysqli_num_rows($res)!=0)
	{
		while ($row = mysqli_fetch_assoc($res))
		{
			echo '	<order_id>'.$row["id_order"].'</order_id>';
		}
	}
		


?>