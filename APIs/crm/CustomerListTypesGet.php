<?php

	/*************************
	********** SOA 2 *********
	*************************/
	
	$xml = '';

	$res=q("SELECT id_type, title FROM crm_customer_list_types", $dbweb, __FILE__, __LINE__);
	while($row = mysqli_fetch_assoc($res))
	{
		$xml .= '<list_type>'."\n";
		$xml .= '	<type_id>'.$row['id_type'].'</type_id>'."\n";
		$xml .= '	<title><![CDATA['.$row['title'].']]></title>'."\n";
		if ( $row['id_type'] == 1 )
		{
			$res2=q("SELECT COUNT(id_list) AS listcount FROM crm_costumer_lists WHERE type=" . $row['id_type'] . " AND firstmod_user=" . $_SESSION['id_user'], $dbweb, __FILE__, __LINE__);
		}
		else
		{
			$res2=q("SELECT COUNT(id_list) AS listcount FROM crm_costumer_lists WHERE type=" . $row['id_type'], $dbweb, __FILE__, __LINE__);
		}
		$row2 = mysqli_fetch_assoc( $res2 );
//		$listcount = ($row['listcount'] != '') ? $row['listcount'] : 0;
//		$listcount = $row2['listcount'];
		$xml .= '	<listcount>' . $row2['listcount'] . '</listcount>'."\n";
		$xml .= '</list_type>'."\n";
	}
	
	if ( $_SESSION['userrole_id'] == 1 )
	{
		$xml .= '<list_type>'."\n";
		$xml .= '	<type_id>0</type_id>'."\n";
		$xml .= '	<title>Alle</title>'."\n";
		$res2=q("SELECT COUNT(id_list) AS listcount FROM crm_costumer_lists;", $dbweb, __FILE__, __LINE__);
		$row2 = mysqli_fetch_assoc($res2);
		$xml .= '	<listcount>'.$row2['listcount'].'</listcount>'."\n";
		$xml .= '</list_type>'."\n";
	}
	print $xml;
?>