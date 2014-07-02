<?php
	//SET ORDER EVENT "SHIPMENT"
	$responseXML=post(PATH."soa/", array("API" => "crm_test", "Action" => "set_orderEvents", "event" => "Shipment", "order_id" => $_POST["id_order"]));
	try
	{
		$xml = new SimpleXMLElement($responseXML);
		if ($xml->Ack[0]!="Success")
		{
			error_logs(__FILE__, __LINE__, $response);
		}

	}
	catch(Exception $e)
	{
		error_logs(__FILE__, __LINE__, $e->getMessage());
	}  


?>