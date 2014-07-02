<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	

	
	
	function get_content($mail_object, $level)
	{
		global $body;
		global $i;
		global $mbox;
		global $boundary;
		//print_r($mail_object);
		for ($k=0; $k<sizeof($mail_object); $k++)
		{
			if ($mail_object[$k]->ifparameters=="1")
			{
				$boundary[$level]=$mail_object[$k]->parameters[0]->value;
				echo "Boundary: ".$boundary[$level]." LEVEL: ".$level."<br />";
				//echo print_r($boundary)."0000000000000000";
				if ($mail_object[$k]->encoding == "4" && $mail_object[$k]->type == "0")
				{
					//echo "%%%%%%%%%%%%%%%%%%%%%%%%%%%".substr($body, strpos($body, "quoted-printable ")+strlen("quoted-printable "))."§§§§§§§§§§§§§§<br />";		
					/*			
					if ($boundary[$level]=="UTF-8")
					{
						echo "%%%%%%%%%%%%%%%%%".substr(imap_fetchbody($mbox, $i , 1),strpos(imap_fetchbody($mbox, $i , 1), "quoted-printable "))."§§§§§§§§§§§§<br />";	
					}
					else
					{
						echo "%%%%%%%%%%%%%%%%%".substr(imap_fetchbody($mbox, $i , 1),strpos(imap_fetchbody($mbox, $i , 1), "quoted-printable "))."§§§§§§§§§§§§<br />";
					}
					*/
					echo imap_fetchbody($mbox, $i , 1.1);
				}
					
			}
			if (isset($mail_object[$k]->parts[0])) 
			{
					//get_content($mail_object[$k]->parts, $level+1, str_replace($boundary[$level],"",substr($body, strpos($body, $boundary[$level]))));
					get_content($mail_object[$k]->parts, $level+1);
			}
		}
	}

	$mbox = imap_open("{dedi473.your-server.de:110/pop3}", "nputzing@mapco.de", "np75w6t2");

		echo "<h1>Nachrichten in INBOX</h1>\n";
		$headers = imap_headers($mbox);
		$num = imap_num_msg($mbox);
		
		echo "++".$num."++<br />";

	$i=0;
		
		for ($i=1; $i<=10; $i++)
		{
			
	$level=0;
	$boundary=array();
	$body="";

			$body=imap_body($mbox, $i);
			$header=imap_header($mbox, $i);
			$body_structure=imap_fetchstructure($mbox, $i);
			

/*
			if ($body_structure->ifparameters == "1")
			{
				$mail_object=$body_structure->parts;
				get_content($mail_object, $level, $body);
			}
*/
			
			$header=imap_header($mbox, $i);
			$body_structure=imap_fetchstructure($mbox, $i);
			echo "____________________________________________________<br>";
	//		print_r($body_structure)."<br />";
			echo "++++++++++++++++++++++<br />";
			echo $header->message_id." ".$header->fromaddress." ".$header->Msgno."<br />";
			echo $header->subject."<br />";
			echo imap_fetchbody($mbox, $i ,1.1);
/*
			if (isset($body_structure->parts[0])) 
			{
				//GET NEXTPART SIGNATURE
				$header=imap_fetchbody($mbox, $i , 0);
				$nextpartpos=strpos($header, "boundary=");
				$tmp=substr($header, $nextpartpos+10);
				$nextpartsignature=substr($tmp,0,strpos($tmp, '"'));
				
				echo "SIGNATURE: ".$nextpartsignature."<br />";
				
				echo "+++".sizeof($body_structure->parts)."**<br />";
				for ($j=0; $j<sizeof($body_structure->parts); $j++)
				{
					if ($j<0)
					{
						echo "ENCODING: ".$body_structure->parts[$j]->encoding."++<br />";
						echo "MIME: ".$body_structure->parts[$j]->type."++<br />";
						$text=imap_fetchbody($mbox, $i , $j);
						//if ()
						//$contentdesc=substr($text, 0, "quoted-printable ")
						
					}
				}
			
			}
			else 
			{
				echo "ENCODING: ".$body_structure->encoding."++<br />";
				echo "MIME: ".$body_structure->type."++<br />";
				$body=imap_body($mbox, $i);
				echo $body."<br />~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~<br />";
			}
*/
			$intStatic = 2;//to initialize the mail body section

			$decode = imap_fetchbody($mbox, $i, "");
 			$no_of_occurences = substr_count($decode,"Content-Transfer-Encoding: base64");//to get the no of images 
	/*		
			if($no_of_occurences > 0){


				for($k = 0; $k < $no_of_occurences; $k++){ 

					$strChange = strval($intStatic+$k); 
					$decode = imap_fetchbody($mbox, $i , $strChange);//to get the base64 encoded string for the image 
					$data = base64_decode($decode); 
					$fName = time()."_".$strChange . '.gif'; 
					echo $fName."<br />";;
					//$file = $fName; 
					//$success = file_put_contents($file, $data); //creates the physical image
				 }
			 } 

		*/
		//	$body=imap_body($mbox, $i);
			//echo imap_utf8($body)."<br /><br />";
	//	echo $body."<br /><br />";
		//echo imap_base64 ( $body );
			echo "____________________________________________________<br>";

		}

//		echo imap_qprint(imap_body($mbox, $num))."<br />"; 
/*
		if ($headers == false) {
			echo "Abruf fehlgeschlagen<br />\n";
		} else {
			foreach ($headers as $val) {
				echo $val
				echo $val . "<br />\n";
			}
		}
*/		
		imap_close($mbox);


	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>

