<?php
	///äöüÄÖÜ UTF-8
	if ( !function_exists("t") )
	{
		function t($text, $file="", $line="", $lang="")
		{
			global $t;
			if ( !isset($t) ) include ("cms_t_".$_SESSION["lang"].".php");
			global $count_translations;
			$count_translations++;
			global $dbweb;
			
			if (!isset($t[$text]))
			{
				$results=q("SELECT * FROM cms_translations WHERE de='".$text."';", $dbweb, __FILE__, __LINE__);
				if (mysqli_num_rows($results)==0)
				{
					q("INSERT INTO cms_translations (de, file, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".$text."', '".$file." ".$line."', ".time().", 10, ".time().", 10);", $dbweb, __FILE__, __LINE__);
				}
				return($text);

			}
			else
			{			
				if($t[$text]=="")
				{
					return($text);
				}
				else
				{
					return($t[$text]);
				}
			}
		}
	}
?>