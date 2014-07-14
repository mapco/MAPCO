<?php
	/*************************
	********** SOA 2 *********
	******Author Sven E.******
	****Lastmod 25.03.2014****
	*************************/
	
	$required=array("filter_id"	=> "numeric", "filter_text"	=> "text", "filter_require"	=> "text");
	check_man_params($required);
	
	$data = array();
	$data['filter_text'] = $_POST['filter_text'];
	$data['filter_require'] = $_POST["filter_require"];
//	$data['lastmod'] = time();
//	$data['lastmod_user'] = $_SESSION["id_user"];

	if ( $_POST["filter_id"] == 0 )
	{	
		$required=array("account_id"	=> "numericNN");
		check_man_params($required);
		
		$data['account_id'] = $_POST['account_id'];
//		$data['firstmod'] = $data['lastmod'];
//		$data['firstmod_user'] = $_SESSION["id_user"];

		q_insert('cms_mail_accounts_filters', $data, $dbweb, __FILE__, __LINE__);
	}
	else
	{
		$where = 'WHERE `id_mail_filter`= '.$_POST["filter_id"];
		
		q_update('cms_mail_accounts_filters', $data, $where, $dbweb, __FILE__, __LINE__);
		$affected_rows = mysql_affected_rows($dbweb);
		if( $affected_rows>0 && $affected_rows<2 )
		{
			$xml .= '<Error>Filter erfolgreich gespeichert</Error>'."\n";
		}
		elseif($affected_rows>1)
		{
			$xml .= '<Error>Es wurde mehr als ein Filter geändert!</Error>'."\n";
		}
	}
	
	print $xml;
?>