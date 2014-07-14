<?php

	/*************************
	********** SOA 2 *********
	***** Author Sven E. *****
	*** Lastmod 26.06.2014  chaendler***
	*************************/

	define("TBL_CMS_SITES_LANGUAGES", "cms_sites_languages");	// dev
	//define("TBL_CMS_SITES_LANGUAGES", "cms_sites_languages");	// prod

	check_man_params(array("site_id" => "site_id"));
	
	$xml = '';
	$languages = array();
	
	$results=q("SELECT language_id, fallback_language_id, ordering FROM ".TBL_CMS_SITES_LANGUAGES." WHERE site_id=".$_POST['site_id']." ORDER BY `ordering` ASC;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$languages[$row['ordering']]['id'] = $row['language_id'];
		$languages[$row['ordering']]['fallback'] = $row['fallback_language_id'];
		$languages[$row['ordering']]['ordering'] = $row['ordering'];
	}
	
	foreach ( $languages as $order => $language )
	{
		$xml .= '<language>'."\n";
		$xml .= '	<language_id>'.$language['id'].'</language_id>'."\n";
		$xml .= '	<fallback_language_id>'.$language['fallback'].'</fallback_language_id>'."\n";
		$xml .= ' 	<ordering>'.$language['ordering'].'</ordering>\n';
		$xml .= '</language>'."\n";
	} 
	
	print $xml;
?>