<?php 

	$checks = 0;
	$entries = 0;


	// Prüfe ob User und Order bereits in crm_conversations vorhanden
	$res_convers = q("SELECT id, article_id, user_id FROM crm_conversations WHERE type_id=4;", $dbweb, __FILE__, __LINE__);
	while ( $row_convers = mysqli_fetch_assoc($res_convers) )
	{   $checks++; 
			$results=q("SELECT site_id FROM cms_users_sites WHERE user_id=".$row_convers["user_id"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
	$cms_users_sites=mysqli_fetch_array($results);
			$cms_users_sites = (mysqli_fetch_assoc($results));
	
			$where = 'WHERE id_article='.$row_convers['article_id'];
			
			if ( $cms_users_sites["site_id"] != '' || $cms_users_sites["site_id"] != 0 )
			{
				$data['site_id'] = $cms_users_sites["site_id"];
				q_update('cms_articles', $data, $where, $dbweb, __FILE__, __LINE__);
				$entries++;
			}
	}

	print 'Anzahl Checks'. $checks."\n";
	print 'Anzahl Eintragungen'. $entries."\n";
?>