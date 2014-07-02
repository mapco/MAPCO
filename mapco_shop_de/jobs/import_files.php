<?php
	include("../config.php");

	//read already imported
	$imported=array();
	$results=q("SELECT * FROM cms_files;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$imported[$row["filename"].'.'.$row["extension"]]=1;
	}
	
	//truncate tables
//	q("TRUNCATE TABLE cms_files;", $dbweb, __FILE__, __LINE__);
//	q("TRUNCATE TABLE shop_items_files;", $dbshop, __FILE__, __LINE__);
	
	$i=0;
	$dir="../fotos57/abbildungen/druck/";
	$handle=opendir($dir);
	while ( (false !== ($file = readdir($handle))) and ($i<20))
	{
		if ($file!="." and $file!=".." and !is_dir($dir.$file) and (!isset($imported[$file])))
		{
//			if ($i==20) exit();
			$artnr=substr($file, 0, strlen($file)-5);
			$artnr=str_replace("_", "/", $artnr);
			$results=q("SELECT * FROM shop_items WHERE MPN='".$artnr."';", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($results)>0)
			{
				$i++;
				$row=mysqli_fetch_array($results);
				$item_id=$row["id_item"];

				$filename=substr($file, 0, strrpos($file, "."));
				
				//save to db
				$extension=substr($file, strrpos($file, ".")+1, strlen($file));
				$query="INSERT INTO cms_files (filename, extension, filesize, description, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".$filename."', '".$extension."', ".filesize($dir.$file).", '', '".filemtime($dir.$file)."', 0, ".time().", 0);";
				q($query, $dbweb, __FILE__, __LINE__);
				$id_file=mysqli_insert_id($dbweb);
				
				//copy file
				$destdir='../'.'files/'.floor(bcdiv($id_file, 1000)).'/';
				if (!file_exists($destdir))
				{
					mkdir($destdir, 0, true);
					chmod($destdir, 0777);
				}
				copy($dir.$file, $destdir.$id_file.".".$extension);


				//create 600x400 version for webshop
				$image_filename=$destdir.$id_file.".".$extension;
				require_once('../modules/phpThumb/phpthumb.class.php');
				$phpThumb = new phpThumb();
				$phpThumb->setSourceFilename('../'.$image_filename);
				$phpThumb->w = 600;
				$phpThumb->h = 400;
				$phpThumb->aoe = 1; //vergrößere kleinere fotos
				$phpThumb->setParameter('fltr', 'wmi|../../fotos57/wasserzeichen_tecdoc.jpg|C|20');
				$phpThumb->config_output_format = 'jpeg';
				$phpThumb->config_error_die_on_error = false;
				if ($phpThumb->GenerateThumbnail())
				{
					$query="INSERT INTO cms_files (filename, extension, filesize, description, original_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".$filename."', '".$extension."', ".filesize($dir.$file).", '', ".$id_file.", '".filemtime($dir.$file)."', 0, ".time().", 0);";
					q($query, $dbweb, __FILE__, __LINE__);
					$file_id=mysqli_insert_id($dbweb);
					$query="INSERT INTO shop_items_files (item_id, file_id) VALUES(".$item_id.", ".$file_id.");";
					q($query, $dbshop, __FILE__, __LINE__);
					$destdir=floor(bcdiv($file_id, 1000));
					if (!file_exists("../files/".$destdir)) mkdir("../files/".$destdir);
					$filename='../files/'.$destdir.'/'.$file_id.'.'.$extension;
					if (!$phpThumb->RenderToFile('../'.$filename))
					{
						echo 'Failed: '.implode("\n", $phpThumb->debugmessages);
					}
					$query="UPDATE cms_files SET filesize=".filesize($filename)." WHERE id_file=".$file_id.";";
					q($query, $dbweb, __FILE__, __LINE__);
				}
				else
				{
					echo 'Failed: '.implode("\n", $phpThumb->debugmessages);
				}


				//link to item
//				q("INSERT INTO shop_items_files (item_id, file_id) VALUES(".$item_id.", ".$id_file.");", $dbshop, __FILE__, __LINE__);

				echo $artnr.' wurde importiert.<br />';
			}
			else echo $artnr.' wurde nicht gefunden!<br />';
		}
	}
	
//	header("Location: import_files.php");
	echo 'Ende';

?>