<?php
	///äöüÄÖÜ UTF-8
	if (!function_exists("hide_price"))
	{
		function hide_price($id_user)
		{
			global $dbweb;
			global $dbshop;
			
			$hide_price=false;
			if ($id_user>0)
			{
				$results=q("SELECT * FROM cms_users WHERE id_user=".$id_user.";", $dbweb, __FILE__, __LINE__);
				$row=mysqli_fetch_array($results);
				if ($row["hide_price"]>0) $hide_price=true;
			}
			return($hide_price);
		}
	}
?>