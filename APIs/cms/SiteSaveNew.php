<?php
	/**************************************************
	*** Service: cms.SiteSaveNew					***
	*** Author: C.Haendler <chaendler(at)mapco.de> 	*** 
	***	Version: 1.1 (SOA2) 30/06/14/ 				***
	***	Last mod: 01/07/14							***
	***************************************************
	*** - creates a new site 						***
	*** - creates a new site language				***
	*** - creates a new template folder				***  
	***************************************************
	*** Requires: 									***
	***				Mapco.Filesystem.FileOp			***
	***************************************************/
	
	define('TBL_CMS_SITES', "cms_sites");
	define('TBL_CMS_SITES_LANGUAGES', "cms_sites_languages");
	define('SITE_EDITOR_DEFAULT_TEMPLATE', "default");			// default template folder to dublicate
		
	check_man_params
	(
		array
		(
			"title" => "text",
			"description" => "text",
			"domain" => "text",
			"template" => "text",
			"location_id" => "numericNN",
			"language_id" => "numericNN"
		)
	);

	$data = array();
	$data['title'] = $_POST['title'];
	$data['description'] = $_POST['description'];
	$data['domain'] = $_POST['domain'];
	$data['template'] = $_POST['template'];
	$data['location_id'] = $_POST['location_id'];
	$data['google_analytics'] = $_POST['google_analytics'];
	
	if ($_POST['ssl'] == true) 
	{
		$data['ssl'] = 1;
	} 
	else 
	{
		$data['ssl'] = 0;	
	}
	
	$results=q_insert(TBL_CMS_SITES, $data, $dbweb, __FILE__, __LINE__);
	$site_id=mysqli_insert_id($dbweb);	
	
	// insert primary language
	$langData['language_id'] = $_POST['language_id'];
	$langData['fallback_language_id'] = 0;
	$langData['ordering'] = 1;
	$langData['firstmod'] = time();
	$langData['firstmod_user'] = $_SESSION['id_user'];
	$langData['lastmod'] = time();
	$langData['lastmod_user'] = $_SESSION['id_user'];	
	$langData['site_id'] = $site_id;
	$lang_results=q_insert(TBL_CMS_SITES_LANGUAGES, $langData, $dbweb, __FILE__, __LINE__);
	  
	if (!is_dir(M_PATH_TEMPLATES.DS.$data['template'])) 
	{
		if (is_dir(M_PATH_TEMPLATES.DS.SITE_EDITOR_DEFAULT_TEMPLATE)) 
		{
			i('Mapco.Filesystem.FileOp');
			MFileOp::rcopy(M_PATH_TEMPLATES.DS.SITE_EDITOR_DEFAULT_TEMPLATE, M_PATH_TEMPLATES.DS.$data['template']);
		}
	} 



	