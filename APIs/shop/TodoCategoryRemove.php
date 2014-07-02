<?php

	check_man_params(array("id" => "numeric"));
	
	//ordering neu setzen
	$results=q("SELECT * FROM todo_categories WHERE id=".$_POST["id"].";", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	
	$results2=q("SELECT * FROM todo_categories WHERE ordering>".$row["ordering"]." AND parent_id=".$row["parent_id"].";", $dbshop, __FILE__, __LINE__);
	while($row2=mysqli_fetch_array($results2))
	{
		$results3=q("UPDATE todo_categories SET ordering=".($row2["ordering"]*1-1)." WHERE id=".$row2["id"].";", $dbshop, __FILE__, __LINE__);	
	}
	
	//Kategorie löschen
	$results=q("DELETE FROM todo_categories WHERE id=".$_POST["id"].";", $dbshop, __FILE__, __LINE__);
	
?>
