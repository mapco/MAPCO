<?php

	//SOA2 SERVICE
	
	//CHECK FOR REQUIRED POST FIELDAS
	$required=array("SELECTOR_id_return" =>"numericNN");
	check_man_params($required);
	
	//CHECK IF ENTRY EXISTS
	$res_check=q("SELECT * FROM shop_returns2 WHERE id_return = ".$_POST["SELECTOR_id_return"].";", $dbshop, __FILE__, __LINE__);
	
	if (mysqli_num_rows($res_check)==0)
	{
		show_error(9778, 7, __FILE__, __LINE__, "ReturnID: ".$_POST["SELECTOR_id_return"]);
		exit;
	}
	$returns=mysqli_fetch_array($res_check);
	
	//AUTOFILL
	if (!isset($_POST["lastmod"]) || $_POST["lastmod"] == 0 || $_POST["lastmod"] == "") $_POST["lastmod"] = time();
	if (!isset($_POST["lastmod_user"]) || $_POST["lastmod_user"] == 0 || $_POST["lastmod_user"] == "") $_POST["lastmod_user"] = $_SESSION["id_user"];
	
	//INSERT DATA
		//get TableStructure
	$data=array();
	$nvp="";
	$selector="";
	$res_struct=q("SHOW COLUMNS FROM shop_returns2;", $dbshop, __FILE__, __LINE__);
	while($struct=mysqli_fetch_assoc($res_struct))
	{
		if ($struct["Extra"]!="auto_increment")
		{
			if (isset($_POST[$struct["Field"]]))
			{
				//DATA FOR ORDEREVENT
				$data[$struct["Field"]]=$_POST[$struct["Field"]];
				
				if ($nvp!="") $nvp.=", ";
				$nvp.=$struct["Field"]." = '".mysqli_real_escape_string($dbshop, $_POST[$struct["Field"]])."'";
				
			}
			elseif(isset($_POST["SELECTOR_".$struct["Field"]]))
			{
				//DATA FOR ORDEREVENT
				$data["SELECTOR_".$struct["Field"]]=$_POST["SELECTOR_".$struct["Field"]];
				
				if ($selector!="") $selector.= " AND ";
				$selector.=$struct["Field"]." = '".$_POST["SELECTOR_".$struct["Field"]]."'";
			}
		}
		elseif(isset($_POST["SELECTOR_".$struct["Field"]]))
		{
			//DATA FOR ORDEREVENT
			$data["SELECTOR_".$struct["Field"]]=$_POST["SELECTOR_".$struct["Field"]];

			if ($selector!="") $selector.= " AND ";
			$selector.=$struct["Field"]." = '".$_POST["SELECTOR_".$struct["Field"]]."'";
		}
	
	}

	//CHECK IF THERE IS ANYTHING TO UPDATE
	if ($nvp!="" && $selector!="")
	{
		$sql="UPDATE shop_returns2 SET ".$nvp." WHERE ".$selector.";";

		$res_update=q($sql,$dbshop, __FILE__, __LINE__);
		$affected_rows=mysqli_affected_rows();

		//SET ORDEREVENT
			//CREATE XML FROM DATA
		$xml='<data>';
		foreach ($data as $key => $val)
		{
			$xml.='<'.$key.'>';
			if (!is_numeric($val)) $xml.='<![CDATA['.$val.']]>'; else $xml.=$val;
			$xml.='</'.$key.'>';
			
		}
		$xml.='</data>';

			//SAVE EVENT
		$responseXml = post(PATH."soa2/", array("API" => "shop", "APIRequest" => "OrderEventSet", "order_id" => $returns["order_id"], "eventtype_id" => 26, "data" => $xml));
			
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXml);
		}
		catch(Exception $e)
		{
			show_error(9756, 7, __FILE__, __LINE__, $responseXml);
			exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		if( $response->Ack[0]!="Success")
		{
			show_error(9777, 7, __FILE__, __LINE__, $responseXml);
			exit;
		}
		
		$id_event = $response -> id_event[0];

		//SERVICE RESPONSE		
		echo '	<affected_rows>'.$affected_rows.'</affected_rows>'."\n";
		echo '	<id_event>'.$id_event.'</id_event>'."\n";

	}
	else
	{
		//SERVICE RESPONSE		
		echo '	<affected_rows>0</affected_rows>'."\n";
		echo '	<id_event>0</id_event>'."\n";
	}


?>