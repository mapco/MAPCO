<?php
	///äöüÄÖÜ UTF-8
	if (!function_exists("SendMail"))
	{
		define("XNL","\r\n") ; // CONSTANT Newline CR
		
		$mime_boundary = "--==================_846811060==_" ;
		$mimetype = "application/octet-stream" ;

		function SendMail($ToReceiver, $FromSender, $Subject, $MsgText, $IFile="none", $IFileName="none")
		{
			global $mimetype, $mime_boundary, $Answer_Reply;
		
		   if (!is_array($IFile))                                  // check for array (multiple attachments)
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
		
		   $attExists = FALSE ;                                    // check if there is really an attachment
		   for ($i=0;$i<$attCount;$i++)
			  {
			  if ($File[$i] != "none")
				 {
				 $attExists = TRUE ;
				 }
			  }
		
		
			$txtheaders  = "From: ".$FromSender."\n" ;              // build header for text
		//	$txtheaders .= "Reply-To: ".$Answer_Reply."\n" ;
			$txtheaders .= "Reply-To: ".$FromSender."\n" ;
			$txtheaders .= "X-Mailer: PHP\n" ;
			$txtheaders .= "X-Sender: ".$FromSender."\n" ;
		
		   if ($attExists)                                        // is there an attachment
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
			//	  $message .= 'Content-Type: text/HTML; charset=ISO-8859-1'."\n\n";
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
			  $mail_status=mail($ToReceiver, $Subject, $message.$attach, $txtheaders.$attheaders) ;
		
		   }
	}
	
	
	if (!function_exists("TextEncode"))
	{
		//
		// build attachment as text conforming RFC2045 (76 char per line, end with \r\n)
		//
		function TextEncode ($FileName)
		   {
		
		   if (is_readable($FileName))
			  {
				$fp = fopen($FileName, "r") ;
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
	


	if (!function_exists("send_html_mail"))
	{
		function send_html_mail($ToReceiver, $Subject, $MsgText, $IFile="none", $IFileName="none")
		{
					$Subject="MAPCO - ".$Subject;
					$MsgText = '
						<html>
							<head>
								<title>'.$Subject.'</title>
							</head>
							<body bgcolor="#ffffff" margin="0">
								<table width="100%" border="1" cellspacing="0" cellpadding="10"><tr><td>
								<table align="center" width="600" height="115" cellpadding="0" cellspacing="0">
									<tr><td color="#ffffff" bgcolor="#000000"><img src="http://www.mapco.de/images/newsletter_header.jpg" border="0" alt="MAPCO Autotechnik GmbH" title="MAPCO Autotechnik GmbH" /></td></tr>
									<tr>
										<td color="#000000" bgcolor="#ffffff">
											<table width="100%" bgcolor="#ffffff" cellspacing="0" cellpadding="5"><tr><td>
											<h2>'.$Subject.'</h2>
											'.$MsgText.'
											</td></tr></table>
										</td>
									</tr>
								</table>
								</td></tr></table>
							</body>
						</html>';
						SendMail($ToReceiver, "MAPCO-Shop <bestellung@mapco-shop.de>", $Subject, $MsgText, $IFile, $IFileName);
//						SendMail("developer@mapco.de", "MAPCO-Shop <bestellung@mapco-shop.de>", $ToReceiver." ".$Subject, $MsgText, $IFile, $IFileName);
		}
	}

	if (!function_exists("send_html_mail2"))
	{
		function send_html_mail2($ToReceiver, $FromSender, $Subject, $MsgText, $IFile="none", $IFileName="none")
		{
					
					$MsgText = '
						<html>
							<head>
								<title>'.$Subject.'</title>
							</head>
							<body bgcolor="#ffffff" margin="0">
								<table width="100%" border="1" cellspacing="0" cellpadding="10"><tr><td>
								<table align="center" width="600" height="115" cellpadding="0" cellspacing="0">
									<tr><td bgcolor="#ffffff" align="center"><img src="http://www.mapco.de/images/newsletter_header.jpg" border="0" alt="MAPCO Autotechnik GmbH" title="MAPCO Autotechnik GmbH" /></td></tr>
									<tr>
										<td color="#000000" bgcolor="#ffffff">
											<table width="100%" bgcolor="#ffffff" cellspacing="0" cellpadding="5"><tr><td>
											<h2>'.$Subject.'</h2>
											'.$MsgText.'
											</td></tr></table>
										</td>
									</tr>
								</table>
								</td></tr></table>
							</body>
						</html>';
						SendMail($ToReceiver, $FromSender , $Subject, $MsgText, $IFile, $IFileName);
		}
	}


	if (!function_exists("send_html_mail_ma"))
	{
		function send_html_mail_ma($ToReceiver, $Subject, $MsgText, $IFile="none", $IFileName="none")
		{
					$Subject="MAPCO - ".$Subject;
					$MsgText = '
						<html>
							<head>
								<title>'.$Subject.'</title>
							</head>
							<body bgcolor="#ffffff" margin="0">
								<table width="100%" border="1" cellspacing="0" cellpadding="10"><tr><td>
								<table align="center" width="600" height="115" cellpadding="0" cellspacing="0">
									<tr>
										<td color="#000000" bgcolor="#ffffff">
											<table width="100%" bgcolor="#ffffff" cellspacing="0" cellpadding="5"><tr><td>
											'.$MsgText.'
											</td></tr></table>
										</td>
									</tr>
								</table>
								</td></tr></table>
							</body>
						</html>';
						SendMail($ToReceiver, "MAPCO-Shop <bestellung@mapco-shop.de>", $Subject, $MsgText, $IFile, $IFileName);
		}
	}
	
	
	
	if (!function_exists("send_news_mail"))
	{
		function send_news_mail($ToReceiver, $Subject, $MsgText, $IFile="none", $IFileName="none")
		{
					$MsgText = '
						<html>
							<head>
								<title>'.$Subject.'</title>
							</head>
							<body bgcolor="#ffffff" margin="0">
								<table width="100%" border="1" cellspacing="0" cellpadding="10"><tr><td>
								<table align="center" width="600" height="115" cellpadding="0" cellspacing="0">
									<tr><td color="#ffffff" bgcolor="#000000"><img src="http://www.mapco.de/images/newsletter_header.jpg" border="0" alt="MAPCO Autotechnik GmbH" title="MAPCO Autotechnik GmbH" /></td></tr>
									<tr>
										<td color="#000000" bgcolor="#ffffff">
											<table width="100%" bgcolor="#ffffff" cellspacing="0" cellpadding="0"><tr><td>
											<span style="font-family:Arial; font-size:24px; font-weight:bold; color:#000000;">'.$Subject.'</span>
											'.$MsgText.'
											</td></tr></table>
										</td>
									</tr>
								</table>
								</td></tr></table>
							</body>
						</html>';
						SendMail($ToReceiver, "MAPCO-Shop <newsletter@mapco-shop.de>", $Subject, $MsgText, $IFile, $IFileName);
						SendMail("developer@mapco.de", "MAPCO-Shop <newsletter@mapco-shop.de>", $ToReceiver." ".$Subject, $MsgText, $IFile, $IFileName);
		}
	}

	if (!function_exists("send_ticket_mail"))
	{
		function send_ticket_mail($ToReceiver, $Subject, $MsgText, $IFile="none", $IFileName="none")
		{
			$MsgText = '
				<html>
					<head>
						<title>'.$Subject.'</title>
					</head>
					<body bgcolor="#ffffff" margin="0">
						<table width="100%" border="1" cellspacing="0" cellpadding="10"><tr><td>
						<table align="center" width="600" height="115" cellpadding="0" cellspacing="0">
							<tr><td color="#ffffff" bgcolor="#000000"><img src="http://www.mapco.de/images/newsletter_header.jpg" border="0" alt="MAPCO Autotechnik GmbH" title="MAPCO Autotechnik GmbH" /></td></tr>
							<tr>
								<td color="#000000" bgcolor="#ffffff">
									<table width="100%" bgcolor="#ffffff" cellspacing="0" cellpadding="5"><tr><td>
										<h2>'.$Subject.'</h2>
										'.$MsgText.'
									</td></tr></table>
								</td>
							</tr>
						</table>
						</td></tr></table>
					</body>
				</html>';
			SendMail($ToReceiver, "Ticket-System <noreply@mapco.de>", $Subject, $MsgText, $IFile, $IFileName);
		}
	}

	if (!function_exists("send_remind_mail"))
	{
		function send_remind_mail($ToReceiver, $Subject, $MsgText, $IFile="none", $IFileName="none")
		{
			$MsgText = '
				<html>
					<head>
						<title>'.$Subject.'</title>
					</head>
					<body bgcolor="#ffffff" margin="0">
						<table width="100%" border="1" cellspacing="0" cellpadding="10"><tr><td>
						<table align="center" width="600" height="115" cellpadding="0" cellspacing="0">
							<tr><td color="#ffffff" bgcolor="#000000"><img src="http://www.mapco.de/images/newsletter_header.jpg" border="0" alt="MAPCO Autotechnik GmbH" title="MAPCO Autotechnik GmbH" /></td></tr>
							<tr>
								<td color="#000000" bgcolor="#ffffff">
									<table width="100%" bgcolor="#ffffff" cellspacing="0" cellpadding="5"><tr><td>
										<h2>'.$Subject.'</h2>
										'.$MsgText.'
									</td></tr></table>
								</td>
							</tr>
						</table>
						</td></tr></table>
					</body>
				</html>';
			SendMail($ToReceiver, "Ticket-System <noreply@mapco.de>", $Subject, $MsgText, $IFile, $IFileName);
		}
	}

	
?>