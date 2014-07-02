<?php

	check_man_params(array("id" => "numeric"));
	
	$xml='';
	
	$results = q("SELECT * FROM todo_tasks ORDER BY firstmod DESC LIMIT 10;", $dbshop, __FILE__, __LINE__);
	$row = mysqli_fetch_array($results);
		
	while($row = mysqli_fetch_array($results))
	{
		$xml.='	<task>' . "\n";
		$xml.='		<task_id>' . $row["id"] . '</task_id>' . "\n";
		$xml.='		<task_cat_id>' . $row["cat_id"] . '</task_cat_id>' . "\n";
		$xml.='		<task_title><![CDATA[' . $row["title"] . ']]></task_title>' . "\n";
		$xml.='		<task_description><![CDATA[' . $row["description"] . ']]></task_description>' . "\n";
		$xml.='	</task>' . "\n";		
	}
	
	echo $xml;	
