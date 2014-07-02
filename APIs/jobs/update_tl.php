<?php

	//include("../config.php");
	
	$shortcode = array();
	$results=q("SELECT * FROM cms_languages;", $dbweb, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($results))
	{
		$lang_id[] = $row["id_language"];
		$shortcode[] = $row["code"];
	}

	for($i = 0; $i<sizeof($lang_id); $i++)
	{
		//open file
		$handle = fopen("../functions/cms_tl_".$shortcode[$i].".php", "w");
		fwrite($handle, "<?php"."\n");
		fwrite($handle, "$"."t = array();"."\n");

		//write translated content
		$translated=array();
		$results=q("SELECT * FROM cms_menuitems_languages WHERE language_id=".$lang_id[$i].";", $dbweb, __FILE__, __LINE__);
		while( $row=mysqli_fetch_assoc($results) )
		{
			$translated[$row["menuitem_id"]]=1;
			fwrite($handle, '$tl["'.$row["menuitem_id"].'"]["alias"] = "'.addslashes($row["alias"]).'";'."\n");
			fwrite($handle, '$tl["'.$row["menuitem_id"].'"]["title"] = "'.addslashes($row["title"]).'";'."\n");
			fwrite($handle, '$tl["'.$row["menuitem_id"].'"]["description"] = "'.addslashes($row["description"]).'";'."\n");
		}

		//write missing translations
		$results=q("SELECT * FROM cms_menuitems_languages;", $dbweb, __FILE__, __LINE__);
		while( $row=mysqli_fetch_assoc($results) )
		{
			if( !isset($translated[$row["menuitem_id"]]) )
			{
				$translated[$row["menuitem_id"]]=1;
				fwrite($handle, '$tl["'.$row["menuitem_id"].'"]["alias"] = "'.addslashes($row["alias"]).'";'."\n");
				fwrite($handle, '$tl["'.$row["menuitem_id"].'"]["title"] = "'.addslashes($row["title"]).'";'."\n");
				fwrite($handle, '$tl["'.$row["menuitem_id"].'"]["description"] = "'.addslashes($row["description"]).'";'."\n");
			}
		}

		//close file
		fwrite($handle, "?>");
		fclose($handle);
		
		echo '<LinksUpdated>'.$shortcode[$i].'</LinksUpdated>'."\n";
	}
?>