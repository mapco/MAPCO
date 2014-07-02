<?php

	check_man_params(array("id" => "numeric"));
	
	//ordering neu setzen
	$results=q("SELECT * FROM todo_tasks WHERE id=".$_POST["id"].";", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	
	$results2=q("SELECT * FROM todo_tasks WHERE ordering>".$row["ordering"]." AND cat_id=".$row["cat_id"].";", $dbshop, __FILE__, __LINE__);
	while($row2=mysqli_fetch_array($results2))
	{
		$results3=q("UPDATE todo_tasks SET ordering=".($row2["ordering"]*1-1)." WHERE id=".$row2["id"].";", $dbshop, __FILE__, __LINE__);	
	}
	
	//priority neu setzen
	$results4=q("SELECT * FROM todo_tasks WHERE priority>".$row["priority"].";", $dbshop, __FILE__, __LINE__);
	while($row4=mysqli_fetch_array($results4))
	{
		$results5=q("UPDATE todo_tasks SET priority=".($row4["priority"]*1-1)." WHERE id=".$row4["id"].";", $dbshop, __FILE__, __LINE__);
	}
	
	//private_priority neu setzen
	$results6=q("SELECT * FROM todo_tasks WHERE in_work_by=".$row["in_work_by"]." AND private_priority>".$row["private_priority"].";", $dbshop, __FILE__, __LINE__);
	while($row6=mysqli_fetch_array($results6))
	{
		$results7=q("UPDATE todo_tasks SET private_priority=".($row6["private_priority"]*1-1)." WHERE id=".$row6["id"].";", $dbshop, __FILE__, __LINE__);
	}
	
	//Task löschen
	$results=q("DELETE FROM todo_tasks WHERE id=".$_POST["id"].";", $dbshop, __FILE__, __LINE__);
	
?>
