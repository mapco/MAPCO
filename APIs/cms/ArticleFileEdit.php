<?php

	/*************************
	********** SOA 2 *********
	***** Author Sven E. *****
	*** Lastmod 25.03.2014 ***
	*************************/

	$required = array("file_id"	=> "numeric", "file_name"	=>	"text", "file_ext"	=> "text", "file_desc"	=> "text");
	check_man_params($required);
	
	$data['id_file'] = $_POST['file_id'];
	$data['filename'] = $_POST['file_name'];
	$data['extension'] = $_POST['file_ext'];
	$data['description'] = $_POST['file_desc'];

	$where = 'WHERE id_file='.$data['id_file'];

	$result = q("SELECT id_file FROM cms_files ".$where.";", $dbweb, __FILE__, __LINE__);
	if (mysqli_affected_rows($dbweb) == 1)
	{
		$result=q_update("cms_files", $data, $where, $dbweb, __FILE__, __LINE__);	
	}
	else
	{
		$xml .=	'<Error>Beim Speichern der Änderungen ist ein Fehler aufgetreten.</Error>';
	}

	print $xml;

?>