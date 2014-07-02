<?php
	/*************************
	********** SOA 2 *********
	***** Author Sven E. *****
	*** Lastmod 28.03.2014 ***
	*************************/

	$required = array(
		"id_site" => "numericNN",
		"title" => "text",
		"description" => "text",
		"domain" => "text",
		"template" => "text",
		"google_analytics" => "text",
	);
	
	check_man_params($required);

	$where = "id_site=".$_POST['id_site'];
	$data['title'] = $_POST['title'];
	$data['description'] = $_POST['description'];
	$data['domain'] = $_POST['domain'];
	$data['template'] = $_POST['template'];
	$data['google_analytics'] = $_POST['google_analytics'];

	$xml = '';
	
	q_update('cms_sites', $data, $where, $dbweb, __FILE__, __LINE__);

	print $xml;
?>