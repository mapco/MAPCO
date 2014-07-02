<?php
		   	include("config.php");
			$dir=bcdiv($_GET["original_id"], 1000, 0);
			if (!file_exists("files/".$dir)) mkdir("files/".$dir);
			$druck_filename='files/'.$dir.'/'.$_GET["original_id"].'.jpg';

			
			q("INSERT INTO cms_files (filename, extension, filesize, description, original_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".$_GET["filename"]."', 'jpg', 0, '', ".$_GET["original_id"].", ".time().", 0, ".time().", 0);", $dbweb, __FILE__, __LINE__);
			$file_id=mysqli_insert_id($dbweb);
			$dir=bcdiv($file_id, 1000, 0);
			if (!file_exists("files/".$dir)) mkdir("files/".$dir);
			$web_filename='files/'.$dir.'/'.$file_id.'.jpg';
			require_once('modules/phpThumb/phpthumb.class.php');
			$phpThumb = new phpThumb();
			$phpThumb->setSourceFilename('../'.$druck_filename);
			$phpThumb->w = 540;
			$phpThumb->h = 380;
			$phpThumb->aoe = 1; //vergrφίere kleinere fotos
			$phpThumb->config_output_format = 'jpeg';
			$phpThumb->config_error_die_on_error = false;
			if ($phpThumb->GenerateThumbnail())
			{
				if (!$phpThumb->RenderToFile("../ebay_tmp.jpg"))
				{
					echo 'ERROR: '.implode("\n", $phpThumb->debugmessages);
				}
			}
			else
			{
				echo 'ERROR: '.implode("\n", $phpThumb->debugmessages);
			}
			
			$phpThumb = new phpThumb();
			$phpThumb->setSourceFilename("../images/library/rahmen_bg.jpg");
			$phpThumb->w = 600;
			$phpThumb->h = 400;
			$phpThumb->aoe = 1; //vergrφίere kleinere fotos
			$phpThumb->setParameter('fltr', 'wmi|../ebay_tmp.jpg|C|100');
			$phpThumb->setParameter('fltr', 'wmi|../images/library/rahmen.png|C|100');
			$phpThumb->config_output_format = 'jpeg';
			$phpThumb->config_error_die_on_error = false;
			if ($phpThumb->GenerateThumbnail())
			{
				if (!$phpThumb->RenderToFile(../$web_filename))
				{
					echo 'ERROR: '.implode("\n", $phpThumb->debugmessages);
				}
			}
			else
			{
				echo 'ERROR: '.implode("\n", $phpThumb->debugmessages);
			}
			q("UPDATE cms_files SET filesize=".filesize($web_filename)." WHERE id_file=".$file_id.";", $dbweb, __FILE__, __LINE__);
			q("INSERT INTO cms_files_labels (file_id, label_id) VALUES(".$file_id.", 8);", $dbweb, __FILE__, __LINE__);
?>