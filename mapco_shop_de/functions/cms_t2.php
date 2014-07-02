<?php
	///äöüÄÖÜ UTF-8
	if (!function_exists("t"))
	{
		function t($text, $file="", $line="", $lang="")
		{
			if (isset($lang) and $lang=="") $lang=$_GET["lang"];
			global $dbweb;
			if ($text=="") return($text);
			
			$results=q("SELECT * FROM cms_translations WHERE de='".$text."';", $dbweb, __FILE__, __LINE__);
			if (mysqli_num_rows($results)>0)
			{
				$row=mysqli_fetch_array($results);
				if ($row[$lang]=="") $translation=$text; else $translation=$row[$lang];
				
				return($translation);
			}
			else
			{
//				q("INSERT INTO cms_translations (de, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".$text."', '".$file." ".$line."',  ".time().", 10, ".time().", 10);", $dbweb, __FILE__, __LINE__);
				q("INSERT INTO cms_translations (de, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".$text."', ".time().", 10, ".time().", 10);", $dbweb, __FILE__, __LINE__);
				return($text);
			}
		}
	}
?>