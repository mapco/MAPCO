<?php

/**
 * Table Data Select
 * @params
 * - DB: select the database
 * - table: define the database table
 * - select: select * or define custom selects column
 * - join: JOIN, LEFT JOIN, INNER JOIN, RIGHT JOIN
 *
 * @return xml result
 */

$required = array("table" => "textNN", "db" => "resource");
check_man_params($required);

// keep post submit
$post = $_POST;

if ($post["db"] == "dbweb") $db = $dbweb;
if ($post["db"] == "dbshop") $db = $dbshop;

/**
 * define data select column
 */
if (isset($post["select"]) && $post["select"] != null) {
	$select = $post["select"];
} else {
	$select = "*";
}

/**
 * special table defines
 */
if ($post['join'] && $post['join'] != null) {
    $join = " " . $post['join'];
} else {
    $join = "";
}

/**
 * Data Query
 */
$query = "SELECT " . $select . " FROM " . $post["table"] . $join;
if (isset($post["where"]) && $post["where"] != "") {
	$query.= " " . $post["where"];
}

$res = q($query, $db, __FILE__, __LINE__);
$xml = null;
while ($row = mysqli_fetch_assoc($res))
{
	$xml.= '<' . $post["table"] . '>' . "\n";
	foreach($row as $key => $value)
	{
		if (is_numeric($value) or $value=="")
		{
			$xml.= '	<' . $key . '>' . $value . '</' . $key . '>' . "\n";
		} else {
			if (strpos($value, '[CDATA[') === false) {
				$xml.= '	<' . $key . '><![CDATA[' . $value . ']]></' . $key . '>' . "\n";
			} else {
				$xml.=  $value . "\n";
			}	
		}
	}
	$xml.= '</' . $post["table"] . '>' . "\n";
}
echo $xml . '<num_rows>' . mysqli_num_rows($res) . '</num_rows>';