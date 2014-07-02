<?php

	include("../config.php");
	
	$shortcode = array();
	$results=q("SELECT * FROM cms_languages;", $dbweb, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($results))
	{
		$shortcode[] = $row["code"];
	}

	$handle = array();
	for ($i = 0; $i<sizeof($shortcode); $i++)
	{
		$handle[$i] = fopen("../functions/cms_t_".$shortcode[$i].".php", "w");
		fwrite($handle[$i], "<?php"."\n");
		fwrite($handle[$i], "$"."t = array();"."\n");
	}

	
	$results=q("SELECT * FROM cms_translations;", $dbweb, __FILE__, __LINE__);
	if (mysqli_num_rows($results)>0)
	{
		while ($row=mysqli_fetch_array($results))
		{
			for($i = 0; $i<sizeof($shortcode); $i++)
			{
				fwrite($handle[$i], '$t["'.$row["de"].'"] = "'.addslashes($row[$shortcode[$i]]).'";'."\n");
			}
		}
	}

	for ($i = 0; $i<sizeof($shortcode); $i++)
	{
		fwrite($handle[$i], "?>");
		fclose($handle[$i]);
	}
	

?>