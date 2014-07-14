<?php 

	//************************ 
	//*     SOA2-SERVICE     *
	//************************

	$res_acc = q("SELECT get_filtered FROM `cms_mail_accounts` WHERE `id_account`=".$_POST['account']." LIMIT 1;", $dbweb, __FILE__, __LINE__);
	$row_acc = mysqli_fetch_assoc($res_acc);
	
	if ( $row_acc['get_filtered'] == 0 )
	{	
		$where = 'WHERE `id_account`='.$_POST['account'];
		$update_data = array();	
		$update_data['get_filtered'] = 1;
		q_update('cms_mail_accounts', $update_data, $where, $dbweb, __FILE__, __LINE__);
		
		require_once("../../mapco_shop_de/functions/mail_connect.php");
		$mbox = mail_connect($_POST['account'], $_POST['folder']);
	
		$res_filters = q("SELECT `filter_text` FROM `cms_mail_accounts_filters` WHERE `filter_require`= '' AND `account_id`=".$_POST['account'].";", $dbweb, __FILE__, __LINE__);
		
		$search_result = array();
		while ( $row_filters = mysqli_fetch_assoc($res_filters) )
		{ 	
			$tmp_search_result = imap_search($mbox, 'SUBJECT "'.$row_filters['filter_text'].'"', SE_UID);
	
			if ( $tmp_search_result !== FALSE && sizeof($search_result) > 0 )
			{	
				$search_result = array_merge($tmp_search_result,$search_result);
			}
			elseif ( $tmp_search_result !== FALSE )
			{
				$search_result = $tmp_search_result;
			}
		}

		foreach($search_result as $result)
		{
			move_mail_to_archiv($mbox, $result, $_POST['account'], $_POST['folder']);
		}
		
		$where = 'WHERE `id_account`='.$_POST['account'];
		$update_data = array();	
		$update_data['get_filtered'] = 0;
		q_update('cms_mail_accounts', $update_data, $where, $dbweb, __FILE__, __LINE__);
	}
	
	print "<get_filtered>".$row_acc['get_filtered']."</get_filtered>\n";
?>