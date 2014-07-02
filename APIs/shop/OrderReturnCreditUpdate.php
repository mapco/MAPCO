<?php

	//CHECK $_POST PARAMS
	check_man_params(array("return_id" => "numericNN"));

	//CHECK FOR EXISTING RETURN
	$res_return = q("SELECT * FROM shop_returns2 WHERE id_return = ".$_POST["return_id"], $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_return)==0)
	{
		//show_error();
		exit;	
	}

	if (isset($_POST["reason_id"]) && $_POST["reason_id"]==2)
	{	
		// CHECK FOR existing shipping_credit
			// IF EXISTS -> update ELSE add
		$res_credit = q("SELECT * FROM shop_returns_credits WHERE reason_id = 2 AND return_id = ".$_POST["return_id"], $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_credit)>0)
		{
			$row_credit = mysqli_fetch_assoc($res_credit);
			$_POST["return_credit_id"] = $row_credit["id_return_credit"];
		}
	}
	
	
	// CHECK FOR UPDATE OR ADD
	if (isset($_POST["return_credit_id"]) && $_POST["return_credit_id"] != 0)
	{
		$update = true;
	}
	else
	{
		$update = false;	
	}
	
	
	//UPDATE
	if ($update)
	{
		//AUTOFILL
		if (!isset($_POST["lastmod"])) $_POST["lastmod"]=time();
		if (!isset($_POST["lastmod_user"])) $_POST["lastmod_user"]=$_SESSION["id_user"];

		$update_field = array();

		$res_struct=q("SHOW COLUMNS FROM shop_returns_credits;", $dbshop, __FILE__, __LINE__);
		while($struct=mysqli_fetch_assoc($res_struct))
		{
			if ($struct["Extra"]!="auto_increment" && $struct["Field"]!="id_return_credit")
			{
				$update_field[$struct["Field"]] = $_POST[$struct["Field"]];
			}
		}

		q_update("shop_returns_credits", $update_field, "WHERE id_return_credit = ".$_POST["return_credit_id"], $dbshop, __FILE__, __LINE__);

	}
	//ADD
	else
	{
		check_man_params(array("gross" => "numeric", "net" => "numeric", "reason_id" => "numericNN"));
		
		//AUTOFILL
		if (!isset($_POST["firstmod"])) $_POST["firstmod"]=time();
		if (!isset($_POST["firstmod_user"])) $_POST["firstmod_user"]=$_SESSION["id_user"];
		if (!isset($_POST["lastmod"])) $_POST["lastmod"]=time();
		if (!isset($_POST["lastmod_user"])) $_POST["lastmod_user"]=$_SESSION["id_user"];

		$insert_field = array();

		$res_struct=q("SHOW COLUMNS FROM shop_returns_credits;", $dbshop, __FILE__, __LINE__);
		while($struct=mysqli_fetch_assoc($res_struct))
		{
			if ($struct["Extra"]!="auto_increment")
			{
				if (isset($_POST[$struct["Field"]]))
				{
					$insert_field[$struct["Field"]] = $_POST[$struct["Field"]];
					
				}
			}
		
		}
		
		q_insert("shop_returns_credits", $insert_field, $dbshop, __FILE__, __LINE__);

	}
?>