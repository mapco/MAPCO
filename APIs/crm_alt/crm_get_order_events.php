<?php

	if ( !isset($_POST["OrderID"]) )
	{
		echo '<crm_get_order_eventsResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>OrderID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine OrderID angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_get_order_eventsResponse>'."\n";
		exit;
	}

	//GET ORDERdata
	$res_events=q("SELECT * FROM shop_orders_events WHERE order_id = ".$_POST["OrderID"].";", $dbshop, __FILE__, __LINE__);
	$eventcount=0;
	$xmldata ='';
	
	if (mysql_affected_rows()>0)
	{
	
		$xmldata.='<OrderEvents>';
		
		while ($row_events=mysql_fetch_array($res_events))
		{
			$xmldata.='	<OrderEvent>';
			$xmldata.='		<EventID>'.$row_events["id_event"].'</EventID>';
			$xmldata.='		<Event><![CDATA['.$row_events["event"].']]></Event>';
			$xmldata.='		<Message>'.$row_events["message"].'</Message>';
			$xmldata.='	</OrderEvent>';
			
			$eventcount++;
		}
		$xmldata.='<EventCount>'.$eventcount.'</EventCount>';
		$xmldata.='</OrderEvents>';
	}
	
echo "<crm_get_order_eventsResponse>\n";
echo "<Ack>Success</Ack>\n";
	echo $xmldata;
echo "</crm_get_order_eventsResponse>";

?>