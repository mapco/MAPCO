<?php
	/*************************
	********** SOA 2 *********
	***** Author Sven E. *****
	*** Lastmod 26.03.2014 ***
	*************************/
	
	  $required=array("article_id"	=> "numeric", "source"	=> "text", "filename"	=>	"text", "filesize"	=>	"numeric");
	
	check_man_params($required);
	
	$article_id = $_POST['article_id'];
	$source = $_POST['source'];
	$filename = $_POST['filename'];
	$filesize = $_POST['filesize'];

	$xml = '';

	//get imageformats
	$result=q("SELECT imageprofile_id FROM cms_articles WHERE id_article=".$article_id.";", $dbweb, __FILE__, __LINE__);
	if( mysqli_num_rows($result)==1 )
	{
		$row=mysqli_fetch_assoc($result);
		
		$i=0;
		$results=q("SELECT * FROM cms_imageformats WHERE imageprofile_id=".$row["imageprofile_id"]." ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
		while( $row=mysqli_fetch_assoc($results) )
		{
			$imageformats[$i]=$row;
			$i++;
		}
	}
	
	// write original
	$filename=substr($filename, strpos($filename, "/"), strlen($filename));
	$filename=substr($filename, 0, strrpos($filename, "."));
	$extension=substr($_POST["filename"], strrpos($_POST["filename"], ".")+1, strlen($_POST["filename"]));
	
	$firstmod=time();
	$firstmod_user=$_SESSION["id_user"];
	$lastmod=time();
	$lastmod_user=$_SESSION["id_user"];
	
	q("INSERT INTO cms_files (filename, extension, filesize,  imageformat_id, original_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".$filename."', '".$extension."', '".$filesize."','0', '0', ".$firstmod.", ".$firstmod_user.", ".$lastmod.", ".$lastmod_user.");", $dbweb, __FILE__, __LINE__);
	$id_file=mysqli_insert_id($dbweb);
	
	$results=q("SELECT id FROM cms_articles_images WHERE article_id=".$article_id." ORDER BY ordering ASC LIMIT 1;", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_assoc($results);
	$ordering=$row['ordering']+1;
	q("INSERT INTO cms_articles_images (article_id, file_id, ordering, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$article_id.", ".$id_file.", ".$ordering.", ".$firstmod.", ".$firstmod_user.", ".$lastmod.", ".$lastmod_user.");", $dbweb, __FILE__, __LINE__);
	$folder=floor($id_file/1000);
	if (!file_exists("../files/".$folder)) mkdir("../files/".$folder);
	$destination="../files/".$folder."/".$id_file.".".$extension;
	copy($source, $destination);
	unlink($source);
	
	require_once('../modules/phpThumb/phpthumb.class.php');
	
	//further formats
	for($i=0; $i<sizeof($imageformats); $i++)
	{	
		$original_filename = $destination;
	
		q("INSERT INTO cms_files (filename, extension, filesize, imageformat_id, original_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".$filename."', '".$extension."', ".$filesize.", ".$imageformats[$i]['id_imageformat'].", ".$id_file.", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
		$file_id=mysqli_insert_id($dbweb);
		$dir=floor(bcdiv($file_id, 1000));
		if (!file_exists("../files/".$dir)) mkdir("../files/".$dir);
		$full_filename='../files/'.$dir.'/'.$file_id.'.'.$extension;

		//render zoomed if set
		if ($imageformat["zoom"]!=100)
		{
			//create temp file
			$fieldset=array();
			$fieldset["API"]="cms";
			$fieldset["Action"]="TempFileAdd";
			$fieldset["extension"]="jpg";
			$responseXml = post(PATH."soa/", $fieldset);
			$use_errors = libxml_use_internal_errors(true);
			try
			{
				$response = new SimpleXMLElement($responseXml);
			}
			catch(Exception $e)
			{
				$xml .= '<ArticleImageImageformatAdd>'."\n";
				$xml .= '	<Ack>Failure</Ack>'."\n";
				$xml .= '	<Error>'."\n";
				$xml .= '		<Code>'.__LINE__.'</Code>'."\n";
				$xml .= '		<shortMsg>Temporärdatei anlegen fehlgeschlagen.</shortMsg>'."\n";
				$xml .= '		<longMsg>Beim Anlegen einer temporären Datei ist ein Fehler aufgetreten.</longMsg>'."\n";
				$xml .= '	</Error>'."\n";
				$xml .= '	<Response>'.$responseXml.'</Response>'."\n";
				$xml .= '</startUploadJobResponse>'."\n";
				exit;
			}
			libxml_clear_errors();
			libxml_use_internal_errors($use_errors);
			$tempfile=(string)$response->Filename[0];
			
			//create zoomed image in tempfile
			$zf=$imageformats[$i]["zoom"]/100;
			$phpThumb = new phpThumb();
			$phpThumb->setSourceFilename("../".$original_filename);
			$phpThumb->q = 95;
			if ($imageformat["width"]>0) $phpThumb->w = $imageformats[$i]["width"]*$zf;
			if ($imageformat["height"]>0) $phpThumb->h = $imageformats[$i]["height"]*$zf;
			if ($imageformat["zc"]==1) $phpThumb->zc = $imageformats[$i]["zc"]; //zoom and crop
			if ($imageformat["aoe"]==1) $phpThumb->aoe = $imageformats[$i]["aoe"]; //enlargen smaller images
			$phpThumb->config_output_format = 'jpeg';
			$phpThumb->config_error_die_on_error = false;
			if (@$phpThumb->GenerateThumbnail())
			{
				if (!$phpThumb->RenderToFile("../".$tempfile))
				{
					$xml .= '<ArticleImageImageformatAdd>'."\n";
					$xml .= '	<Ack>Failure</Ack>'."\n";
					$xml .= '	<Error>'."\n";
					$xml .= '		<Code>'.__LINE__.'</Code>'."\n";
					$xml .= '		<shortMsg>Fehler beim Speichern der Bildkonvertierung.</shortMsg>'."\n";
					$xml .= '		<longMsg>Beim Speichern des konvertierten Bildes trat ein Fehler auf.</longMsg>'."\n";
					$xml .= '	</Error>'."\n";
					$xml .= '	<Response>'.implode("\n", $phpThumb->debugmessages).'</Response>'."\n";
					$xml .= '</startUploadJobResponse>'."\n";
					exit;
				}
			}
			else
			{
				$xml .= '<ArticleImageImageformatAdd>'."\n";
				$xml .= '	<Ack>Failure</Ack>'."\n";
				$xml .= '	<Error>'."\n";
				$xml .= '		<Code>'.__LINE__.'</Code>'."\n";
				$xml .= '		<shortMsg>Fehler bei der Bildkonvertierung.</shortMsg>'."\n";
				$xml .= '		<longMsg>Beim Konvertieren des Bildes trat ein Fehler auf.</longMsg>'."\n";
				$xml .= '	</Error>'."\n";
				$xml .= '	<Response>'.implode("\n", $phpThumb->debugmessages).'</Response>'."\n";
				$xml .= '</startUploadJobResponse>'."\n";
				exit;
			}
			$original_filename=$tempfile;
		}
	
		if($imageformat["background_image"]!="")
		{
			//render background image if set
			$phpThumb = new phpThumb();
			$phpThumb->setSourceFilename('../../'.$imageformats[$i]["background_image"]);
			$phpThumb->q = 95;
			if ($imageformats[$i]["width"]>0) $phpThumb->w = $imageformats[$i]["width"];
			if ($imageformats[$i]["height"]>0) $phpThumb->h = $imageformats[$i]["height"];
			if ($imageformats[$i]["zc"]==1) $phpThumb->zc = $imageformats[$i]["zc"]; //zoom and crop
			if ($imageformats[$i]["aoe"]==1) $phpThumb->aoe = $imageformats[$i]["aoe"]; //enlargen smaller images
			$phpThumb->setParameter('fltr', 'wmi|../'.$original_filename.'|C|100');
			if ($imageformat["watermark"]!="") $phpThumb->setParameter('fltr', 'wmi|../../'.$imageformats[$i]["watermark"].'|'.$imageformats[$i]["watermark_position"].'|'.$imageformats[$i]["watermark_opacity"].'|0|0');
			$phpThumb->config_output_format = 'jpeg';
			$phpThumb->config_error_die_on_error = false;
			if (@$phpThumb->GenerateThumbnail())
			{
				if (!$phpThumb->RenderToFile('../'.$full_filename))
				{
					$xml .= '<ArticleImageImageformatAdd>'."\n";
					$xml .= '	<Ack>Failure</Ack>'."\n";
					$xml .= '	<Error>'."\n";
					$xml .= '		<Code>'.__LINE__.'</Code>'."\n";
					$xml .= '		<shortMsg>Fehler beim Speichern der Bildkonvertierung.</shortMsg>'."\n";
					$xml .= '		<longMsg>Beim Speichern des konvertierten Bildes trat ein Fehler auf.</longMsg>'."\n";
					$xml .= '	</Error>'."\n";
					$xml .= '	<Response>'.implode("\n", $phpThumb->debugmessages).'</Response>'."\n";
					$xml .= '</startUploadJobResponse>'."\n";
					exit;
				}
			}
			else
			{
				$xml .= '<ArticleImageImageformatAdd>'."\n";
				$xml .= '	<Ack>Failure</Ack>'."\n";
				$xml .= '	<Error>'."\n";
				$xml .= '		<Code>'.__LINE__.'</Code>'."\n";
				$xml .= '		<shortMsg>Fehler bei der Bildkonvertierung.</shortMsg>'."\n";
				$xml .= '		<longMsg>Beim Konvertieren des Bildes trat ein Fehler auf.</longMsg>'."\n";
				$xml .= '	</Error>'."\n";
				$xml .= '	<Response>'.implode("\n", $phpThumb->debugmessages).'</Response>'."\n";
				$xml .= '</startUploadJobResponse>'."\n";
				exit;
			}
		}
		else
		{
			//render image
			$phpThumb = new phpThumb();
			$phpThumb->setSourceFilename("../".$original_filename);
			$phpThumb->q = 95;
			if ($imageformats[$i]["width"]>0) $phpThumb->w = $imageformats[$i]["width"];
			if ($imageformats[$i]["height"]>0) $phpThumb->h = $imageformats[$i]["height"];
			if ($imageformats[$i]["zc"]==1) $phpThumb->zc = $imageformats[$i]["zc"]; //zoom and crop
			if ($imageformats[$i]["aoe"]==1) $phpThumb->aoe = $imageformats[$i]["aoe"]; //enlargen smaller images
			if ($imageformats[$i]["watermark"]!="") $phpThumb->setParameter('fltr', 'wmi|../../'.$imageformats[$i]["watermark"].'|'.$imageformats[$i]["watermark_position"].'|'.$imageformats[$i]["watermark_opacity"].'|0|0');
			$phpThumb->config_output_format = 'jpeg';
			$phpThumb->config_error_die_on_error = false;
			if (@$phpThumb->GenerateThumbnail())
			{
				if (!$phpThumb->RenderToFile('../'.$full_filename))
				{
					$xml .= '<ArticleImageImageformatAdd>'."\n";
					$xml .= '	<Ack>Failure</Ack>'."\n";
					$xml .= '	<Error>'."\n";
					$xml .= '		<Code>'.__LINE__.'</Code>'."\n";
					$xml .= '		<shortMsg>Fehler beim Speichern der Bildkonvertierung.</shortMsg>'."\n";
					$xml .= '		<longMsg>Beim Speichern des konvertierten Bildes trat ein Fehler auf.</longMsg>'."\n";
					$xml .= '	</Error>'."\n";
					$xml .= '	<Response>'.implode("\n", $phpThumb->debugmessages).'</Response>'."\n";
					$xml .= '</startUploadJobResponse>'."\n";
					exit;
				}
			}
			else
			{
				$xml .= '<ArticleImageImageformatAdd>'."\n";
				$xml .= '	<Ack>Failure</Ack>'."\n";
				$xml .= '	<Error>'."\n";
				$xml .= '		<Code>'.__LINE__.'</Code>'."\n";
				$xml .= '		<shortMsg>Fehler bei der Bildkonvertierung.</shortMsg>'."\n";
				$xml .= '		<longMsg>Beim Konvertieren des Bildes trat ein Fehler auf.</longMsg>'."\n";
				$xml .= '	</Error>'."\n";
				$xml .= '	<Response>'.implode("\n", $phpThumb->debugmessages).'</Response>'."\n";
				$xml .= '</startUploadJobResponse>'."\n";
				exit;
			}
		}
		$filesize=filesize($full_filename);
		q("UPDATE cms_files SET filesize=".$filesize." WHERE id_file=".$file_id.";", $dbweb, __FILE__, __LINE__);
	}

	print $xml;

?>