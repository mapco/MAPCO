<?php

	if ( !isset($_POST["zipfile"]) )
	{
		echo '<ZipFileAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>ZIP-Archivname nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein ZIP-Archivname (zipfile) übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ZipFileAdd>'."\n";
		exit;
	}

	if ( !isset($_POST["file"]) )
	{
		echo '<ZipFileAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Datei nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Datei (file) übergeben werden, die der ZIP-Datei hinzugefügt werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ZipFileAdd>'."\n";
		exit;
	}

	if ( !isset($_POST["filename"]) )
	{
		echo '<ZipFileAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Dateiname nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Dateiname (filename) für die zu archivierende Datei übergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ZipFileAdd>'."\n";
		exit;
	}


	//create file if not exists
	if( !file_exists($_POST["zipfile"]) )
	{
		$handle=fopen($_POST["zipfile"], "w");
		fclose($handle);
	}

	//add file to zip
	$zip = new ZipArchive;
	if ($zip->open($zipfile) === TRUE)
	{
		$files=explode(", ", $_POST["file"]);
		$filenames=explode(", ", $_POST["filename"]);
		for($i=0; $i<sizeof($files); $i++)
		{
			if ($zip->addFile('../'.$files[$i], $filenames[$i]))
			{
			}
			else
			{
				echo '<ZipFileAdd>'."\n";
				echo '	<Ack>Failure</Ack>'."\n";
				echo '	<Error>'."\n";
				echo '		<Code>'.__LINE__.'</Code>'."\n";
				echo '		<shortMsg>Datei nicht hinzugefügt.</shortMsg>'."\n";
				echo '		<longMsg>Die Datei konnte zum ZIP-Archiv nicht hinzugefügt werden.</longMsg>'."\n";
				echo '	</Error>'."\n";
				echo '</ZipFileAdd>'."\n";
				exit;
			}
		}
		$zip->close();
		echo '<ZipFileAdd>'."\n";
		echo '	<Ack>Success</Ack>'."\n";
		echo '</ZipFileAdd>'."\n";
		exit;
	}
	else
	{
		echo '<ZipFileAdd>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>ZIP-Archiv nicht geöffnet.</shortMsg>'."\n";
		echo '		<longMsg>Das ZIP-Archiv konnte nicht erfolgreich geöffnet werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ZipFileAdd>'."\n";
		exit;
	}

?>