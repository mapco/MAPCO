<?php

	check_man_params(array("ids" => "text"));

	$id=explode(", ", $_POST["ids"]);
	
	//update cms_menuitems
	for($i=0; $i<sizeof($id); $i++)
	{
		$data=array();
		$data["ordering"]=$i+1;
		q_update("cms_menuitems", $data, "WHERE id_menuitem=".$id[$i], $dbweb, __FILE__, __LINE__);
	}

?>