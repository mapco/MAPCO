<?php
	$starttime = time()+microtime();

	if ( !isset($_FILES["Filedata"]) )
	{
		echo '<ImageMassUploadResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Datei nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Datei übermittelt werden, damit der Service weiß, welche Datei verarbeitet werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ImageMassUploadResponse>'."\n";
		exit;
	}

	require_once('../modules/phpThumb/phpthumb.class.php');

	//get artnr
	$filename=str_replace(" ", "", $_FILES["Filedata"]["name"]);
	$filename=eregi_replace(".jpg", "", $filename);
	$buchstaben = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z");
	for ($i=0; $i<sizeof($buchstaben); $i++)
	{
		$filename=str_replace($buchstaben[$i], "", $filename);
	}
	$artnr=eregi_replace("_", "/", $filename);
	
	//get id_item
	$results=q("SELECT * FROM shop_items WHERE MPN='".$artnr."';", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($results)==0)
	{
		echo 'Artikel mit der Nummer '.$artnr.' nicht gefunden!';
		exit();
	}
	$row=mysqli_fetch_array($results);
	$_POST["id_article"]=$row["article_id"];
	$id_item=$row["id_item"];
	
	//get order number
	$results=q("SELECT * FROM cms_articles_images WHERE article_id=".$_POST["id_article"].";", $dbweb, __FILE__, __LINE__);
	$ordering=mysqli_num_rows($results)+1;
		
	//save original image
	$tmp_file=$_FILES["Filedata"]["tmp_name"];
	$file=$_FILES["Filedata"]["name"];
	$filename=substr($file, 0, strrpos($file, "."));
	$extension=strtolower(substr($file, strrpos($file, ".")+1, strlen($file)));
	$filesize=filesize($tmp_file);
	$query="INSERT INTO cms_files (filename, extension, filesize, description, imageformat_id, original_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".$filename."', '".$extension."', ".$filesize.", '', 0, 0, ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");";
	q($query, $dbweb,__FILE__, __LINE__);
	$file_id=mysqli_insert_id($dbweb);
	$dir=floor(bcdiv($file_id, 1000));
	$full_filename='../files/'.$dir.'/'.$file_id.'.'.$extension;
	$full_files_edit_name='../files_edit/'.$file_id.'.'.$extension;
	if (!file_exists("../files/".$dir)) mkdir("../files/".$dir);
	//copy to files_edit
	copy($tmp_file, $full_files_edit_name);
	//copy to files
	$noerror=move_uploaded_file($tmp_file, $full_filename);
	if (!$noerror) die("ERROR #".__LINE__.' in '.__FILE__.': Could not move uploaded file.');
	$filesize=filesize($full_filename);
	q("UPDATE cms_files SET filesize=".$filesize." WHERE id_file=".$file_id.";", $dbweb, __FILE__, __LINE__);
	q("INSERT INTO cms_articles_images (article_id, file_id, ordering, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$_POST["id_article"].", ".$file_id.", ".$ordering.", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
	$original_id=$file_id;
	$original_filename='../'.$full_filename;
		
	//save image in imageformats
	$results=q("SELECT * FROM cms_articles WHERE id_article=".$_POST["id_article"].";", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$results=q("SELECT * FROM cms_imageformats WHERE imageprofile_id=".$row["imageprofile_id"].";", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		q("INSERT INTO cms_files (filename, extension, filesize, description, imageformat_id, original_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".$filename."', '".$extension."', ".$filesize.", '', ".$row["id_imageformat"].", ".$original_id.", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
		$file_id=mysqli_insert_id($dbweb);
		$dir=floor(bcdiv($file_id, 1000));
		if (!file_exists("../files/".$dir)) mkdir("../files/".$dir);
		$full_filename='../files/'.$dir.'/'.$file_id.'.'.$extension;

		//render zoomed if set
		if ($row["zoom"]!=100)
		{
			$zf=$row["zoom"]/100;
			$phpThumb = new phpThumb();
			$phpThumb->setSourceFilename($original_filename);
			$phpThumb->setParameter('q', 95); //quality / compression
			if ($row["width"]>0) $phpThumb->w = $row["width"]*$zf;
			if ($row["height"]>0) $phpThumb->h = $row["height"]*$zf;
			if ($row["zc"]==1) $phpThumb->zc = $row["zc"]; //zoom and crop
			if ($row["aoe"]==1) $phpThumb->aoe = $row["aoe"]; //enlargen smaller images
			$phpThumb->config_output_format = 'jpeg';
			$phpThumb->config_error_die_on_error = false;
			if (@$phpThumb->GenerateThumbnail())
			{
				if (!$phpThumb->RenderToFile('temp.jpg'))
				{
					echo 'ERROR #'.__LINE__.': '.implode("<br />", $phpThumb->debugmessages);
				}
			}
			else
			{
				echo 'ERROR #'.__LINE__.': '.implode("<br />", $phpThumb->debugmessages);
			}
			$original_filename="temp.jpg";
		}
	
		if($row["background_image"]!="")
		{
			//render background image if set
			$phpThumb = new phpThumb();
			$phpThumb->setSourceFilename('../../'.$row["background_image"]);
			$phpThumb->setParameter('q', 95); //quality / compression
			if ($row["width"]>0) $phpThumb->w = $row["width"];
			if ($row["height"]>0) $phpThumb->h = $row["height"];
			if ($row["zc"]==1) $phpThumb->zc = $row["zc"]; //zoom and crop
			if ($row["aoe"]==1) $phpThumb->aoe = $row["aoe"]; //enlargen smaller images
			if ($row["watermark"]!="") $phpThumb->setParameter('fltr', 'wmi|'.$original_filename.'|C|100');
			if ($row["watermark"]!="") $phpThumb->setParameter('fltr', 'wmi|../../'.$row["watermark"].'|'.$row["watermark_position"].'|'.$row["watermark_opacity"].'');
			$phpThumb->config_output_format = 'jpeg';
			$phpThumb->config_error_die_on_error = false;
			if (@$phpThumb->GenerateThumbnail())
			{
				if (!$phpThumb->RenderToFile('../'.$full_filename))
				{
					echo 'ERROR #'.__LINE__.': '.implode("<br />", $phpThumb->debugmessages);
				}
			}
			else
			{
				echo 'ERROR #'.__LINE__.': '.implode("<br />", $phpThumb->debugmessages);
			}
		}
		else
		{
			//render image
			$phpThumb = new phpThumb();
			$phpThumb->setSourceFilename($original_filename);
			$phpThumb->setParameter('q', 95); //quality / compression
			if ($row["width"]>0) $phpThumb->w = $row["width"];
			if ($row["height"]>0) $phpThumb->h = $row["height"];
			if ($row["zc"]==1) $phpThumb->zc = $row["zc"]; //zoom and crop
			if ($row["aoe"]==1) $phpThumb->aoe = $row["aoe"]; //enlargen smaller images
			if ($row["watermark"]!="") $phpThumb->setParameter('fltr', 'wmi|../../'.$row["watermark"].'|'.$row["watermark_position"].'|'.$row["watermark_opacity"].'');
			$phpThumb->config_output_format = 'jpeg';
			$phpThumb->config_error_die_on_error = false;
			if (@$phpThumb->GenerateThumbnail())
			{
				if (!$phpThumb->RenderToFile('../'.$full_filename))
				{
					error(__FILE__, __LINE__, print_r($phpThumb->debugmessages, true));
				}
			}
			else
			{
				error(__FILE__, __LINE__, print_r($phpThumb->debugmessages, true));
			}
		}
		$filesize=filesize($full_filename);
		q("UPDATE cms_files SET filesize=".$filesize." WHERE id_file=".$file_id.";", $dbweb, __FILE__, __LINE__);
	}
	if ($original_filename=="temp.jpg") unlink("../modules/phpThumb/temp.jpg");

	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<ImageMassUploadResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<ItemID>'.$id_item.'</ItemID>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</ImageMassUploadResponse>'."\n";

?>