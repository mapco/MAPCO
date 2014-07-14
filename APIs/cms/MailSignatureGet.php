<?php 
	
	/*********************/
	/********SOA2*********/
	/*********************/
	
	mb_internal_encoding("UTF-8");
	
	check_man_params(array("account" => "numericNN"));
	
	$xml = '';

	$res = q("SELECT signature_id, signature_text, signature_desc FROM cms_mail_accounts, cms_mail_signatures WHERE id_account=".$_POST['account']." AND id_signature=signature_id LIMIT 1;", $dbweb, __FILE__, __LINE__);
	$row = mysqli_fetch_assoc($res);
	
	foreach ( $row as $key => $value )
	{
		$xml .= "	<".$key.">".$value."</".$key.">\n";
	}
	
	$res_sign = q("SELECT file_id, replace_tag, filename, extension FROM cms_mail_signatures_images, cms_files WHERE signature_id=".$row['signature_id']." AND id_file=file_id;", $dbweb, __FILE__, __LINE__);
	while ( $row_sign = mysqli_fetch_assoc($res_sign) )
	{
		$row_sign['folder'] = floor(bcdiv($row_sign['file_id'], 1000));
		$xml .= "<signature_image>\n";
		foreach ( $row_sign as $key => $value )
		{
			$xml .= "	<".$key.">".$value."</".$key.">\n";
		}
		$xml .= "</signature_image>\n";
	}
	
	print $xml;
?>