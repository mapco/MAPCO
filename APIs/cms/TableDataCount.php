<?php

/**
*
*
*/

$required = array("table" => "textNN", "db" => "resource", "col" => "text");
check_man_params($required);

if ($_POST["db"] == "dbweb") $db = $dbweb;
if ($_POST["db"] == "dbshop") $db = $dbshop;

$sql = 'SELECT COUNT('.$_POST['col'].') AS entries FROM '.$_POST['table'];
if ($_POST["where"] == "") { $sql .= ' '.$_POST['where']; }

$res = q($sql, $db, __FILE__, __LINE__);
$row = mysqli_fetch_assoc($res);
echo $xml . '<num_rows>' . $row['entries'] . '</num_rows>';