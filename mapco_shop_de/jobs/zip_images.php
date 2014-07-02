<?php
	$starttime=time()+microtime();
	include("../config.php");

	//create filelist
	$file=array();
	$file_id=array();
	$results=q("SELECT * FROM shop_items WHERE active>0;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) ) 
	{
		$results2=q("SELECT * FROM cms_articles_images WHERE article_id=".$row["article_id"].";", $dbweb, __FILE__, __LINE__);
		while( $row2=mysqli_fetch_array($results2) ) 
		{
			$results3=q("SELECT * FROM cms_files WHERE original_id=".$row2["file_id"]." AND imageformat_id=9;", $dbweb, __FILE__, __LINE__);
			$row3=mysqli_fetch_array($results3);
			$file[]='../files/'.bcdiv($row3["id_file"], 1000, 0).'/'.$row3["id_file"].'.'.$row3["extension"];
			$file_artnr[]=$row["MPN"];
		}		
	}

	$last="";
	$j=0;
	$alphabet=array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r");
	array_multisort($file_artnr, $file);
	for($i=0; $i<sizeof($file); $i++)
	{
		echo '<br />';
		if ($last!=$file_artnr[$i])
		{
			$last=$file_artnr[$i];
			$j=0;
		}
		else $j++;
		echo $file[$i];
		echo '|';
		echo $filename[$i]=str_replace("/", "_", $file_artnr[$i]).$alphabet[$j].'.jpg';
	}

/*
	$results=q("SELECT * FROM cms_files WHERE original_id>0;", $dbweb, __FILE__, __LINE__);
	if (mysqli_num_rows($results)>0)
	{
		while($row=mysqli_fetch_array($results))
		{
			$results2=q("SELECT * FROM shop_items_files WHERE file_id=".$row["id_file"]." LIMIT 1;", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($results2)>0)
			{
				$row2=mysqli_fetch_array($results2);
				$results3=q("SELECT * FROM shop_items WHERE id_item=".$row2["item_id"]." LIMIT 1;", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($results3)>0)
				{
					$row3=mysqli_fetch_array($results3);
					if ($_GET["format"]==1) $file[]='../files/'.bcdiv($row["id_file"], 1000, 0).'/'.$row["id_file"].'.jpg';
					else $file[]='../files/'.bcdiv($row["id_file"], 1000, 0).'/'.$row["id_file"].'.jpg';
					$file_artnr[]=$row3["MPN"];
				}
			}
		}
		$last="";
		$j=0;
		$alphabet=array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r");
		array_multisort($file_artnr, $file);
		for($i=0; $i<sizeof($file); $i++)
		{
			echo '<br />';
			if ($last!=$file_artnr[$i])
			{
				$last=$file_artnr[$i];
				$j=0;
			}
			else $j++;
			echo $file[$i];
			echo '|';
			echo $filename[$i]=str_replace("/", "_", $file_artnr[$i]).$alphabet[$j].'.jpg';
		}
	}
*/
	$zipfile="../mapco_images.zip";
	$handle=fopen($zipfile, "w");
	fclose($handle);
	$zip = new ZipArchive;
	if ($zip->open($zipfile) === TRUE)
	{
		for($i=0; $i<sizeof($file); $i++)
		{
			if ($zip->addFile($file[$i], $filename[$i]))
			{
				echo 'Datei "'.$filename[$i].'" in Archiv hinzugefügt.<br />';
			}
			else die('<p>Fehler beim Hinzufügen der Datei "'.$file[$i].'".</p>');
		}
	}
	else echo '<p>Fehler beim Öffnen des ZIP-Archivs.</p>';
	
	$stoptime=time()+microtime();
	echo ($stoptime-$starttime);
?>