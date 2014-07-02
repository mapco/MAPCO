<?php

	$required = array("error_id" => "numericNN");
	check_man_params($required);

	q("DELETE FROM idims_zlg_error WHERE id_error = ".$_POST["error_id"], $dbshop, __FILE__, __LINE__);
	

?>