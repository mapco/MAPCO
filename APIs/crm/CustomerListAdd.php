<?php

	/*
	*	SOA2-SERVICE
	*/
	
	$required = array( 'title' => 	'text',
					   'type' => 	'numeric' );
	check_man_params( $required );
	
	$data = 			array();
	$data['title'] = 	$_POST['title'];
	$data['type'] = 	$_POST['type'];
	if ( $_POST['type'] == 1 )
	{
		$data['private'] = 1;
	}
	elseif ( $_POST['type'] == 2 )
	{
		$data['private'] = 0;
	}
	$data['firstmod'] = 		time();
	$data['firstmod_user'] = 	$_SESSION['id_user'];
	$data['lastmod'] = 			time();
	$data['lastmod_user'] = 	$_SESSION['id_user'];
	
	q_insert( 'crm_costumer_lists', $data, $dbweb, __FILE__, __LINE__ );
	
	$res = q( "SELECT * FROM crm_costumer_lists ORDER BY id_list DESC", $dbweb, __FILE__, __LINE__ );
	$crm_costumer_lists = mysqli_fetch_assoc( $res );
	$list_id = $crm_costumer_lists['id_list'];
	
	$xml = '';
	$xml .= '<list_id>' . $list_id . '</list_id>' . "\n";
	$xml .= '<title>' . $_POST['title'] . '</title>' . "\n";
	$xml .= '<type>' . $_POST['type'] . '</type>' . "\n";
	
	echo $xml;

?>