<?php

	if ( !function_exists("tl") )
	{
		/**
		 *	translate links to appropriate language
		 *
		 *	@params
		 *	- id_menuitem
		 *	- type
		 *	- lang
		 */
		function tl($id_menuitem, $type, $lang = "")
		{
			global $dbweb, $tl;
			
			if ($lang != "")
			{
				$results = q("
					SELECT * 
					FROM cms_languages 
					WHERE code = '" . $lang . "';", $dbweb, __FILE__, __LINE__);
				$cms_languages = mysqli_fetch_assoc($results);
				$results = q("
					SELECT * 
					FROM cms_menuitems_languages 
					WHERE menuitem_id = " . $id_menuitem . " 
					AND language_id = " . $cms_languages["id_language"] . ";", $dbweb, __FILE__, __LINE__);
				if (mysqli_num_rows($results) == 0)
				{
					$results = q("
						SELECT * 
						FROM cms_menuitems_languages 
						WHERE menuitem_id = " . $id_menuitem . ";", $dbweb, __FILE__, __LINE__);
				}
				$row = mysqli_fetch_assoc($results);
				return ($row[$type]);
			}
			
			if (!isset($tl)) include("cms_tl_" . $_SESSION["lang"] . ".php");
			
			return($tl[$id_menuitem][$type]);
		}
	}
?>