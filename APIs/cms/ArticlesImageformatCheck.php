<?php

	//cache files
	$files=array();
	$results=q("SELECT * FROM cms_files WHERE original_id>0;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_assoc($results) )
	{
		$files[$row["original_id"]][$row["imageformat_id"]]=$row["id_file"];
	}

	//cache articles
	$articles=array();
	$results=q("SELECT * FROM cms_articles WHERE imageprofile_id=".$_POST["id_imageprofile"].";", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_assoc($results) )
	{
		$articles[$row["id_article"]]=$row["id_article"];
	}

	//cache imageformats
	$imageformat=array();
	$results=q("SELECT * FROM cms_imageformats WHERE imageprofile_id=".$_POST["id_imageprofile"].";", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_assoc($results) )
	{
		$imageformat[]=$row["id_imageformat"];
	}
	
	//check known imageformats
	$results=q("SELECT * FROM cms_articles_images WHERE article_id IN (".implode(", ", $articles).");", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_assoc($results) )
	{
		for($i=0; $i<sizeof($imageformat); $i++)
		{
			if( !isset($files[$row["file_id"]][$imageformat[$i]]) )
			{
				echo '	<ImageformatMissing>'."\n";
				echo '		<id_article>'.$row["article_id"].'</id_article>'."\n";
				echo '		<id_file>'.$row["file_id"].'</id_file>'."\n";
				echo '		<id_imageformat>'.$imageformat[$i].'</id_imageformat>'."\n";
				echo '	</ImageformatMissing>'."\n";
			}
		}
	}
?>