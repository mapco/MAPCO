<?php

	//************************ 
	//*     SOA2-SERVICE     *
	//************************
	
	$required=array("key"	=> "text",
					"value"	=> "text");
	
	check_man_params($required);
	
	$_SESSION[$_POST["key"]]=$_POST["value"];
	
?>