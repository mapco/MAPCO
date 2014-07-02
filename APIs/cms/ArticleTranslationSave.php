<?php
	/*************************
	********** SOA 2 *********
	*************************/
	
	$required=array("translation_id"	=> "numeric", "article_id"	=> "numeric", "title"	=> "text", "introduction"	=> "text", "lang"	=> "numeric", "text"	=> "text");
	check_man_params($required);
	
	$xml = '';
	
	$data["article_id"] = $_POST["article_id"];
	$data['meta_title'] = $_POST['meta_title'];
	$data['meta_keywords'] = $_POST["meta_keywords"];
	$data['meta_description'] = $_POST["meta_description"];
	$data['title'] = $_POST['title'];
	$data['introduction'] = $_POST["introduction"];
	$data['article'] = $_POST["text"];
	$data['lastmod'] = time();
	$data['lastmod_user'] = $_SESSION["id_user"];

	$result = q("SELECT format, imageprofile_id, ordering, newsletter, site_id FROM cms_articles WHERE id_article=".$data["article_id"].";", $dbweb, __FILE__, __LINE__);
	$row= mysqli_fetch_assoc($result);
	foreach($row as $key => $value)
	{
		$data[$key] = $value;	
	}

	if ( ($_POST["translation_id"] == 0) )
	{	
		$data['published'] = 0;
		
		$data['language_id'] = $_POST['lang'];
		$data['firstmod'] = time();
		$data['firstmod_user'] = $_SESSION["id_user"];
			
		$result = q("SELECT meta_title, meta_keywords, meta_description, introduction, title, article FROM cms_articles WHERE id_article=".$data["article_id"].";", $dbweb, __FILE__, __LINE__);
		$row= mysqli_fetch_assoc($result);
	
		$data['meta_title'] = $row['meta_title'];
		$data['meta_keywords'] = $row["meta_keywords"];
		$data['meta_description'] = $row["meta_description"];
		$data['title'] = $row['title'];
		$data['introduction'] = $row["introduction"];
		$data['article'] = $row["article"];
			
		q_insert('cms_articles', $data, $dbweb, __FILE__, __LINE__);
		$xml .= '<insert_id>'.mysqli_insert_id($dbweb).'</insert_id>';
		$xml .= '<Error>Artikel erfolgreich gespeichert.</Error>'."\n";
		
		
		
		foreach ( $row as $key => $value )
		{
			if ( !is_numeric($value) )
			{
				$value = "<![CDATA[".$value."]]>";	
			}
			$xml .= '	<'.$key.'>'.$value.'</'.$key.'>'."\n";
		}
	}
	else
	{ 		
		$data['published'] = $_POST["published"];
		
		$where = 'WHERE `id_article`= '.$_POST["translation_id"];
		$result = q_update('cms_articles', $data, $where, $dbweb, __FILE__, __LINE__);
		
		if(mysqli_affected_rows($dbweb) == 1)
		{
			$xml .= '<Error>Artikel erfolgreich gespeichert.</Error>'."\n";
		}
		elseif(mysqli_affected_rows($dbweb)>1)
		{
			$xml .= '<Error>Es wurde mehr als ein Artikel geändert!</Error>'."\n";
		}
		else
		{
			$xml .= '<Error>Da ist ein Fehler aufgetreten!</Error>'."\n";
		}
	}
	print $xml;
?>