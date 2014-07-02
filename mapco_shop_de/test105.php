<?php
	include("config.php");

	$results=q("SELECT * FROM cms_users;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$results2=q("SELECT * FROM cms_users_sites WHERE user_id=".$row["id_user"]." AND site_id=1;", $dbweb, __FILE__, __LINE__);
		if( mysqli_num_rows($results2)==0 )
		{
			q("INSERT INTO cms_users_sites (user_id, site_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$row["id_user"].", 1, ".time().", 21371, ".time().", 21371);", $dbweb, __FILE__, __LINE__);
		}
	}

?>