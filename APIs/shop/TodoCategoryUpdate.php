<?php
	
	check_man_params(array("mode"	=> "text"));
	
	if($_POST["mode"]=="order")
	{
		$required=array("ids" 	=> "numeric");
		
		check_man_params($required);
	}
	
	//sortieren innerhalb Kategorie
	if($_POST["mode"]=="order")
	{
		for($i=0; $i<sizeof($_POST["ids"]); $i++)
		{
			$results=q("UPDATE todo_categories SET ordering=".($i+1)." WHERE id=".$_POST["ids"][$i].";", $dbshop, __FILE__, __LINE__);
		}
	}

?>
