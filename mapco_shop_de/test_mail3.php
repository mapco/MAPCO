<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

function get_parts($struct, $part, $delimiter)
{
	//print_r($struct);
	global $mbox;
	global $msg;
	$ausgabe=false;
	if (sizeof($struct)>1)
	{
	
		for ($k=1; $k<sizeof($struct); $k++)
		{
			
			if (isset($struct[$k]->parts)) 
			{
				echo "HALLO";
				get_parts($struct[$k], $part.".".(string)($k+1), $struct[$k]->parameters[0]->value);
				get_parts($struct[$k], $part.".".(string)($k), $struct[$k]->parameters[0]->value);
				
			}
			else
			{
				
			}
		}
		$ausgabe=false;
	}
	else
	{
		if (isset($struct->parts)) 
		{
			for ($k=0; $k<sizeof($struct->parts); $k++)
			{
				echo "HALLO";
	//			get_parts($struct->parts[$k],$part.".".(string)($k+1), $struct->parameters[0]->value);
				//$ausgabe=false;
			}
		}
		else
		{
			
		}
$ausgabe=false;
		
	}
	if ($ausgabe)
	{
		//print_r($struct);
		echo "PART: ".$part."<br />";
		echo "DELIMITER: ".$delimiter."<br />";
		echo "TYPE: ".$struct->type."<br />";
		echo "Encoding: ".$struct->encoding."<br />";
		echo "STRUCT:".$struct->parameters[0]->attribute." - ".$struct->parameters[0]->value."<br />";
		echo "T E X T::::::::::::::::::::::::::::::::<br>";
		
		$text=imap_fetchbody($mbox,$msg,$part);
		
		//HEADER AUSSCHLIESSEN
		if (strpos(strtolower($text),'boundary="--')===false)
		{
			//echo $text."<br />";
		
			
			if (isset($delimiter) && $delimiter!="")
			{
				$texts=explode($delimiter,$text);
			}
			else
			{
				$texts=$text;
			}
			
			for ($l=0; $l<sizeof($texts); $l++)
			{
				//echo $texts[$l]."%%%%%%%%%<br />";
				echo substr($texts[$l], strpos($text,"quoted-printable")+strlen("quoted-printable"))."%%%%%%%%%<br />";
				
			}
		}
		else 
		{
			echo "HEADER#################<br />";
		}
		
		//echo imap_fetchbody($mbox,$i,$part);
	}
}






	$mbox = imap_open("{dedi473.your-server.de:110/pop3}", "nputzing@mapco.de", "np75w6t2");


	if($mbox) 
	{ 
		// Mailbox pruefen und Anzahl der Mails 
		$hdr = imap_check($mbox); 
		if ($hdr) { 
			$msgcount = $hdr->Nmsgs; 
		} 
	
		// Header-Informationen der Email 
		$overview=imap_fetch_overview($mbox,"1:$msgcount",0); 
		$size=sizeof($overview); 




	
	   for($i=$size-1;$i>=$size-8;$i--){ 
		   // Informationen lesen (Absender, Datum, Groesse, Betreff) 
			$val=$overview[$i]; 
			$msg=$val->msgno; 
			$to=$val->to; 
			$from=$val->from; 
			$msize=$val->size; 
			$date=$val->date; 
			$subj=$val->subject; 
	
	
			// Empfänger aus Header lesen 
			$header=imap_fetchheader($mbox,$msg); 
			$header=str_replace('<','',$header); 
			$header=str_replace('>','',$header); 
	
			$start=strrpos($header,' To:')+4; 
			$end=strrpos($header,'Subject:'); 
			$endb=strrpos($header,'References:'); 
	
			if($endb<$end && $endb>$start) $end=$endb; 
			$headerto=trim(substr($header,$start,$end-$start)); 
	
			// Empfaenger auslesen (unterschiedliche Empfaenger) 
			$accounts=array(); 
			foreach(explode(",",$headerto) as $vala) 
				foreach(explode(' ',$vala) as $valb) 
					if(eregi('@',$valb) && eregi('yourdomain.com',$valb))  // nur Adressen deiner Domain 
						foreach(explode('@',$valb) as $valc) 
							if(!eregi('yourdomain.com',$valc)) $accounts[]=trim($valc); 
	
	
			 // falls header ungenügend, nimm Adresse aus overview 
			if(empty($accounts) && ereg("@yourdomain.com",$to)){ 
				$toarr=explode("@",$to); 
				$accounts[]=$toarr[0]; 
			} 
	
	
	/*
	
			// DB-Eintrag vorbereiten 
			$userarr=array(); 
			foreach($assounts as $mailboxname) 
			{ 
				$sql="select UserID,folder from usertable where mailboxname like '$mailboxname' limit 1"; 
				$result=mysqli_query($dbweb, $sql); 
				if(mysqli_num_rows($result)){ 
					$mailuser=mysqli_fetch_assoc($result);  // User-id und Ordner 
					$folder=$mailuser['folder']; 
					$mailuser=$mailuser['UserID']; 
					$userarr[$mailuser]=$folder; 
				} 
			} 
	*/	 
	
		echo "<p>";
	
			// Informationen ausgeben (Absender, Datum, Groesse, Betreff) 
			$msize=round($msize/1024); 
			list($dayName,$day,$month,$year,$time) = split(" ",$date); 
			$time = substr($time,0,5); 
			$date = $day ." ". $month ." ". $year . " ". $time; 
			$from = ereg_replace(array("\"","<",">"),array("","(",")"),$from); 
			echo $from." | ".$date." | ".$subj." | ".$msize." KB<br />"; 
	
	
	
			// Text lesen 
			$structure = imap_fetchstructure($mbox, $msg); 
			$text=imap_fetchbody($mbox,$i,1); 
//	echo $text."<br />---------------------------<br />";


//echo "ENCODING:".$structure->encoding."<br />";
//echo "TYPE:".$structure->type."<br />";
//echo "DELIMITER:".$structure->parameters[0]->value."<br />";
//echo "PARTCHARSET:".$structure->parts[1]->parameters[0]->value."<br />";
//print_r($structure);
if (isset($structure->parts))
{
	for ($m=0; $m<sizeof($structure->parts); $m++)
	{
		get_parts($structure->parts[$m], (string)$m, $structure->parameters[0]->value);
	}
}
else
{
	echo imap_fetchbody($mbox,$msg,1)."<br />";
}
			if($structure->encoding == 3) 
			{   
				 // generelles Encoding der Email (auch in den parts möglich) 
				 // foreach ($structure->parts as $part)  if($part->encoding == 3)... 
				$text=imap_base64($text);          
			} 
			elseif($structure->encoding == 4) 
			{ 
				$text=imap_qprint($text);     
			} 
			elseif($structure->encoding == 0) 
			{
				 $text=imap_utf8($text);
			}
			elseif($structure->encoding == 1) 
			{
				$text=$text;
			}
			 
			// Anhaenge lesen 
			unset($attachment); 
			if (isset($structure->parts))
			{
				$numparts = count($structure->parts); // Anzahl der Parts in der Mail 
			}
			else
			{
				$numparts=1;
			}
			if ($numparts > 1) {  // Std.-mäßig Anhänge im imap_fetchbody ab $j=2 
				$attachment=array(); 
				$j=2; 
				foreach ($structure->parts as $part) { 
					if (isset($part->disposition) && eregi("attachment",$part->disposition)) { 
						$dateiname=$part->dparameters[0]->value; 
						$attachment[]=array($dateiname,imap_fetchbody($mbox,$msg,$j)); 
						$j++; 
					} 
				} 
			} 
	
	
	
			// Text vorbereiten 
			$partoftext=0;
			$textparts=array();
			$contenttypes=array();
			//$text=trim(strip_tags(nl2br($text))); 
			$text=trim(nl2br($text));
			
			
			if ($numparts>1)
			{
				//find BOUNDARY	
				
			
				$ignore=array('NextPart','Content-Type','charset=','Content-Transfer');   // Header und Boundary entfernen 
				$arrtext=explode("\n",$text); 
			
			
				
				foreach($arrtext as $key=>$val)
				{ 
					$istext=true;
					//foreach($ignore as $wrong) 
					//{
					//	if(eregi($wrong,strtolower($val))) 
					//	{
							
							//unset($arrtext[$key]); 
							//save parts & info 
							//mit Zeile, die nicht info ist  -> partoftext ++
							if (strpos(strtolower($val), "charset")!==false)
							{
								$istext=false;
								$contenttypes[$partoftext]=trim(substr($val, strpos($val, "charset")+8 ));
							}
					//	}
						
						
					//}
					if ($istext)
					{
						if (isset($contenttypes[$partoftext])) $partoftext++;
						$textparts[$partoftext][]=$val;
					}
					
					$cval=strip_tags(trim($val));     // html entfernen 
					$cval=trim($val);
					if(empty($cval)) unset($arrtext[$key]); 
				}
			}
			/*
			//$arrtext=array_unique($arrtext);    // Doppelte Texteinträge entfernen  (Text ist in Mails mit Anhängen doppelt enthalten) 
			$textparts2=array();
			foreach($textparts as $textpart)
			{
				$textparts2[]=implode("<br />", $textpart);
			}
			for ($j=0; $j<sizeof($textparts2); $j++)
			{
				if (isset($contenttypes[$j]))
				{
					echo "CONTENTTYPE: ".$contenttypes[$j]."<br />";
				}
				else 
				{
					echo "CONTENTFAILURE<br />";
				}
			//	echo $textparts2[$j]."<br />";
			}
			echo imap_fetchbody($mbox,$msg,1); 
			$text=implode("<br>",$arrtext); 
			//echo $text."<br>"; 
			
			*/
			
			$filename="";
			if (!empty($attachment))
			{
				foreach ($attachment as $s_attachment)
				{ 
					list ($key, $val) = each ($s_attachment);
					echo "ANHANG: ".$key."<br />";
					if ($filename="") $filename=$val; else $filename.="|".$val;
					
				}
			}
	
			echo "</p><br />";
	/*		
			q("INSERT INTO mails (sender, reciever, subject, text, attachments, recieved) VALUES (
				'".mysqli_real_escape_string($dbweb, $from)."',
				'".mysqli_real_escape_string($dbweb, $to)."',
				'".mysqli_real_escape_string($dbweb, $subj)."',
				'".mysqli_real_escape_string($dbweb, $text)."',
				'".mysqli_real_escape_string($dbweb, $filename)."',
				".time()."
				);", $dbweb, __FILE__, __LINE__);
//			echo "<b>".mysqli_error()."</b><br />";
		*/	
	
	/*
		   // Mail speichern 
			$subj=trim(mysqli_real_escape_string($dbweb, $subj)); 
			$from=trim(mysqli_real_escape_string($dbweb, $from)); 
			$sql="select ID from mailtable order by id desc limit 1";  // neue Mail-ID ermitteln 
			$lastmailid=mysqli_fetch_assoc(mysqli_query($dbweb, $sql)); 
			$newmailid=$lastmailid['ID']+1; 
	
			$savemail=false; 
			foreach($userarr as $userid=>$folder) 
			{ 
				$sql="insert into mailtable (ID,UserID,betreff,text,datum,fromextern) values ($newmailid,$userid,'$subj','$text',NOW(),'$from')"; 
				if(mysqli_query($dbweb, $sql)){ 
					$savemail=true; 
					// Anhaenge speichern 
					foreach($attachment as $value){ 
						$code=getRandomCode(20); 
						$fname=getRepName($value[0]); 
						$end=explode(".",$fname); 
						$end=$end[count($end)-1]; 
						if(eregi("txt",$end)) $fvalue=imap_qprint($value[1]);  // für Textdateien 
						else $fvalue=imap_base64($value[1]); 
						$fvalue=imap_base64($value[1]); 
						$sqlatt="insert into UNET_attachments (mailid,user,titel,dateiname) values ($newmailid,$userid,'$fname','$code')"; 
						if(mysqli_query($dbweb, $sqlatt)){ 
							echo $dir="../".$folder."/".$code; 
							$fp=fopen($dir,'w'); 
							fwrite($fp,$fvalue); 
							echo "<b>saved</b><br>"; 
						} 
					} 
				} 
			} 
			// Delete-Flag setzen 
			if($savemail) imap_delete($mbox, $msg); 
		*/
		} 
	
	
		// Nachrichten loeschen 
	  //  imap_expunge($mbox); 
		// Verbindung schließen 
		imap_close($mbox); 
	} 



	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>
