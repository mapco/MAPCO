<?php
//	$starttime = time()+microtime();

	if ( !isset($_POST["id_file"]) )
	{
		echo '<EndItemResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Datei-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Datei-ID übermittelt werden, damit der Service weiß, zu welcher Datei ein Vorschaubild angezeigt werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</EndItemResponse>'."\n";
		exit;
	}

	require_once('../modules/phpThumb/phpthumb.class.php');

	$results=q("SELECT * FROM cms_files WHERE id_file='".$_POST["id_file"]."';", $dbweb, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo PATH.'files_thumbnail/0.jpg';
		exit;
	}
	$row=mysqli_fetch_array($results);
	$source='files/'.floor(bcdiv($_POST["id_file"], 1000)).'/'.$_POST["id_file"].'.'.$row["extension"];
	$destination='files_thumbnail/'.floor(bcdiv($_POST["id_file"], 1000)).'/'.$_POST["id_file"].'.'.$row["extension"];
	if ( file_exists('../'.$destination) )
	{
		echo PATH.$destination;
		exit;
	}
	elseif( !file_exists('../'.$source) )
	{
		echo PATH.'files_thumbnail/0.jpg';
		exit;
	}
	else
	{
		$dir=floor(bcdiv($_POST["id_file"], 1000));
		if ( !file_exists("../files_thumbnail/".$dir) )	mkdir("../files_thumbnail/".$dir);
		$phpThumb = new phpThumb();
		$phpThumb->setSourceFilename("../../".$source);
		$phpThumb->setParameter('w', 120); //width
		$phpThumb->setParameter('h', 80); //height
		$phpThumb->setParameter('q', 60); //quality / compression
		$phpThumb->setParameter('far', "C"); //force aspect ratio (needs bg!!!)
		$phpThumb->setParameter('bg', "ffffff"); //background color
		$phpThumb->config_output_format = 'jpeg';
		$phpThumb->config_error_die_on_error = false;
		if (@$phpThumb->GenerateThumbnail())
		{
			if (!$phpThumb->RenderToFile("../../".$destination))
			{
				error(__FILE__, __LINE__, print_r($phpThumb->debugmessages, true));
			}
		}
		else
		{
			error(__FILE__, __LINE__, print_r($phpThumb->debugmessages, true));
		}
	}
	echo PATH.$destination;
//	if ( $_SESSION["id_user"]==21371 ) print_r($phpThumb->debugmessages);
//	$stoptime = time()+microtime();
//	echo $time = number_format($stoptime-$starttime, 2);

?>