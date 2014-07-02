<?php

	/*************************
	********** SOA 2 *********
	***** Author Sven E. *****
	*** Lastmod 28.03.2014 ***
	*************************/
		
	$results=q("SELECT id_language, language FROM cms_languages;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$languages[$row['id_language']] = $row['language'];
	}
		
	$results=q("SELECT id_site, title, description, domain, template, google_analytics FROM cms_sites;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$sites[$row['id_site']]['site_title'] = $row['title'];
		$sites[$row['id_site']]['description'] = $row['description'];
		$sites[$row['id_site']]['domain'] = $row['domain'];
		$sites[$row['id_site']]['template'] = $row['template'];
		$sites[$row['id_site']]['google_analytics'] = $row['google_analytics'];
		
		$site_ids[] = $row['id_site'];
	}
	
	$site_ids = implode(",", $site_ids);
	$results=q("SELECT site_id, language_id, fallback_language_id FROM cms_sites_languages WHERE site_id IN (".$site_ids.");", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$sites[$row['site_id']]['languages'][$row['language_id']]['language'] = $languages[$row['language_id']];
		
		if ( $row['fallback_language_id'] == 0 )
		{
			$sites[$row['site_id']]['languages'][$row['language_id']]['fallback_language'] = 'Keine';
		}
		else
		{
			$sites[$row['site_id']]['languages'][$row['language_id']]['fallback_language'] = $languages[$row['fallback_language_id']];	
		}
	}

	foreach($languages as $language_id => $language_title)
	{	
		echo '	<Languages>'."\n";
		echo '		<id><![CDATA['.$language_id.']]></id>'."\n";
		echo '		<title><![CDATA['.$language_title.']]></title>'."\n";
		echo '	</Languages>'."\n";
	}

	foreach($sites as $site_id => $site)
	{	
		echo '<Site>'."\n";
		echo '	<site_id><![CDATA['.$site_id.']]></site_id>'."\n";
		foreach($site as $key => $value)
		{
			if ($key == 'languages')
			{
				foreach($value as $language_id => $language)
				{	
					echo '	<language>'."\n";
					echo '		<title><![CDATA['.$language['language'].']]></title>'."\n";
					echo '		<fallback><![CDATA['.$language['fallback_language'].']]></fallback>'."\n";
					echo '	</language>'."\n";
				}
			}
			else
			{
				echo '	<'.$key.'><![CDATA['.$sites[$site_id][$key].']]></'.$key.'>'."\n";
			}
		}
		echo '</Site>'."\n";
	}
?>