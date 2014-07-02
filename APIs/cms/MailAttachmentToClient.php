<?php
	mb_internal_encoding("UTF-8");
	require_once("../../mapco_shop_de/functions/mail_connect.php");
	
	check_man_params(array("msg_num"		=> "numericNN",
						   "account"	=> "numericNN",
						   "filename"		=> "textNN"));

	$mbox = mail_connect($_POST['account']);
	getmsg($mbox,$_POST['msg_num']);

	foreach ( $attachments as $filename => $filedata)
	{
		if ( $filename == $_POST['filename'] )
		{	
			$xml = '<attachment><![CDATA['."\n";
			$xml .=	header("Pragma: public"); 
			$xml .= header("Expires: 0"); 
			$xml .= header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
			$xml .= header("Content-Type: application/force-download"); 
			$xml .= header("Content-Type: application/octet-stream"); 
			$xml .= header("Content-Type: application/download"); 
			$xml .= header('Content-Disposition: attachment; filename='.$filename.'.pdf'); 
			$xml .= header("Content-Transfer-Encoding: binary");
			$xml .= header("Content-Length: ".$bytes[$filename]);
			
			$xml .= $filedata;
			$xml .= ']]></attachment>'."\n";
			break;
		}
	}
	print $xml;

?>