<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["id_article"]) )
	{
		echo '<ImageUploadResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Artikel-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Artikel-ID übermittelt werden, damit der Service weiß, zu welchem Artikel die Datei gehört.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ImageUploadResponse>'."\n";
		exit;
	}

	if ( !isset($_FILES["Filedata"]) )
	{
		echo '<ImageUploadResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Datei nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Datei übermittelt werden, damit der Service weiß, welche Datei verarbeitet werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ImageUploadResponse>'."\n";
		exit;
	}

	require_once('../modules/phpThumb/phpthumb.class.php');

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
	if (!file_exists("../files/".$dir)) mkdir("../files/".$dir);
	$noerror=move_uploaded_file($tmp_file, $full_filename);
	if (!$noerror) die("ERROR #".__LINE__.' in '.__FILE__.': Could not move uploaded file.');
	$filesize=filesize($full_filename);
	q("UPDATE cms_files SET filesize=".$filesize." WHERE id_file=".$file_id.";", $dbweb, __FILE__, __LINE__);
	q("INSERT INTO cms_articles_images (article_id, file_id, ordering, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$_POST["id_article"].", ".$file_id.", ".$ordering.", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
	$original_id=$file_id;
	$original_filename='../'.$full_filename;
	$original_filename2=$original_filename; //backup for later
		
	//save image in imageformats
	$results=q("SELECT * FROM cms_articles WHERE id_article=".$_POST["id_article"].";", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$results=q("SELECT * FROM cms_imageformats WHERE imageprofile_id=".$row["imageprofile_id"].";", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$original_filename=$original_filename2; //reset original_filename
		q("INSERT INTO cms_files (filename, extension, filesize, description, imageformat_id, original_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".$filename."', '".$extension."', ".$filesize.", '', ".$row["id_imageformat"].", ".$original_id.", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
		$file_id=mysqli_insert_id($dbweb);
		$dir=floor(bcdiv($file_id, 1000));
		if (!file_exists("../files/".$dir)) mkdir("../files/".$dir);
		$full_filename='../files/'.$dir.'/'.$file_id.'.'.$extension;

		//render zoomed if set
		if ($row["zoom"]!=0)
		{
			$zf=$row["zoom"]/100;
			$phpThumb = new phpThumb();
			$phpThumb->setSourceFilename($original_filename);
			$phpThumb->q = 95;
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
			$phpThumb->q = 95;
			if ($row["width"]>0) $phpThumb->w = $row["width"];
			if ($row["height"]>0) $phpThumb->h = $row["height"];
			if ($row["zc"]==1) $phpThumb->zc = $row["zc"]; //zoom and crop
			if ($row["aoe"]==1) $phpThumb->aoe = $row["aoe"]; //enlargen smaller images
			$phpThumb->setParameter('fltr', 'wmi|'.$original_filename.'|C|100');
			if ($row["watermark"]!="") $phpThumb->setParameter('fltr', 'wmi|../../'.$row["watermark"].'|'.$row["watermark_position"].'|'.$row["watermark_opacity"].'|0|0');
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
			$phpThumb->q = 95;
			if ($row["width"]>0) $phpThumb->w = $row["width"];
			if ($row["height"]>0) $phpThumb->h = $row["height"];
			if ($row["zc"]==1) $phpThumb->zc = $row["zc"]; //zoom and crop
			if ($row["aoe"]==1) $phpThumb->aoe = $row["aoe"]; //enlargen smaller images
			if ($row["watermark"]!="") $phpThumb->setParameter('fltr', 'wmi|../../'.$row["watermark"].'|'.$row["watermark_position"].'|'.$row["watermark_opacity"].'|0|0');
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
	
	echo '<ImageUploadResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</ImageUploadResponse>'."\n";

?>