<?php
	include("config.php");

			//rewrite .htaccess
			$htaccess=file_get_contents("htaccess.bak");
			$results=q("SELECT * FROM cms_connections_blacklist WHERE time>".(time()-24*3600).";", $dbweb, __FILE__, __LINE__);
			if( mysqli_num_rows($results)>0 )
			{
				$htaccess .= "\n\n#cms_connections_blacklist\n";
				while( $row=mysqli_fetch_array($results) )
				{
					$htaccess .= 'Deny from '.long2ip($row["ip"])."\n";
				}
			}
			$handle=fopen(".htaccess", "w");
			fwrite($handle, $htaccess);
			fclose($handle);
?>