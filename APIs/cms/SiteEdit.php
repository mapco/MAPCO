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

	$where = "WHERE `id_site`='".$_POST['id_site']."' LIMIT 1";
	
	$data['title'] = $_POST['title'];
	$data['description'] = $_POST['description'];
	$data['domain'] = $_POST['domain'];
	$data['template'] = $_POST['template'];
	$data['google_analytics'] = $_POST['google_analytics'];

	q_update('cms_sites', $data, $where, $dbweb, __FILE__, __LINE__);

?>