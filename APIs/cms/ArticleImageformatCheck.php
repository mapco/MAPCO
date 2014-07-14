<?php

	$required=array("id_article"	=> "numericNN" );
	check_man_params($required);

	//cache files
	$files=array();
	$results=q("SELECT * FROM cms_files WHERE original_id>0;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_assoc($results) )
	{
		$files[$row["original_id"]][$row["imageformat_id"]]=$row["id_file"];
	}

	//cache imageformats
	$imageformat=array();
	$results=q("SELECT * FROM cms_articles WHERE id_article=".$_POST["id_article"].";", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_assoc($results);
	$results=q("SELECT * FROM cms_imageformats WHERE imageprofile_id=".$row["imageprofile_id"].";", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_assoc($results) )
	{
		$imageformat[]=$row["id_imageformat"];
	}
	
	//check known imageformats
	$results=q("SELECT * FROM cms_articles_images WHERE article_id=".$_POST["id_article"].";", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_assoc($results) )
	{
		for($i=0; $i<sizeof($imageformat); $i++)
		{
			if( !isset($files[$row["file_id"]][$imageformat[$i]]) )
			{
				/*
				$postdata=array();
				$postdata["API"]="cms";
				$postdata["Action"]="ArticleImageImageformatAdd";
				$postdata["id_article"]=$_POST["id_article"];
				$postdata["id_file"]=$row["file_id"];
				$postdata["id_imageformat"]=$imageformat[$i];
				post(PATH."soa/", $postdata);
				*/
				echo '	<ImageformatMissing>'."\n";
				echo '		<id_article>'.$_POST["id_article"].'</id_article>'."\n";
				echo '		<id_file>'.$row["file_id"].'</id_file>'."\n";
				echo '		<id_imageformat>'.$imageformat[$i].'</id_imageformat>'."\n";
				echo '	</ImageformatMissing>'."\n";
			}
		}
	}
?>