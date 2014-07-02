<?php

/**
*
*
*/

$required = array("table" => "textNN", "db" => "resource");
check_man_params($required);

if ($_POST["db"] == "dbweb") $db = $dbweb;
if ($_POST["db"] == "dbshop") $db = $dbshop;

$query = "SELECT * FROM " . $_POST["table"];
(isset($_POST["where"]) && $_POST["where"] != "") ? $query.= " " . $_POST["where"] : $query.= "";

$res = q($query, $db, __FILE__, __LINE__);
while ($row = mysqli_fetch_assoc($res))
{
	echo '<' . $_POST["table"] . '>' . "\n";
	foreach($row as $key => $value) //	while (list($key, $val) = each ($row))
	{
		echo '	<' . $key . '><![CDATA[' . $value . ']]></' . $key . '>' . "\n";
	}
	echo '</' . $_POST["table"] . '>' . "\n";
}

echo '<num_rows>' . mysqli_num_rows($res) . '</num_rows>';