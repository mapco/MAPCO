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

	check_man_params(array("mode" => "text"));

	
	if ($_POST["mode"]=="new")
	{
		$required=array("order_id" =>"numericNN", 
						"item_id" =>"numeric", 
						"amount" => "numeric", 
						"price" =>"numeric", 
						"netto" => "numeric", 
						"Currency_Code" => "currency", 
						"exchange_rate_to_EUR" => "numeric");

		check_man_params($required);					
	}
	
	if ($_POST["mode"]=="copy")
	{
		$required=array("id" =>"numericNN"); 
		check_man_params($required);					
	}


	if ($_POST["mode"]=="new")
	{
		//INSERT DATA
			//get TableStructure
		$data=array();
		$fields="";
		$values="";
		$res_struct=q("SHOW COLUMNS FROM shop_orders_items_test;", $dbshop, __FILE__, __LINE__);
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
		
		$sql="INSERT INTO shop_orders_items_test (".$fields.") VALUES (".$values.");";
		
		$res_ins=q($sql,$dbshop, __FILE__, __LINE__);
		$id_orderitem=mysqli_insert_id($dbshop);
		
		$data["id"]=$id_orderitem;
		
		//SET ORDEREVENT
		$id_event=save_order_event(2, $_POST["order_id"], $data);

		
		//SERVICE RESPONSE
		echo '	<id_orderitem>'.$id_orderitem.'</id_orderitem>'."\n";
		echo '	<id_event>'.$id_event.'</id_event>'."\n";

	}
	
	if ($_POST["mode"]=="copy")
	{
		//GET DATA
		$res_data=q("SELECT * FROM shop_orders_items_test WHERE id = ".$_POST["id"].";", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_data)==0)
		{
			echo '<OrderItemAddResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Zu kopierendes OrderItem nicht gefunden</shortMsg>'."\n";
			echo '		<longMsg>Kein OrderItem zur ID '.$_POST["id"].' gefunden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</OrderItemAddResponse>'."\n";
			exit;
		}
		$itemolddata=mysqli_fetch_array($res_data);
		
		//INSERT DATA
			//get TableStructure
		$data=array();
		$fields="";
		$values="";
		$res_struct=q("SHOW COLUMNS FROM shop_orders_items_test;", $dbshop, __FILE__, __LINE__);
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
				// ELSE USE DATA FROM COPY
				else	
				{
					//DATA FOR ORDEREVENT
					$data[$struct["Field"]]=$_POST[$struct["Field"]];

					if ($fields!="") $fields.=", ";
					$fields.=$struct["Field"];
					
					if ($values!="") $values.=", ";
					$values.="'".mysqli_real_escape_string($dbshop, $itemolddata[$struct["Field"]])."'";
				}
			}
		
		}
		
		$sql="INSERT INTO shop_orders_items_test (".$fields.") VALUES (".$values.");";
		
		$res_ins=q($sql,$dbshop, __FILE__, __LINE__);
		$id_orderitem=mysqli_insert_id($dbshop);

		$data["id"]=$id_orderitem;

		//SET ORDEREVENT
		$id_event=save_order_event(2, $itemolddata["order_id"], $data);

		//SERVICE RESPONSE
		echo '	<id_orderItem>'.$id_orderitem.'</id_orderItem>'."\n";
		echo '	<id_event>'.$id_event.'</id_event>'."\n";
	}

	
?>