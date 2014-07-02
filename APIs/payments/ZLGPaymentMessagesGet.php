<?php

	$query="SELECT * FROM idims_zlg_log";


	$query.= " WHERE NOT shop_id = 6";

	if (isset($_POST["send_state"])) 
	{
		if ( $_POST["send_state"] == 1 )
		{
			$query.=" AND response_time = 0 AND NOT acknowledgment = 'Expired'";
		}
		elseif ( $_POST["send_state"] == 0 )
		{
			$query.=" AND NOT response_time = 0";
		}
		elseif ( $_POST["send_state"] == 2 )
		{
			$query.=" AND response_time = 0";
		}
		elseif ( $_POST["send_state"] == 3 )
		{
			$query.=" AND acknowledgment = 'Expired'";
		}

	}
	if (isset($_POST["acknowledgment"]) && $_POST["acknowledgment"]=="Success")
	{
		$query.=" AND acknowledgment = 'Success'";
	}
	if (isset($_POST["Ack"]) && $_POST["acknowledgment"]=="Error")
	{
		$query.=" AND acknowledgment = 'Error'";
	}
	if (isset($_POST["payment_type_id"]) && $_POST["payment_type_id"]!=0)
	{
		$query.=" AND payment_type_id = ".$_POST["payment_type_id"];
	}
	if (isset($_POST["shop_id"]) && $_POST["shop_id"]!=0)
	{
		$query.=" AND shop_id = ".$_POST["shop_id"];
	}

	if ( isset( $_POST["invoice_date_from"] ) && $_POST["invoice_date_from"] != "" && isset( $_POST["invoice_date_to"] ) && $_POST["invoice_date_to"] != "" )
	{
		$query.=" AND invoice_time > ".strtotime( $_POST["invoice_date_from"] )." AND invoice_time <  ".(strtotime( $_POST["invoice_date_to"] )+(24*3600)-1);
	}
 	 	
	$res = q($query, $dbshop, __FILE__, __LINE__);
	
	echo '<idims_zlg_logs>'."\n";
	
	while ($row = mysqli_fetch_assoc($res))
	{
		echo '	<idims_zlg_log>'."\n";
		while (list ($key, $val) = each ($row))
		{
			echo '		<'.$key.'><![CDATA['.$val.']]></'.$key.'>'."\n"; 
		}
		echo '	</idims_zlg_log>'."\n";
	}
	
	echo '</idims_zlg_logs>'."\n";

?>