<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["id_article"]) )
	{
		echo '<ArticleImageImageformatAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Artikel-ID leer.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Artikel-ID (id_article) übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ArticleImageImageformatAdd>'."\n";
		exit;
	}
	$results=q("SELECT * FROM cms_articles WHERE id_article=".$_POST["id_article"].";", $dbweb, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<ArticleImageImageformatAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Artikel nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zu der angegebenen Artikel-ID (id_article) konnte kein Artikel gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ArticleImageImageformatAdd>'."\n";
		exit;
	}
	$article=mysqli_fetch_array($results);


	if ( !isset($_POST["id_file"]) )
	{
		echo '<ArticleImageImageformatAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Datei-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Datei-ID (id_file) übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ArticleImageImageformatAdd>'."\n";
		exit;
	}
	$results=q("SELECT * FROM cms_files WHERE id_file=".$_POST["id_file"].";", $dbweb, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<ArticleImageImageformatAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Datei nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zu der angegebenen Datei-ID (id_file) konnte keine Datei gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ArticleImageImageformatAdd>'."\n";
		exit;
	}
	$file=mysqli_fetch_array($results);
	if( $file["original_id"]>0 )
	{
		echo '<ArticleImageImageformatAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Datei kein Originalbild.</shortMsg>'."\n";
		echo '		<longMsg>Die angegebene Datei muss ein Originalbild sein.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ArticleImageImageformatAdd>'."\n";
		exit;
	}


	if ( !isset($_POST["id_imageformat"]) )
	{
		echo '<ArticleImageImageformatAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bildformat-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Bildformat-ID (id_imageformat) übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ArticleImageImageformatAdd>'."\n";
		exit;
	}
	$results=q("SELECT * FROM cms_imageformats WHERE id_imageformat=".$_POST["id_imageformat"].";", $dbweb, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<ArticleImageImageformatAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bildformat nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zu der angegebenen Bildformat-ID (id_imageformat) konnte kein Bildformat gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ArticleImageImageformatAdd>'."\n";
		exit;
	}
	$imageformat=mysqli_fetch_array($results);


	require_once('../modules/phpThumb/phpthumb.class.php');


	$dir=floor(bcdiv($_POST["id_file"], 1000));
	$original_filename='../files/'.$dir.'/'.$_POST["id_file"].'.'.$file["extension"];

	q("INSERT INTO cms_files (filename, extension, filesize, description, imageformat_id, original_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".$file["filename"]."', '".$file["extension"]."', ".$file["filesize"].", '', ".$_POST["id_imageformat"].", ".$file["id_file"].", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
	$file_id=mysqli_insert_id($dbweb);
	$dir=floor(bcdiv($file_id, 1000));
	if (!file_exists("../files/".$dir)) mkdir("../files/".$dir);
	$full_filename='../files/'.$dir.'/'.$file_id.'.'.$file["extension"];

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
			echo '<ArticleImageImageformatAdd>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Temporärdatei anlegen fehlgeschlagen.</shortMsg>'."\n";
			echo '		<longMsg>Beim Anlegen einer temporären Datei ist ein Fehler aufgetreten.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<Response>'.$responseXml.'</Response>'."\n";
			echo '</startUploadJobResponse>'."\n";
			exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		$tempfile=(string)$response->Filename[0];
		
		//create zoomed image in tempfile
		$zf=$imageformat["zoom"]/100;
		$phpThumb = new phpThumb();
		$phpThumb->setSourceFilename("../".$original_filename);
		$phpThumb->q = 95;
		if ($imageformat["width"]>0) $phpThumb->w = $imageformat["width"]*$zf;
		if ($imageformat["height"]>0) $phpThumb->h = $imageformat["height"]*$zf;
		if ($imageformat["zc"]==1) $phpThumb->zc = $imageformat["zc"]; //zoom and crop
		if ($imageformat["aoe"]==1) $phpThumb->aoe = $imageformat["aoe"]; //enlargen smaller images
		$phpThumb->config_output_format = 'jpeg';
		$phpThumb->config_error_die_on_error = false;
		if (@$phpThumb->GenerateThumbnail())
		{
			if (!$phpThumb->RenderToFile("../".$tempfile))
			{
				echo '<ArticleImageImageformatAdd>'."\n";
				echo '	<Ack>Failure</Ack>'."\n";
				echo '	<Error>'."\n";
				echo '		<Code>'.__LINE__.'</Code>'."\n";
				echo '		<shortMsg>Fehler beim Speichern der Bildkonvertierung.</shortMsg>'."\n";
				echo '		<longMsg>Beim Speichern des konvertierten Bildes trat ein Fehler auf.</longMsg>'."\n";
				echo '	</Error>'."\n";
				echo '	<Response>'.implode("\n", $phpThumb->debugmessages).'</Response>'."\n";
				echo '</startUploadJobResponse>'."\n";
				exit;
			}
		}
		else
		{
			echo '<ArticleImageImageformatAdd>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Fehler bei der Bildkonvertierung.</shortMsg>'."\n";
			echo '		<longMsg>Beim Konvertieren des Bildes trat ein Fehler auf.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<Response>'.implode("\n", $phpThumb->debugmessages).'</Response>'."\n";
			echo '</startUploadJobResponse>'."\n";
			exit;
		}
		$original_filename=$tempfile;
	}
	
	if($imageformat["background_image"]!="")
	{
		//render background image if set
		$phpThumb = new phpThumb();
		$phpThumb->setSourceFilename('../../'.$imageformat["background_image"]);
		$phpThumb->q = 95;
		if ($imageformat["width"]>0) $phpThumb->w = $imageformat["width"];
		if ($imageformat["height"]>0) $phpThumb->h = $imageformat["height"];
		if ($imageformat["zc"]==1) $phpThumb->zc = $imageformat["zc"]; //zoom and crop
		if ($imageformat["aoe"]==1) $phpThumb->aoe = $imageformat["aoe"]; //enlargen smaller images
		$phpThumb->setParameter('fltr', 'wmi|../'.$original_filename.'|C|100');
		if ($imageformat["watermark"]!="") $phpThumb->setParameter('fltr', 'wmi|../../'.$imageformat["watermark"].'|'.$imageformat["watermark_position"].'|'.$imageformat["watermark_opacity"].'|0|0');
		$phpThumb->config_output_format = 'jpeg';
		$phpThumb->config_error_die_on_error = false;
		if (@$phpThumb->GenerateThumbnail())
		{
			if (!$phpThumb->RenderToFile('../'.$full_filename))
			{
				echo '<ArticleImageImageformatAdd>'."\n";
				echo '	<Ack>Failure</Ack>'."\n";
				echo '	<Error>'."\n";
				echo '		<Code>'.__LINE__.'</Code>'."\n";
				echo '		<shortMsg>Fehler beim Speichern der Bildkonvertierung.</shortMsg>'."\n";
				echo '		<longMsg>Beim Speichern des konvertierten Bildes trat ein Fehler auf.</longMsg>'."\n";
				echo '	</Error>'."\n";
				echo '	<Response>'.implode("\n", $phpThumb->debugmessages).'</Response>'."\n";
				echo '</startUploadJobResponse>'."\n";
				exit;
			}
		}
		else
		{
			echo '<ArticleImageImageformatAdd>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Fehler bei der Bildkonvertierung.</shortMsg>'."\n";
			echo '		<longMsg>Beim Konvertieren des Bildes trat ein Fehler auf.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<Response>'.implode("\n", $phpThumb->debugmessages).'</Response>'."\n";
			echo '</startUploadJobResponse>'."\n";
			exit;
		}
	}
	else
	{
		//render image
		$phpThumb = new phpThumb();
		$phpThumb->setSourceFilename("../".$original_filename);
		$phpThumb->q = 95;
		if ($imageformat["width"]>0) $phpThumb->w = $imageformat["width"];
		if ($imageformat["height"]>0) $phpThumb->h = $imageformat["height"];
		if ($imageformat["zc"]==1) $phpThumb->zc = $imageformat["zc"]; //zoom and crop
		if ($imageformat["aoe"]==1) $phpThumb->aoe = $imageformat["aoe"]; //enlargen smaller images
		if ($imageformat["watermark"]!="") $phpThumb->setParameter('fltr', 'wmi|../../'.$imageformat["watermark"].'|'.$imageformat["watermark_position"].'|'.$imageformat["watermark_opacity"].'|0|0');
		$phpThumb->config_output_format = 'jpeg';
		$phpThumb->config_error_die_on_error = false;
		if (@$phpThumb->GenerateThumbnail())
		{
			if (!$phpThumb->RenderToFile('../'.$full_filename))
			{
				echo '<ArticleImageImageformatAdd>'."\n";
				echo '	<Ack>Failure</Ack>'."\n";
				echo '	<Error>'."\n";
				echo '		<Code>'.__LINE__.'</Code>'."\n";
				echo '		<shortMsg>Fehler beim Speichern der Bildkonvertierung.</shortMsg>'."\n";
				echo '		<longMsg>Beim Speichern des konvertierten Bildes trat ein Fehler auf.</longMsg>'."\n";
				echo '	</Error>'."\n";
				echo '	<Response>'.implode("\n", $phpThumb->debugmessages).'</Response>'."\n";
				echo '</startUploadJobResponse>'."\n";
				exit;
			}
		}
		else
		{
			echo '<ArticleImageImageformatAdd>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Fehler bei der Bildkonvertierung.</shortMsg>'."\n";
			echo '		<longMsg>Beim Konvertieren des Bildes trat ein Fehler auf.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<Response>'.implode("\n", $phpThumb->debugmessages).'</Response>'."\n";
			echo '</startUploadJobResponse>'."\n";
			exit;
		}
	}
	$filesize=filesize($full_filename);
	q("UPDATE cms_files SET filesize=".$filesize." WHERE id_file=".$file_id.";", $dbweb, __FILE__, __LINE__);

	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<ArticleImageImageformatAdd>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</ArticleImageImageformatAdd>'."\n";

?>