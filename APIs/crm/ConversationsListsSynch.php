<?php

	/*************************
	********** SOA 2 *********
	*************************/
	
	$res = q("SELECT user_id, order_id FROM crm_conversations_1396610499 ORDER BY id ASC;", $dbweb, __FILE__, __LINE__);
	while ( $row = mysqli_fetch_assoc($res) )
	{
		$res_list1 = q("SELECT id_conversation_list FROM crm_conversations_lists WHERE customer_id=".$row['user_id']." AND order_id=".$row['order_id'].";", $dbweb, __FILE__, __LINE__);
		if ( mysqli_num_rows($res_list1) == 0 )
		{
			$insert_data = array();
			$insert_data['customer_id'] = $row['user_id'];
			$insert_data['order_id'] = $row['order_id'];
			q_insert('crm_conversations_lists', $insert_data, $dbweb, __FILE__, __LINE__);

			$res_list = q("SELECT id_conversation_list FROM crm_conversations_lists WHERE customer_id=".$row['user_id']." AND order_id=".$row['order_id'].";", $dbweb, __FILE__, __LINE__);
			$row_list = mysqli_fetch_assoc($res_list);

			$update_data = array();
			$update_data['conversation_list_id'] = $row_list['id_conversation_list'];
			$where = 'WHERE user_id='.$row['user_id'].' AND order_id='.$row['order_id'];
			q_update('crm_conversations_1396610499', $update_data, $where, $dbweb, __FILE__, __LINE__);
		}
	}
?>