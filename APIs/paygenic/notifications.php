<?php
	include("../../mapco_shop_de/config.php");

$text="POST:";
while (list ($key, $val) = each ($_POST)) $text.=$key.'+'.$val.'|';
	$res=q("INSERT INTO testtable (text) VALUES ('".$text."');", $dbshop, __FILE__, __LINE__);
$text="GET:";
while (list ($key, $val) = each ($_GET)) $text.=$key.'+'.$val.'|';
	$res=q("INSERT INTO testtable (text) VALUES ('".$text."');", $dbshop, __FILE__, __LINE__);

?>