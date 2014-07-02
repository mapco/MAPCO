<?php
	/*************************
	********** SOA 2 *********
	******Author Sven E.******
	****Lastmod 25.03.2014****
	*************************/
	
	$required=array("id_article"	=> "numeric", "todo"	=> "text");
	check_man_params($required);
	
	$xml = '';
	
	if ( ($_POST["id_article"] == '') ) $xml .= '<Error>Es konnte keine ID für den Artikel gefunden werden.</Error>'."\n";
	else
	{
		switch($_POST['todo'])
		{
			case "save":	$required=array("title"	=> "text", "introduction"	=> "text", "text"	=> "text");
							check_man_params($required);
							$data['meta_title'] = $_POST['meta_title'];
							$data['meta_keywords'] = $_POST["meta_keywords"];
							$data['meta_description'] = $_POST["meta_description"];
							$data['title'] = $_POST['title'];
							$data['introduction'] = $_POST["introduction"];
							$data['article'] = $_POST["text"];
							break;
			case "imgprfl":	$required=array("imageprofile"	=> "numeric");
							check_man_params($required);
							$data['imageprofile_id'] = $_POST['imageprofile'];
							break;
			case "publish":	$required=array("published"	=> "numeric");
							check_man_params($required);
							$data['published'] = $_POST['published'];
							break;
			case "format":	$required=array("format"	=> "numeric");
							check_man_params($required);
							$data['format'] = $_POST['format'];
							break;
		}			
			
		$where = 'WHERE `id_article`= '.$_POST["id_article"];

		$data['lastmod'] = time();
		$data['lastmod_user'] = $_SESSION["id_user"];

		q_update('cms_articles', $data, $where, $dbweb, __FILE__, __LINE__);
		$affected_rows = mysql_affected_rows($dbweb);
		if($affected_rows>0 && $affected_rows<2)
		{
			$xml .= '<Error>Artikel erfolgreich gespeichert</Error>'."\n";
		}
		elseif($affected_rows>1)
		{
			$xml .= '<Error>Es wurde mehr als ein Artikel geändert!</Error>'."\n";
		}
	}
	
	print $xml;
?>