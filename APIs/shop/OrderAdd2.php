<?php

	function save_order_event($eventtype_id, $order_id, $data)
	{
		global $dbshop;
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
		q("INSERT INTO shop_orders_events_test (
			order_id, 
			eventtype_id, 
			data, 
			firstmod, 
			firstmod_user
		) VALUES (
			".$order_id.",
			".$eventtype_id.",
			'".mysqli_real_escape_string($dbshop, $xml)."',
			".time().",
			".$_SESSION["id_user"]."
		);", $dbshop, __FILE__, __LINE__);
		
		return mysqli_insert_id($dbshop);
		
	}



	//CHECK FOR MODE
	check_man_params(array("mode" => "text"));

	if ($_POST["mode"]=="new")
	{
		/*
		INSERT NEW ORDER
		- benötigt die in $required definierten Felder
		- zusätzlich in $_POST übergebene Werte werden ebenfalls in die Tabelle geschrieben (bei Übereinstimmung von Feldnamen)
		- AUTOFILL	- firstmod
					- firstmoduser
					- lastmod
					- lastmoduser
		*/

		$required=array("shop_id" =>"numericNN", 
						"status_id" =>"numericNN", 
						"Currency_Code" => "currency", 
						"customer_id" =>"numericNN", 
						"usermail" => "text", 
						"bill_lastname" => "text", 
						"bill_zip" => "text", 
						"bill_city" => "text", 
						"bill_street" => "text", 
						"bill_number" => "text", 
						"shipping_costs" =>"numeric", 
						"shipping_type_id" =>"numeric", 
						"bill_adr_id" =>"numeric", 
						"shipping_net" =>"numeric");
	
		check_man_params($required);					
		
		//AUTOFILL
		if (!isset($_POST["firstmod"])) $_POST["firstmod"]=time();
		if (!isset($_POST["firstmod_user"])) $_POST["firstmod_user"]=$_SESSION["id_user"];
		if (!isset($_POST["lastmod"])) $_POST["lastmod"]=time();
		if (!isset($_POST["lastmod_user"])) $_POST["lastmod_user"]=$_SESSION["id_user"];

	}

	if ($_POST["mode"]=="copy")
	{
		/*
		INSERT NEW ORDER AS COPY
		- benötigt shop_orders.id_order (SOURCE)
		- schreibt die SOURCE-Werte in die neue Order, wenn die Felder nicht per $_POST übergeben werden
		*/ 
		
		$required=array("id_order" =>"numericNN"); 
		check_man_params($required);

	}

	if ($_POST["mode"]=="manual")
	{
		/*
		schreibt Order, wie Felder übergeben
		- AUTOFILL	- firstmod
					- firstmoduser
					- lastmod
					- lastmoduser
		*/
		//AUTOFILL
		if (!isset($_POST["firstmod"])) $_POST["firstmod"]=time();
		if (!isset($_POST["firstmod_user"])) $_POST["firstmod_user"]=$_SESSION["id_user"];
		if (!isset($_POST["lastmod"])) $_POST["lastmod"]=time();
		if (!isset($_POST["lastmod_user"])) $_POST["lastmod_user"]=$_SESSION["id_user"];

	}

			
	if ($_POST["mode"]=="new" || $_POST["mode"]=="manual")
	{
		
		//INSERT DATA
			//get TableStructure
		$fields="";
		$values="";
		
		$data=array(); // DATAFIELD FOR ORDEREVENT
		
		$res_struct=q("SHOW COLUMNS FROM shop_orders_test;", $dbshop, __FILE__, __LINE__);
		while($struct=mysqli_fetch_assoc($res_struct))
		{
			if ($struct["Extra"]!="auto_increment")
			{
				if (isset($_POST[$struct["Field"]]))
				{
					//DATA FOR ORDEREVENT
					$data[$struct["Field"]]=$_POST[$struct["Field"]];
					
					if ($fields!="") $fields.=", ";
					$fields.=$struct["Field"];
					
					if ($values!="") $values.=", ";
					$values.="'".mysqli_real_escape_string($dbshop, $_POST[$struct["Field"]])."'";
				}
			}
		
		}
		
		$sql="INSERT INTO shop_orders_test (".$fields.") VALUES (".$values.");";
		
		$res_ins=q($sql,$dbshop, __FILE__, __LINE__);
		$id_order=mysqli_insert_id($dbshop);
		
		$data["id_order"]=$id_order;
		
		//SET ORDEREVENT
		$id_event=save_order_event(1, $id_order, $data);
		
		
		//SERVICE RESPONSE
		echo '	<id_order>'.$id_order.'</id_order>'."\n";
		echo '	<id_event>'.$id_event.'</id_event>'."\n";
		
	}
	
	if ($_POST["mode"]=="copy")
	{
		//GET DATA
		$res_data=q("SELECT * FROM shop_orders_test WHERE id_order = ".$_POST["id_order"].";", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_data)==0)
		{
			echo '<OrderAddResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Zu kopierenden Order nicht gefunden</shortMsg>'."\n";
			echo '		<longMsg>Keine Order zur ID '.$_POST["id_order"].' gefunden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</OrderAddResponse>'."\n";
			exit;
		}
		$oldorder_data=mysqli_fetch_array($res_data);
		
		//INSERT DATA
			//get TableStructure
		$data=array();
		$fields="";
		$values="";
		$res_struct=q("SHOW COLUMNS FROM shop_orders_test;", $dbshop, __FILE__, __LINE__);
		while($struct=mysqli_fetch_assoc($res_struct))
		{
			if ($struct["Extra"]!="auto_increment")
			{
				// IF SET POST DATA 
				if (isset($_POST[$struct["Field"]]))
				{
					//DATA FOR ORDEREVENT
					$data[$struct["Field"]]=$_POST[$struct["Field"]];

					if ($fields!="") $fields.=", ";
					$fields.=$struct["Field"];
					
					if ($values!="") $values.=", ";
					$values.="'".mysqli_real_escape_string($dbshop, $_POST[$struct["Field"]])."'";
				}
				// ELSE USE DATA FROM SOURCE
				else	
				{
					if ($fields!="") $fields.=", ";
					$fields.=$struct["Field"];
					
					if ($values!="") $values.=", ";
					$values.="'".mysqli_real_escape_string($dbshop, $oldorder_data[$struct["Field"]])."'";
				}
			}
		
		}
		
		$sql="INSERT INTO shop_orders_test (".$fields.") VALUES (".$values.");";
		
		$res_ins=q($sql,$dbshop, __FILE__, __LINE__);
		$id_order=mysqli_insert_id($dbshop);

		$data["id_order"]=$id_order;
		
		$id_event=save_order_event(1, $id_order, $data);
		//SERVICE RESPONSE
		echo '	<id_order>'.$id_order.'</id_order>'."\n";
		echo '	<id_event>'.$id_event.'</id_event>'."\n";
	}

	
?>