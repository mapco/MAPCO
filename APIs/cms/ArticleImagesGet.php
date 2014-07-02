<?php
	/*************************
	********** SOA 2 *********
	*************************/
	
	$required=array("article_id"	=> "numeric", "lang"	=> "text");
	
	check_man_params($required);
	
	$article_id = $_POST['article_id'];
	$lang = $_POST['lang'];
	
	//IMAGE VIEW
	$imageformats=array();
//		$imageformats[0]="Original";
	$results=q("SELECT imageprofile_id FROM cms_articles WHERE id_article=".$article_id.";", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	
	//get imageformats
	$i=0;
	$results=q("SELECT id_imageformat, title FROM cms_imageformats WHERE imageprofile_id=".$row["imageprofile_id"]." ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$imageformats[$i]["id"]=$row["id_imageformat"];
		$imageformats[$i]["title"]=$row["title"];

		$xml .= '<image_format>'.$imageformats[$i]["title"].'</image_format>'."\n";
		
		$i++;
	}
	 
	$query="SELECT file_id, id, ordering FROM cms_articles_images WHERE article_id=".$article_id." ORDER BY ordering;";
	$results=q($query, $dbweb, __FILE__, __LINE__);
	if (mysqli_num_rows($results)<1)
	{
		$xml .= '<Error>Es sind noch keine Bilder mit diesem Artikel verknüpft!</Error>'."\n";
	}	
	while($row=mysqli_fetch_array($results))
	{
		$xml .= ' <image>'."\n";
		$results2=q("SELECT id_file, filename, extension, description FROM cms_files WHERE id_file=".$row["file_id"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		
		//buffer other images
		$images=array();
		$results3=q("SELECT imageformat_id, id_file FROM cms_files WHERE original_id=".$row["file_id"].";", $dbweb, __FILE__, __LINE__);
		while( $row3=mysqli_fetch_array($results3) )
		{
			$images[$row3["imageformat_id"]]=$row3["id_file"];
		}
		
		$dir=floor(bcdiv($row2["id_file"], 1000));
		$id_file=$row2["id_file"];
		$filename=$row2["filename"].'.'.$row2["extension"];
		$full_filename='files/'.$dir.'/'.$row2["id_file"].'.'.$row2["extension"];
		$description=$row2["description"];
		$xml .= '	<id>'.$row["id"].'</id>'."\n";
		//ordering
		$xml .= '	<ordering>'.$row["ordering"].'</ordering>'."\n";
		//original
		$xml .= '	<original_name>'.$full_filename.'</original_name>'."\n";
		$xml .= '	<original_path>'.post(PATH."soa/", array("API" => "cms", "Action" => "ImageThumbnail", "id_file" => $row2["id_file"])).'</original_path>'."\n";
		
		//further formats
		for($i=0; $i<sizeof($imageformats); $i++)
		{
			$xml .= '<subimage>'."\n";
//				$results2=q("SELECT * FROM cms_files WHERE original_id=".$row["file_id"]." AND imageformat_id=".$imageformats[$i]["id"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
//				if ( mysqli_num_rows($results2)==0 )
			if ( !isset($images[$imageformats[$i]["id"]]) )
			{
				$xml .= 'N/A';
			}
			else
			{
//					$row2=mysqli_fetch_array($results2);
//					$dir=floor(bcdiv($row2["id_file"], 1000));
				$row2["id_file"]=$images[$imageformats[$i]["id"]];
				$dir=floor(bcdiv($row2["id_file"], 1000));
				$full_filename='files/'.$dir.'/'.$row2["id_file"].'.jpg';
				
				$xml .= '	<image_name>'.$full_filename.'</image_name>'."\n";
				$xml .= '	<image_path>'.post(PATH."soa/", array("API" => "cms", "Action" => "ImageThumbnail", "id_file" => $row2["id_file"])).'</image_path>'."\n";
				$xml .= '	<image_format_id>'.$imageformats[$i]["id"].'</image_format_id>'."\n";
				$xml .= '	<image_format_title>'.$imageformats[$i]["title"].'</image_format_title>'."\n";
//					echo '		<img class="lazyimage" src="images/icons/loaderb64.gif" title="'.$imageformats[$i]["title"].' anzeigen" alt="'.$row2["id_file"].'" />';
			}
			$xml .= '</subimage>'."\n";
		}
		
		//Bildunterschrift
		$xml .= '	<image_description>'.$description.'</image_description>'."\n";
		$xml .= '	<extension>'.$row2["extension"].'</extension>'."\n";
		$xml .= '	<file_id>'.$row["file_id"].'</file_id>'."\n";
		$xml .= '	<file_name>'.$row2["filename"].'</file_name>'."\n";
		$xml .= '	<id_file>'.$id_file.'</id_file>'."\n";
		$xml .= ' </image>'."\n";
	}

	print $xml;
?>