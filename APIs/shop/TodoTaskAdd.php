<?php

	check_man_params(array(	"title" => "text",
							"description" => "text",
							"cat_id" => "numeric"));
	
	//priority bestimmen
	
	$priority=0;
	$results=q("SELECT * FROM todo_tasks ORDER BY priority DESC", $dbshop, __FILE__, __LINE__);
	if(mysqli_num_rows($results)==0)
		$priority=1;
	else
	{
		$row=mysqli_fetch_array($results);
		if($row["priority"]<=20)
			$priority=$row["priority"]+1;
		else
			$priority=21;
	}
	
	$results2=q("SELECT * FROM todo_tasks WHERE priority>=".$priority.";", $dbshop, __FILE__, __LINE__);
	if(mysqli_num_rows($results2)>0)
	{
		while($row2=mysqli_fetch_array($results2))
		{
			$results3=q("UPDATE todo_tasks SET priority=".($row2["priority"]+1)." WHERE id=".$row2["id"].";", $dbshop, __FILE__, __LINE__);
		}
	}
	
	// Task einfügen
	$results=q("SELECT * FROM todo_tasks WHERE cat_id=".$_POST["cat_id"]." ORDER BY ordering DESC;", $dbshop, __FILE__, __LINE__);
	if(mysqli_num_rows($results)==0)
	{
		$results2=q("INSERT INTO todo_tasks (cat_id, title, description, ordering, priority, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$_POST["cat_id"].", '".$_POST["title"]."', '".$_POST["description"]."', 1, ".$priority.", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
	}
	else
	{
		$row=mysqli_fetch_array($results);
		$ordering_new=$row["ordering"]*1+1;
		$results3=q("INSERT INTO todo_tasks (cat_id, title, description, ordering, priority, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$_POST["cat_id"].", '".$_POST["title"]."', '".$_POST["description"]."', ".$ordering_new.", ".$priority.", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
	}

?>
