<?php

	if ( !isset($_POST["id_file"]) )
	{
		echo '<ImageImageformatAdd>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bild-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Bild-ID (id_file) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ImageImageformatAdd>'."\n";
		exit;
	}
	$results=q("SELECT * FROM cms_files WHERE id_file=".$_POST["id_file"].";", $dbweb, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<ImageImageformatAdd>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bild nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Unter der angegebenen Bild-ID ('.$_POST["id_imageformat"].') existiert kein Bild.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ImageImageformatAdd>'."\n";
		exit;
	}
	$file=mysqli_fetch_array($results);
	$dir=floor(bcdiv($_POST["id_file"], 1000));
	$full_filename='../files/'.$dir.'/'.$_POST["id_file"].'.'.$file["extension"];
	$original_filename='../'.$full_filename;

	if ( !isset($_POST["id_imageformat"]) )
	{
		echo '<ImageImageformatAdd>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bildformat-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Bildformat-ID (id_imageformat) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ImageImageformatAdd>'."\n";
		exit;
	}
	$results=q("SELECT * FROM cms_imageformats WHERE id_imageformat=".$_POST["id_imageformat"].";", $dbweb, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<ImageImageformatAdd>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bildformat nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Unter der angegebenen Bildformat-ID ('.$_POST["id_imageformat"].') existiert kein Bildformat.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ImageImageformatAdd>'."\n";
		exit;
	}
	$imageformat=mysqli_fetch_array($results);
	
	//add file for imageformat
	q("INSERT INTO cms_files (filename, extension, filesize, description, imageformat_id, original_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".$filename."', '".$extension."', ".$filesize.", '', ".$_POST["id_imageformat"].", ".$_POST["id_file"].", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
	$file_id=mysqli_insert_id($dbweb);
	$dir=floor(bcdiv($file_id, 1000));
	if (!file_exists("../files/".$dir)) mkdir("../files/".$dir);
	$full_filename='../files/'.$dir.'/'.$file_id.'.'.$extension;

	//render zoomed if set
	if ($imageformat["zoom"]!=100)
	{
		$zf=$imageformat["zoom"]/100;
		$phpThumb = new phpThumb();
		$phpThumb->setSourceFilename($original_filename);
		$phpThumb->setParameter('q', 95); //quality / compression
		if ($imageformat["width"]>0) $phpThumb->w = $imageformat["width"]*$zf;
		if ($imageformat["height"]>0) $phpThumb->h = $imageformat["height"]*$zf;
		if ($imageformat["zc"]==1) $phpThumb->zc = $imageformat["zc"]; //zoom and crop
		if ($imageformat["aoe"]==1) $phpThumb->aoe = $imageformat["aoe"]; //enlargen smaller images
		$phpThumb->config_output_format = 'jpeg';
		$phpThumb->config_error_die_on_error = false;
		if (@$phpThumb->GenerateThumbnail())
		{
			if (!$phpThumb->RenderToFile('temp.jpg'))
			{
				echo '<ImageImageformatAdd>'."\n";
				echo '	<Ack>Error</Ack>'."\n";
				echo '	<Error>'."\n";
				echo '		<Code>'.__LINE__.'</Code>'."\n";
				echo '		<shortMsg>phpThumb-Fehler aufgetreten.</shortMsg>'."\n";
				echo '		<longMsg>Es ist ein Fehler beim Ausführen von phpThumb aufgetreten.</longMsg>'."\n";
				echo '	</Error>'."\n";
				echo '	<Response><![CDATA['.implode("\n", $phpThumb->debugmessages).']]></Response>'."\n";
				echo '</ImageImageformatAdd>'."\n";
				exit;
			}
		}
		else
		{
			echo '<ImageImageformatAdd>'."\n";
			echo '	<Ack>Error</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>phpThumb-Fehler aufgetreten.</shortMsg>'."\n";
			echo '		<longMsg>Es ist ein Fehler beim Ausführen von phpThumb aufgetreten.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<Response><![CDATA['.implode("\n", $phpThumb->debugmessages).']]></Response>'."\n";
			echo '</ImageImageformatAdd>'."\n";
			exit;
		}
		$original_filename="temp.jpg";
	}
	
	if($imageformat["background_image"]!="")
	{
		//render background image if set
		$phpThumb = new phpThumb();
		$phpThumb->setSourceFilename('../../'.$imageformat["background_image"]);
		$phpThumb->setParameter('q', 95); //quality / compression
		if ($imageformat["width"]>0) $phpThumb->w = $imageformat["width"];
		if ($imageformat["height"]>0) $phpThumb->h = $imageformat["height"];
		if ($imageformat["zc"]==1) $phpThumb->zc = $imageformat["zc"]; //zoom and crop
		if ($imageformat["aoe"]==1) $phpThumb->aoe = $imageformat["aoe"]; //enlargen smaller images
		if ($imageformat["watermark"]!="") $phpThumb->setParameter('fltr', 'wmi|'.$original_filename.'|C|100');
		if ($imageformat["watermark"]!="") $phpThumb->setParameter('fltr', 'wmi|../../'.$imageformat["watermark"].'|'.$imageformat["watermark_position"].'|'.$imageformat["watermark_opacity"].'');
		$phpThumb->config_output_format = 'jpeg';
		$phpThumb->config_error_die_on_error = false;
		if (@$phpThumb->GenerateThumbnail())
		{
			if (!$phpThumb->RenderToFile('../'.$full_filename))
			{
				echo '<ImageImageformatAdd>'."\n";
				echo '	<Ack>Error</Ack>'."\n";
				echo '	<Error>'."\n";
				echo '		<Code>'.__LINE__.'</Code>'."\n";
				echo '		<shortMsg>phpThumb-Fehler aufgetreten.</shortMsg>'."\n";
				echo '		<longMsg>Es ist ein Fehler beim Ausführen von phpThumb aufgetreten.</longMsg>'."\n";
				echo '	</Error>'."\n";
				echo '	<Response><![CDATA['.implode("\n", $phpThumb->debugmessages).']]></Response>'."\n";
				echo '</ImageImageformatAdd>'."\n";
				exit;
			}
		}
		else
		{
			echo '<ImageImageformatAdd>'."\n";
			echo '	<Ack>Error</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>phpThumb-Fehler aufgetreten.</shortMsg>'."\n";
			echo '		<longMsg>Es ist ein Fehler beim Ausführen von phpThumb aufgetreten.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<Response><![CDATA['.implode("\n", $phpThumb->debugmessages).']]></Response>'."\n";
			echo '</ImageImageformatAdd>'."\n";
			exit;
		}
	}
	else
	{
		//render image
		$phpThumb = new phpThumb();
		$phpThumb->setSourceFilename($original_filename);
		$phpThumb->setParameter('q', 95); //quality / compression
		if ($imageformat["width"]>0) $phpThumb->w = $imageformat["width"];
		if ($imageformat["height"]>0) $phpThumb->h = $imageformat["height"];
		if ($imageformat["zc"]==1) $phpThumb->zc = $imageformat["zc"]; //zoom and crop
		if ($imageformat["aoe"]==1) $phpThumb->aoe = $imageformat["aoe"]; //enlargen smaller images
		if ($imageformat["watermark"]!="") $phpThumb->setParameter('fltr', 'wmi|../../'.$imageformat["watermark"].'|'.$imageformat["watermark_position"].'|'.$imageformat["watermark_opacity"].'');
		$phpThumb->config_output_format = 'jpeg';
		$phpThumb->config_error_die_on_error = false;
		if (@$phpThumb->GenerateThumbnail())
		{
			if (!$phpThumb->RenderToFile('../'.$full_filename))
			{
				echo '<ImageImageformatAdd>'."\n";
				echo '	<Ack>Error</Ack>'."\n";
				echo '	<Error>'."\n";
				echo '		<Code>'.__LINE__.'</Code>'."\n";
				echo '		<shortMsg>phpThumb-Fehler aufgetreten.</shortMsg>'."\n";
				echo '		<longMsg>Es ist ein Fehler beim Ausführen von phpThumb aufgetreten.</longMsg>'."\n";
				echo '	</Error>'."\n";
				echo '	<Response><![CDATA['.implode("\n", $phpThumb->debugmessages).']]></Response>'."\n";
				echo '</ImageImageformatAdd>'."\n";
				exit;
			}
		}
		else
		{
			echo '<ImageImageformatAdd>'."\n";
			echo '	<Ack>Error</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>phpThumb-Fehler aufgetreten.</shortMsg>'."\n";
			echo '		<longMsg>Es ist ein Fehler beim Ausführen von phpThumb aufgetreten.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '	<Response><![CDATA['.implode("\n", $phpThumb->debugmessages).']]></Response>'."\n";
			echo '</ImageImageformatAdd>'."\n";
			exit;
		}
	}
	$filesize=filesize($full_filename);
	q("UPDATE cms_files SET filesize=".$filesize." WHERE id_file=".$file_id.";", $dbweb, __FILE__, __LINE__);

	//remove tempfile
	if ($original_filename=="temp.jpg") unlink("../modules/phpThumb/temp.jpg");

	echo '<ImageImageformatAdd>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<id_file>'.$file_id.'</id_file>'."\n";
	echo '</ImageImageformatAdd>'."\n";

?>