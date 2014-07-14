<?php

	/*************************
	********** SOA 2 *********
	***** Author Christopher H. *****
	*** Firstmod 09.04.2014 Sven E. ***
	*** Lastmod 25.06.2014 ***
	*** version 0.01 ***
	*************************/

	/**
	 * Returns $_POST data by value
	 *
	 * @param $value
	 * @return mixed
	 */
	function getPost($value)
	{
		if (isset($_POST[$value]) && $_POST[$value] != null) {
			return $_POST[$value];
		}
		return false;
	} 

	//check_man_params(array("site_id" => "numeric"));
	$languages = getPost('languages');
	if (is_array($languages) && getPost('site_id') != false) 
	{
		// remove old language selection from db
		q("DELETE FROM `cms_sites_languages` WHERE `site_id`=".getPost('site_id')."", $dbweb, __FILE__, __LINE__);
		
		// add new language selection to db
		foreach ($languages as $order => $language) 
		{			
			$data['site_id'] = getPost('site_id');
			$data['language_id'] = $language['id'];
			$data['fallback_language_id'] = $language['fallback'];
			$data['ordering'] = $order;
			$data['firstmod'] = time();
			$data['firstmod_user'] = $_SESSION['id_user'];
			$data['lastmod'] = time();
			$data['lastmod_user'] = $_SESSION['id_user'];
			q_insert('cms_sites_languages', $data, $dbweb, __FILE__, __LINE__);	
		}	
	}


?>