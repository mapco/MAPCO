<?php
	$starttime=time()+microtime();
	include("../config.php");

	//create filelist
	$items=array();
	$results=q("SELECT article_id, MPN FROM shop_items WHERE active>0;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$items[$row["article_id"]]=$row["MPN"];
	}

	echo 'starte bei: ';
	if (isset($_GET["start"]) and $_GET["start"]!="") echo $start = $_GET["start"];
	else echo $start = 0;
	echo '<br />';
	
	$files=array();
	$results=q("SELECT original_id, id_file, extension FROM cms_files WHERE imageformat_id=19 order by id_file;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$files[$row["original_id"]]=$row;
	}

	$alphabet=array("a", "b", "c", "d", "e", "f", "g", "h");
	$id_article=0;
	$results=q("SELECT article_id, file_id FROM cms_articles_images ORDER BY article_id, ordering;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if( $id_article != $row["article_id"] )
		{
			$i=0;
			$id_article=$row["article_id"];
		}
		if( isset($items[$row["article_id"]]) )
		{
			if( isset($files[$row["file_id"]]) )
			{
				$row2=$files[$row["file_id"]];
				$dir=floor($row2["id_file"] / 1000);
				$name=$items[$row["article_id"]].$alphabet[$i];
				$i++;
				$name=str_replace("/", "_", $name);
				$file[]='../files/'.$dir.'/'.$row2["id_file"].'.'.$row2["extension"];
				$file_artnr[]=$name.'.jpg';
			}
		}
	}

	if ( isset($_GET["start"]) and $_GET["start"]!="") $start=$_GET["start"];
	else $start=0;
	$end=$start+10000;
	$zipfile="mapco_images_ori.zip";
	$handle=fopen($zipfile, "w");
	fclose($handle);
	$zip = new ZipArchive;
	if ($zip->open($zipfile) === TRUE)
	{
		for($i=$start; $i<$end; $i++)
		{
			if (isset($file[$i]) and $file[$i]!="")
			{
				if ($zip->addFile($file[$i], $file_artnr[$i]))
				{
					echo 'Datei "'.$file_artnr[$i].'" in Archiv hinzugefügt.<br />';
				}
				else die('<p>Fehler beim Hinzufügen der Datei "'.$file[$i].'".</p>');
			}
		}
		$zip->close();
	}
	else echo '<p>Fehler beim Öffnen des ZIP-Archivs.</p>';
	$stoptime=time()+microtime();
	echo ($stoptime-$starttime);
?>