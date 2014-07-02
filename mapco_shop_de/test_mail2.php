<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	
	
	function get_content($mail_object, $level, $boundary="0", $part="0")
	{
		$part2="";
		//global $level;
		global $mbox;
		global $i;
		//echo "MO:".sizeof($mail_object);
		
		for ($l=0; $l<sizeof($mail_object); $l++)
		{


			
			echo $l."Level: <b>".$level."</b><br />";
//			print_r($mail_object[$l]);
			if (isset($mail_object[$l]->parts))
			{
			
				for ($j=0; $j<sizeof($mail_object[$l]->parts); $j++)
				{
					if ($part=="0")
					{
					$part2=(string)($l+1);
					$part2=(string)$part2.".".(string)($j+1);
					}
					else
					{
						$part2=(string)$part.".".(string)($j+1);
					}


					if 	(isset($mail_object[$l]->parts[$j]))
					{

						if ($mail_object[$l]->parameters[0]->attribute=="BOUNDARY")
						{  
							$boundary=$mail_object[$l]->parameters[0]->value;
						}
						else 
						{
							$boundary="";
						}
						
						$mail_object2=$mail_object[$l]->parts;
						
			
						get_content($mail_object2, $level+1, $boundary, $part2);
					}
					else 
					{
						for ($k=0; $k<$level; $k++)
						{ 
							echo ">> ";
						}
						echo "PARTs: ".$part2."<br />";
						echo "+".$mail_object[$l]->parameters[0]->attribute." ";
						echo $mail_object[$l]->parameters[0]->value."<br />";
						//echo $mail_object[$l]->

				//echo imap_fetchbody($mbox, $i, $part2)."<br />";
				echo "~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~<br />";
					}
		
				}
			}
			else 
			{
				for ($k=0; $k<$level; $k++)
				{ 
					echo ">> ";
				}
				if ($boundary!="") echo $boundary."<br />";
				echo  $mail_object[$l]->parameters[0]->attribute." ";
				echo  $mail_object[$l]->parameters[0]->value."<br />";
				echo  "TYPE: ".$mail_object[$l]->type."<br />";
				echo  "Encoding: ".$mail_object[$l]->encoding."<br />";
				if ($part=="0") 
				{
					$part2=$l+1; 
				}
				else 
				{
					$part2=$part;
				}
				echo "PART: ".$part2."<br />";
				
				$text= imap_fetchbody($mbox, $i, $part2);
				//echo $text;
				if ( $mail_object[$l]->type=="0" ||  $mail_object[$l]->type=="1" ||  $mail_object[$l]->type=="2")
				{
					if ($mail_object[$l]->encoding=="0") $text=imap_utf7_decode($text);
					elseif ($mail_object[$l]->encoding=="1") $text=imap_utf8($text);

					elseif ($mail_object[$l]->encoding=="4") $text=iconv("iso-8859-1", "UTF-8", $text);
					else $text="FORMAT: ".$mail_object[$l]->encoding;
				}
				if ( $mail_object[$l]->type=="3" ||  $mail_object[$l]->type=="4" ||  $mail_object[$l]->type=="6" || $mail_object[$l]->type=="7")
				{
						$text="ANDERES FORMAT";
				}
				if ( $mail_object[$l]->type==5)
				{
						$text="IMAGE ENCODED: ".$mail_object[$l]->encoding;
				}
				echo $text;
				echo "<br />~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~<br />";
			}
		}
	}
	


	$mbox = imap_open("{dedi473.your-server.de:110/pop3}", "nputzing@mapco.de", "np75w6t2");

		$headers = imap_headers($mbox);
		$num = imap_num_msg($mbox);

		$i=0;
		for ($i=1; $i<=$num; $i++)
		{
			print_r($headers[$i-1]);
			echo "<br />";
			$body=imap_body($mbox, $i);
			$header=imap_header($mbox, $i);
			
			
			$level=0;
			
			
			$body_structure=imap_fetchstructure($mbox, $i);
		/*	
			if ($body_structure->ifparameters == "1")
			{
				$mail_object=$body_structure->parts;
				get_content($mail_object, $level);
			}
		*/
			if (isset($body_structure->parts))
			{
				get_content($body_structure->parts, $level,"0","0");
			}
		//	echo imap_fetchbody($mbox, $i, "1.1")."<br />";
			echo "######################################################################################<br />";
		}


	imap_close($mbox);


	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>
