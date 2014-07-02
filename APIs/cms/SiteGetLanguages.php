<?php

	/*************************
	********** SOA 2 *********
	***** Author Sven E. *****
	*** Lastmod 28.03.2014 ***
	*************************/
		
	check_man_params(array("site_id" => "site_id"));
	
	$xml = '';
	$languages = array();
	
	$results=q("SELECT language_id, fallback_language_id FROM cms_sites_languages WHERE site_id=".$_POST['site_id'].";", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$languages[$row['language_id']] = $row['fallback_language_id'];
	}
	
	foreach ( $languages as $id => $language )
	{
		$xml .= '<language>'."\n";
		$xml .= '	<language_id>'.$id.'</language_id>'."\n";
		$xml .= '	<fallback_language_id>'.$id.'</fallback_language_id>'."\n";
		$xml .= '</language>'."\n";
	}
	
	print $xml;
?>