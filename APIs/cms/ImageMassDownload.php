<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["id_file"]) )
	{
		echo '<ImageMassDownloadResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Datei-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Datei-ID übermittelt werden, damit der Service weiß, welche Abbildung exportiert werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ImageMassDownloadResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["filename"]) )
	{
		echo '<ImageMassDownloadResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Dateiname nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Dateiname übermittelt werden, damit der Service weiß, wie die Datei beim Export benannt werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ImageMassDownloadResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["format"]) )
	{
		echo '<ImageMassDownloadResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Format nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Format übermittelt werden, damit der Service weiß, in welchem Format die Fotos exportiert werden sollen.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ImageMassDownloadResponse>'."\n";
		exit;
	}

	//id_file to path
	$results=q("SELECT * FROM cms_files WHERE id_file=".$_POST["id_file"].";", $dbweb, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<ImageMassDownloadResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Datei nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Die angegebene Datei-ID verweist auf keine gültige Datei.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ImageMassDownloadResponse>'."\n";
		exit;
	}
	$row=mysqli_fetch_array($results);
	$full_filename='files/'.floor(bcdiv($row["id_file"], 1000)).'/'.$row["id_file"].'.'.$row["extension"];


	//convert to TecDoc-BMP
	if($_POST["format"]==1)
	{
		require_once('../modules/phpThumb/phpthumb.class.php');
		$_POST["filename"]=str_replace(".jpg", ".bmp", $_POST["filename"]);
		$phpThumb = new phpThumb();
		$phpThumb->setSourceFilename('../../'.$full_filename);
		$phpThumb->setParameter('q', 95); //quality / compression
		$phpThumb->w = 600;
		$phpThumb->h = 400;
		$phpThumb->zc = 0; //zoom and crop
		$phpThumb->aoe = 1; //enlargen smaller images
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


		$file="temp.tmp";
		$_POST["filename"]=str_replace(".jpg", ".bmp", $_POST["filename"]);
		$phpThumb = new phpThumb();
		$phpThumb->setSourceFilename('../../images/library/tecdoc_export.bmp');
		$phpThumb->setParameter('fltr', 'wmi|temp.jpg|C|100');
		$phpThumb->setParameter('fltr', 'wmi|../../images/library/watermark.jpg|C|15');
		$phpThumb->config_output_format = 'bmp';
		$phpThumb->config_error_die_on_error = false;
		if ($phpThumb->GenerateThumbnail())
		{
			if (!$phpThumb->RenderToFile('../../'.$file))
			{
				echo '<ImageMassDownloadResponse>'."\n";
				echo '	<Ack>Failure</Ack>'."\n";
				echo '	<Error>'."\n";
				echo '		<Code>'.__LINE__.'</Code>'."\n";
				echo '		<shortMsg>Fehler beim Speichern.</shortMsg>'."\n";
				echo '		<longMsg>Beim Speichern eines konvertierten Bildes ist ein Fehler aufgetreten.</longMsg>'."\n";
				echo '		<Response><![CDATA['.print_r($phpThumb->debugmessages, true).']]></Response>'."\n";
				echo '	</Error>'."\n";
				echo '</ImageMassDownloadResponse>'."\n";
				exit;
//				error(__FILE__, __LINE__, print_r($phpThumb->debugmessages, true));
			}
		}
		else
		{
			echo '<ImageMassDownloadResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Fehler bei der Bildkonvertierung.</shortMsg>'."\n";
			echo '		<longMsg>Bei der Konvertierung einer Abbildung ist ein Fehler aufgetreten.</longMsg>'."\n";
			echo '		<Response><![CDATA['.print_r($phpThumb->debugmessages, true).']]></Response>'."\n";
			echo '	</Error>'."\n";
			echo '</ImageMassDownloadResponse>'."\n";
			exit;
//			error(__FILE__, __LINE__, print_r($phpThumb->debugmessages, true));
		}
	}
	//convert to IDIMS-BMP
	elseif($_POST["format"]==2)
	{
		require_once('../modules/phpThumb/phpthumb.class.php');
		$_POST["filename"]=str_replace(".jpg", ".bmp", $_POST["filename"]);
		$phpThumb = new phpThumb();
		$phpThumb->setSourceFilename('../../'.$full_filename);
		$phpThumb->setParameter('q', 95); //quality / compression
		$phpThumb->w = 250;
		$phpThumb->h = 130;
		$phpThumb->zc = 0; //zoom and crop
		$phpThumb->aoe = 1; //enlargen smaller images
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


		$file="temp.tmp";
		$phpThumb = new phpThumb();
		$phpThumb->setSourceFilename('../../images/library/idims_export.bmp');
		$phpThumb->setParameter('fltr', 'wmi|temp.jpg|C|100');
		$phpThumb->config_output_format = 'bmp';
		$phpThumb->config_error_die_on_error = false;
		if ($phpThumb->GenerateThumbnail())
		{
			if (!$phpThumb->RenderToFile('../../'.$file))
			{
				echo '<ImageMassDownloadResponse>'."\n";
				echo '	<Ack>Failure</Ack>'."\n";
				echo '	<Error>'."\n";
				echo '		<Code>'.__LINE__.'</Code>'."\n";
				echo '		<shortMsg>Fehler beim Speichern.</shortMsg>'."\n";
				echo '		<longMsg>Beim Speichern eines konvertierten Bildes ist ein Fehler aufgetreten.</longMsg>'."\n";
				echo '		<Response><![CDATA['.print_r($phpThumb->debugmessages, true).']]></Response>'."\n";
				echo '	</Error>'."\n";
				echo '</ImageMassDownloadResponse>'."\n";
				exit;
//				error(__FILE__, __LINE__, print_r($phpThumb->debugmessages, true));
			}
		}
		else
		{
			echo '<ImageMassDownloadResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Fehler bei der Bildkonvertierung.</shortMsg>'."\n";
			echo '		<longMsg>Bei der Konvertierung einer Abbildung ist ein Fehler aufgetreten.</longMsg>'."\n";
			echo '		<Response><![CDATA['.print_r($phpThumb->debugmessages, true).']]></Response>'."\n";
			echo '	</Error>'."\n";
			echo '</ImageMassDownloadResponse>'."\n";
			exit;
//			error(__FILE__, __LINE__, print_r($phpThumb->debugmessages, true));
		}
	}
	//export original
	else $file=$full_filename;
	

	//add file to zip-archive
	$zip = new ZipArchive;
	if ($zip->open($_POST["exportfile"]) === TRUE)
	{
		if ($zip->addFile('../'.$file, $_POST["filename"]))
		{
			echo '<ImageMassDownloadResponse>'."\n";
			echo '	<Ack>Success</Ack>'."\n";
			$stoptime = time()+microtime();
			$time = $stoptime-$starttime;
			echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
			echo '</ImageMassDownloadResponse>'."\n";
		}
		else
		{
			echo '<ImageMassDownloadResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Hinzufügen zum Archiv fehlgeschlagen.</shortMsg>'."\n";
			echo '		<longMsg>Die Datei '.$file.' konnte dem ZIP-Archiv '.$exportfile.' nicht hinzugefügt werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</ImageMassDownloadResponse>'."\n";
			exit;
		}
	}
	else
	{
		echo '<ImageMassDownloadResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>ZIP-Archiv nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Das ZIP-Archiv '.$exportfile.' konnte nicht geöffnet werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ImageMassDownloadResponse>'."\n";
		exit;
	}

?>