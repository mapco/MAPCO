<?php
	/*************************
	********** SOA 2 *********
	***** Author Sven E. *****
	*** Lastmod 25.03.2014 ***
	*************************/
	
	$required=array("file_id"	=> "numeric", "type"	=>	"text" );
	check_man_params($required);

	$xml = '';
	$file_id = $_POST["file_id"];

	switch($_POST['type'])
	{
		case "file": $table = "cms_articles_files";
			$message = '<Message>Datei erfolgreich gelöscht.</Message>';
			$error = '<Message>Beim Löschen der Datei ist ein Fehler aufgetreten.</Message>';
			break;
		case "image": $table = "cms_articles_images";
			$message = '<Message>Bild erfolgreich gelöscht.</Message>';
			$error = '<Message>Beim Löschen des Bildes ist ein Fehler aufgetreten.</Message>';
			break;
		case "video": $table = "cms_articles_videos";
			$message = '<Message>Video erfolgreich gelöscht.</Message>';
			$error = '<Message>Beim Löschen des Videos ist ein Fehler aufgetreten.</Message>';
			break;
	}

	$result = q("SELECT id, article_id FROM ".$table." WHERE file_id=".$file_id.";", $dbweb, __FILE__, __LINE__);
	if (mysqli_affected_rows($dbweb) == 1)
	{ 
		$row=mysqli_fetch_assoc($result);
		$article_id=$row["article_id"];
		$query="DELETE FROM ".$table." WHERE file_id=".$file_id.";";
		q($query, $dbweb, __FILE__, __LINE__);
		$query="SELECT * FROM cms_files WHERE id_file=".$file_id.";";
		$results=q($query, $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$dir=floor(bcdiv($file_id, 1000));
		$filename='../files/'.$dir.'/'.$file_id.'.'.$row["extension"];
		unlink($filename);
		$query="DELETE FROM cms_files WHERE id_file=".$file_id.";";
		q($query, $dbweb, __FILE__, __LINE__);
		$xml .=	$message;
			
		if ($_POST['type'] == 'image')
		{
			$result = q("SELECT id_file, extension FROM cms_files WHERE original_id=".$file_id.";", $dbweb, __FILE__, __LINE__);
			while($row=mysqli_fetch_assoc($result))
			{ 
				$query="DELETE FROM cms_articles_images WHERE file_id=".$row['id_file'].";";
				q($query, $dbweb, __FILE__, __LINE__);
				
				$dir=floor(bcdiv($row['id_file'], 1000));
				$filename='../files/'.$dir.'/'.$row['id_file'].'.'.$row["extension"];
				unlink($filename);
				$query="DELETE FROM cms_files WHERE id_file=".$row['id_file'].";";
				q($query, $dbweb, __FILE__, __LINE__);
			}
		}
		
		//reset ordering
		$i=1;
		$results=q("SELECT * FROM ".$table." WHERE article_id=".$article_id." ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
		while( $row=mysqli_fetch_assoc($results) )
		{
			q("UPDATE ".$table." SET ordering=".$i." WHERE id=".$row["id"].";", $dbweb, __FILE__, __LINE__);
			$i++;
		}
	}
	else
	{
		$xml .=	$error;
	}
	print $xml;
?>