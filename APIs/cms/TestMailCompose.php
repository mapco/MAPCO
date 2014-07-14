<?php

$envelope["from"]= "segerland@mapco.de";
$envelope["to"]  = "sven.egerland@web.de";
//$envelope["cc"]  = "bar@example.com";

$body[0]["type"] = TYPEMULTIPART;
$body[0]["subtype"] = "related";
$body[0]["boundary"] = "1724";

$body[1]["type"] = TYPEMULTIPART;
$body[1]["subtype"] = "alternative";
$body[1]["boundary"] = "2417";

$body[2]["type"] = TYPETEXT;
$body[2]["subtype"] = "plain";
$body[2]["description"] = "Reiner Text";
$body[2]["contents.data"] = "Dies ist der Plain Text Teil\n\n\n\t";
$body[1]["boundary"] = "2417";

$body[3]["type"] = TYPETEXT;
$body[3]["subtype"] = "html";
$body[3]["description"] = "HTML Part";
$body[3]["contents.data"] = "<h1>Dies ist der HTML Teil</h1>\n\n\n\t";
$body[1]["boundary"] = "2417";

echo nl2br(imap_mail_compose($envelope, $body));
?>