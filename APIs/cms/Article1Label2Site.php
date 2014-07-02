<?php

	//get ordering
	$sql = 'SELECT cal.article_id, cal.label_id, cl.site_id AS cl_site_id, cl.label, ca.site_id AS ca_site_id
FROM  `cms_articles_labels` AS cal,  `cms_labels` AS cl,  `cms_articles` AS ca
WHERE ca.id_article = cal.article_id
AND cl.id_label = cal.label_id
AND cl.site_id !=0
AND cl.site_id != ca.site_id
ORDER BY  `ca`.`id_article` ASC';

	print '<thead>cal.id / cal.article_id / cal.label_id / cl_site_id / cl.label / ca_site_id</thead>'."\n";

	$results=q($sql, $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_assoc($results))
	{
		print '<tbody>';
		foreach($row as $value)
		{		   
			echo $value.' / ';
		}
		print '</tbody>'."\n";
	}
	print '<num_rows>'.mysqli_num_rows($results).'</num_rows>'."\n";

?>