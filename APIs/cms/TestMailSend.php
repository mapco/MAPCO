wen es interessiert, bei mir klappt das hervorragend so:

PHP-Code:
$mail_header = "From: $von"; 
/* boundary */ 
$boundary = strtoupper(md5(uniqid(time()))); 

/* MIME-mail-header */ 
$mail_header .= "\nMIME-Version: 1.0"; 
$mail_header .= "\nContent-type: multipart/mixed; boundary=$boundary"; 
$mail_header .= "\n\nThis is a multi-part message in MIME format  --  Dies ist eine mehrteilige Nachricht im MIME-Format"; 

/* Hier faengt der normale Mail-Text an */ 
$mail_header .= "\n--$boundary"; 
$mail_header .= "\nContent-type: $content_type_s"; 
$mail_header .= "\nContent-Transfer-Encoding: 8bit"; 
$mail_header .= "\n\n$mail_content"; 

/* Hier faengt der Datei-Anhang an */ 
$mail_header .= "\n--$boundary"; 
$mail_header .= "\nContent-type: $anhang_content_type; name=\"$dateiname\""; 
/* Lese aus dem Array $contenttypes die Codierung fuer den MIME-Typ des Anhangs aus */ 
$mail_header .= "\nContent-Transfer-Encoding: ".$encoding; 
$mail_header .= "\nContent-Disposition: attachment; filename=\"$dateiname\""; 
$mail_header .= "\n\n$datei_content"; 

/* Gibt das Ende der eMail aus */ 
$mail_header .= "\n--$boundary--";  
$content_type_s = "text/html" oder "text/plain"
$mail_content ist der eigentliche mailtext (html-text)
$anhang_content_type = "image/gif" oder was da halt drann hängt
$dateiname = name des attachments, wie er angezeigt werden soll

----------------------------------------------------------------------
$boundary="=_XXXboundaryXXX"; 
        $empf=$defaul_mail; // Empfaenger 
        $subj="Test"; // Betreff 
        $hdrs="From: ".$mail."\r\n"; // Absender 
        $hdrs.="MIME-Version: 1.0\r\n"; // MIME-Version 
        $hdrs.="Content-Type: multipart/mixed"; 
        $hdrs.="boundary=\"$boundary\";\r\n"; 
        $hdrs.="Content-Transfer-Encoding: 8bit"; 
        $body ="--$boundary\r\n"; // Ende des Headers markieren 
        // Header fuer den HTML-Teil schreiben 
        $body .="Content-Type: text/html; charset=\"iso-8859?1\";\r\n"; 
        // NACH dem letzten Header ZWEI Zeilenumbrueche 
        $body .="Content-Transfer-Encoding: 8bit\r\n\r\n"; 
        // Der HTML-Teil der Mail 
        $body.="<html><head><title></title> 
                </head> 
                <body> 
                <table width=\"300\"> 
                <tr><td colspan=\"2\"> 
                 
                <img src=\"cid:bild1\"> 
                </td></tr> 
                <tr> 
                 
                <td><img src=\"cid:bild2\"></td> 
                <td>PHP ist eine tolle Sache!</td> 
                </tr> 
                <tr><td colspan=\"2\"> 
                 
                <img src=\"cid:bild1\"> 
                </td></tr> 
                </table> 
                </body> 
                </html>\r\n"; 
        $body.="--$boundary\r\n"; // HTML-Teil beenden 
        // Erste Grafik einfuegen 
        grafik_einfuegen ("bild1","Images/Layout/nav_pfeil_gruen_o.gif",$body); 
        $body.="--$boundary\r\n"; // Erste Grafik beenden 
        // Zweite Grafik einfuegen 
        grafik_einfuegen ("bild2","Images/Layout/nav_pfeil_gruen.gif",$body); 
        $body.="--$boundary--\r\n\r\n";// Zweite Grafik beenden 

<?php 

$header = "From: segerland@mapco.de
	MIME-Version: 1.0
	";
$header .= "Content-Type: MULTIPART/related; 
	BOUNDARY=\"173361623-748685133-1404735784=:21895\"
	
	";

$message .= "--173361623-748685133-1404735784=:21895
	Content-Type: MULTIPART/alternativ; 
	BOUNDARY=\"1404735784-748685133-173361623=:95821\"
	
	--1404735784-748685133-173361623=:95821
	Content-Type: TEXT/plain; CHARSET=US-ASCII
	Content-Description: Reiner Text
	
	Dies ist der Plain Text Teil
	
	--1404735784-748685133-173361623=:95821
	Content-Type: TEXT/html; CHARSET=US-ASCII
	Content-Description: HTML Part
	
	<h1>Dies ist der HTML Teil</h1>
	
	
	--1404735784-748685133-173361623=:95821--
	
	--173361623-748685133-1404735784=:21895--


";

$mail_status=mail('sven.egerland@web.de', 'testmail', $message, $header);

/*
$message .= "--173361623-748685133-1404735784=:21895\n";
$message .= "Content-Type: image/png;\n
	name=\"image002.png\"\n";
$message .= "Content-Transfer-Encoding: base64\n";
$message .= "Content-ID: <image002.png@01CF9511.BD2C1840>\n\n";

$message .= "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
YII=\n\n";
*/



?>