<?php
	
	check_man_params(array( "ToReceiver" 	=> "textNN",
							"FromSender" 	=> "textNN",
							"Subject"		=> "text",
							"MsgText"		=> "text"));
							
	$ToReceiver=$_POST["ToReceiver"];
	$FromSender=$_POST["FromSender"];
	$Subject=$_POST["Subject"];
	$MsgText=$_POST["MsgText"];
	
	define("XNL","\r\n") ; // CONSTANT Newline CR
	
	if (!function_exists("TextEncode"))
	{
		//
		// build attachment as text conforming RFC2045 (76 char per line, end with \r\n)
		//
		function TextEncode($FileName)
		{
			if (is_readable($FileName))
			{
				$fp = fopen($FileName, "rb") ;
				$cont = fread($fp, filesize ($FileName)) ;
				$contents = base64_encode($cont) ;
				$len = strlen($contents) ;
				$str = "" ;
				
				while($len > 0)
				{
					if ($len >= 76)
					{
						$str .= substr($contents,0,76).XNL ;
						$contents = substr($contents, 76) ;
						$len = $len - 76 ;
					}
					else
					{
						$str .= $contents.XNL ;
						$contents = "" ;
						$len = 0 ;
					}
				}
				fclose($fp) ;
			}
			else
			{
				$str = "File ".$FileName." not found" ;
			}
			return $str ;
		}
	}
							
	if(isset($_POST["IFile"]))
	{
		check_man_params(array( "IFile" => "textNN"));
		$IFile=$_POST["IFile"];
	}
	else
	{
		$IFile="none";
	}
	
	if(isset($_POST["IFileName"]))
	{
		check_man_params(array( "IFileName" => "textNN"));
		$IFileName=$_POST["IFileName"];
	}
	else
	{
		$IFileName="none";
	}
		
	$mime_boundary = "--==================_846811060==_" ;
	$mimetype = "application/octet-stream" ;
	
	// check for array (multiple attachments)
	if (!is_array($IFile))                                  
	{
		$File[0] = $IFile ;
		$FileName[0] = $IFileName ;
	}
	else
	{
		for ($i=0;$i<count($IFile);$i++)
		{
			$File[$i] = $IFile[$i] ;
			$FileName[$i] = $IFileName[$i] ;
		}
	}
	
	$attCount = count($File) ;
	
	// check if there is really an attachment
	$attExists = FALSE ;                                    
	for ($i=0;$i<$attCount;$i++)
	{
		if ($File[$i] != "none")
		{
			$attExists = TRUE ;
		}
	}
	
	// build header for text
	$txtheaders  = "From: ".$FromSender."\n" ;              
	$txtheaders .= "Reply-To: ".$FromSender."\n" ;
	$txtheaders .= "X-Mailer: PHP\n" ;
	$txtheaders .= "X-Sender: ".$FromSender."\n" ;
	
	// is there an attachment
	if ($attExists)                                       
	{
		// build header for attachment
		$attheaders  = "MIME-version: 1.0\n" ;
		$attheaders .= 'Content-type: multipart/mixed; boundary="'.$mime_boundary.'"'."\n" ;
		$attheaders .= "Content-transfer-encoding: 7BIT\n" ;
		$attheaders .= "X-attachments: " ;
		$firstAtt = TRUE ;
		for ($i=0;$i<$attCount;$i++)
		{
			if ($File[$i] != "none")
			{
				if ($firstAtt)
				{
					$firstAtt = FALSE ;
				}
				else
				{
					$attheaders .= "," ;
				}
				$attheaders .= $FileName[$i] ;
			}
		}
		$attheaders .= ";\n\n" ;
		
		// build attachment itself
		$attach = "" ;
		for ($i=0;$i<$attCount;$i++)
		{
			if ($File[$i] != "none")
			{
				$attach  .= "--".$mime_boundary."\n" ;
				$attach  .= "Content-type:".$mimetype.'; name="'.$FileName[$i].'";'."\n" ;
				$attach  .= "Content-Transfer-Encoding: base64\n" ;
				$attach  .= 'Content-disposition: attachment; filename="'.$FileName[$i].'"'."\n\n" ;
				$attach  .= TextEncode($File[$i])."\n" ;
			}
		}
		// build message itself
		$message  = "--".$mime_boundary."\n" ;
		$message .= 'MIME-Version: 1.0' ."\n";
		$message .= 'Content-Transfer-Encoding: 8bit'. "\n";
		$message .= 'Content-Type: text/html; charset=utf-8' ."\n\n";
		$message .= $MsgText."\n" ;
	}
	else                                                  // no attachment
	{
		$txtheaders .= 'MIME-Version: 1.0' ."\n";
		$txtheaders .= 'Content-Transfer-Encoding: 8bit'. "\n";
		$txtheaders .= 'Content-Type: text/html; charset=utf-8' ."\n\n";
		$attheaders = "" ;
		$attach  = "" ;
		$message = $MsgText."\n" ;                         // send text only
	}
	
	// send email
	$mail_status='test';
	$mail_status=mail($ToReceiver, $Subject, $message.$attach, $txtheaders.$attheaders) ;
	
	$xml='';
//	$xml.='<mail_msg><![CDATA['.$txtheaders.$attheaders.$message.$attach.']]></mail_msg>'."\n";
	$xml.='<mail_status><![CDATA['.$mail_status.']]></mail_status>'."\n";
	echo $xml;

?>