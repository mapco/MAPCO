<?php

	/*************************
	********** SOA 2 *********
	***** Author Sven E. *****
	*** Firstmod 09.04.2014 ***
	*** Lastmod 09.04.2014 ***
	*************************/
		
	check_man_params(array("site_id" => "numeric"));
	
	$xml = '';
	$languages = explode(',',$_POST['languages']);

	q("DELETE FROM cms_site_languages WHERE site_id=".$_POST['site_id'].";", $dbweb, __FILE__, __LINE__);
	
	$data = array();
	for ( $x=0; $x<sizeof($languages); $x++ )
	{
		$data['site_id'] = $_POST['site_id'];
		$data['language_id'] = $languages[$x];
		$data['fallback_language_id'] = $_POST['fallback_language_id'];
		$data['firstmod'] = time();
		$data['firstmod_user'] = $_SESSION['id_user'];
		$data['lastmod'] = time();
		$data['lastmod_user'] = $_SESSION['id_user'];
		q_insert('cms_site_languages', $data, $dbweb, __FILE__, __LINE__);
	}

?>