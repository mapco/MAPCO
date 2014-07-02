<?php

	check_man_params(array("id" => "numeric"));
	
	$xml='';
	
	$results = q("SELECT * FROM todo_tasks WHERE id = " . $_POST["id"] . " ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	
	$xml.='	<task>'."\n";
	$xml.='		<task_id>'.$row["id"].'</task_id>'."\n";
	$xml.='		<task_cat_id>'.$row["cat_id"].'</task_cat_id>'."\n";
	$xml.='		<task_title><![CDATA['.$row["title"].']]></task_title>'."\n";
	$xml.='		<task_description><![CDATA['.$row["description"].']]></task_description>'."\n";
//	$xml.='		<task_ordering>'.$row["ordering"].'</task_ordering>'."\n";
//	$xml.='		<task_in_work_by>'.$row["in_work_by"].'</task_in_work_by>'."\n";
//	$xml.='		<task_in_work_by_name>'.$in_work_by_name.'</task_in_work_by_name>'."\n";
//	$xml.='		<task_done>'.$row["done"].'</task_done>'."\n";
	$xml.='	</task>'."\n";

	//get category-paths
	$results = q("SELECT * FROM todo_categories;", $dbshop, __FILE__, __LINE__);
	$xml.='<categories>'."\n";
	while($row=mysqli_fetch_array($results))
	{
		$results2 = q("SELECT * FROM todo_categories WHERE parent_id = " . $row["id"] . ";", $dbshop, __FILE__, __LINE__);
		if(mysqli_num_rows($results2) == 0) {
			$xml.='	<cat>'."\n";
			$xml.='		<cat_id>'.$row["id"].'</cat_id>'."\n";
			$cat_path=$row["title"];
			$p=$row["parent_id"];
			while($p>=0)
			{
				$results3 = q("SELECT * FROM todo_categories WHERE id=" . $p . ";", $dbshop, __FILE__, __LINE__);
				$row3 = mysqli_fetch_array($results3);
				if ($p != 0)
					$cat_path = $row3["title"] . "/" . $cat_path;
				$p = $row3["parent_id"];
				if ($p == 0)
					break;
			}
			$xml.='		<cat_path>'.$cat_path.'</cat_path>'."\n";
			$xml.='	</cat>'."\n";
		}
	}
	$xml.='</categories>'."\n";
	echo $xml;
?>
