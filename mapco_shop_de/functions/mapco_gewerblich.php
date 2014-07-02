<?php
	///äöüÄÖÜ UTF-8
	if (!function_exists("gewerblich"))
	{
		function gewerblich($id_user)
		{
			global $dbweb;
			global $dbshop;
			
			$gewerblich=false;
			if ($id_user>0)
			{
				$results=q("SELECT * FROM cms_users WHERE id_user=".$id_user.";", $dbweb, __FILE__, __LINE__);
				$row=mysqli_fetch_array($results);
				$results=q("SELECT * FROM kunde WHERE ADR_ID='".$row["idims_adr_id"]."';", $dbshop, __FILE__, __LINE__);
				if(mysqli_num_rows($results)>0) $gewerblich=true; 
				//$row=mysqli_fetch_array($results);
				//if ($row["GEWERBE"]>0 or $row["KUND_NR"]) $gewerblich=true;
			}
			return($gewerblich);
		}
	}
?>