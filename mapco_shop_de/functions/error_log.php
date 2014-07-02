<?php
	///äöüÄÖÜ UTF-8
	//error log
	if (!function_exists("error_log"))
	{
		function error_log($table, $url, $ip)
		{
			global $dbshop;
			echo '<script> alert("x"); </script>';
			
			$results=q("SELECT * FROM error_".$table." WHERE url='".$url."' AND ip='".$ip."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
			if (mysql_num_rows($results)>0)
			{
				$row=mysql_fetch_array($results);
				q("UPDATE error_".$table." SET count=".($row["count"]+1).", lastmod=".time()." WHERE id_error=".$row["id_error"].";", $dbshop, __FILE__, __LINE__);
			}
			else
			{
				q("INSERT INTO error_".$table." (url, ip, lastmod) VALUES('".$url."', '".$ip."', ".time().");", $dbshop, __FILE__, __LINE__);
			}
		}
	}	
?>