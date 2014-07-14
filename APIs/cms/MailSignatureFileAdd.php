<?php
	/*************************
	********** SOA 2 *********
	***** Author Sven E. *****
	*** Firstmod 10.07.2014 **
	*** Lastmod 10.07.2014 ***
	*************************/
	
	$required=array("signature_id"	=> "numericNN", "filename"	=>	"text", "filesize"	=>	"numeric", "filename_temp"	=>	"text");
	
	check_man_params($required);
	
	$xml = '';

	//get filename
	$arrfilename = explode(".",$_POST['filename']);
	
	$extension = array_pop($arrfilename);
	$filename = implode(".",$arrfilename);
	
	$filesize =$_POST['filesize'];
	$filename_temp =$_POST['filename_temp'];
	
	$query="INSERT INTO cms_files (filename, extension, filesize, description, original_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".$filename."','".$extension."','".$filesize."','', 0, ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");";
	q($query, $dbweb, __FILE__, __LINE__);
	$file_id=mysqli_insert_id($dbweb);

	$update_array = array();
	$update_array['file_id'] = $file_id;
	$update_array['signature_id'] = $_POST['signature_id'];
	q_insert('cms_mail_signatures_images', $update_array, $dbweb, __FILE__, __LINE__);

	$dir=floor(bcdiv($file_id, 1000));
	if (!file_exists("../files/".$dir)) mkdir("../files/".$dir);
	$destination='../files/'.$dir.'/'.$file_id.'.'.$extension;
	$filename=substr($destination, 3);
		
	copy($filename_temp, $destination);

	$xml .= '	<File_id>'.$file_id.'</File_id>'."\n";
	$xml .= '	<File>'.str_replace("../", "", $destination).'</File>'."\n";
	$xml .= '	<File_name>'.$_POST['filename'].'</File_name>'."\n";
	$xml .= '	<Path>'.PATH.$filename.'</Path>'."\n";

	print $xml;

?>