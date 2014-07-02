<?php

	//************************ 
	//*     SOA2-SERVICE     *
	//************************
	
	$required = array( "key"	=> "text" );
	
	check_man_params( $required );
	
	if ( isset( $_SESSION[$_POST['key']] ) )
	{
		unset( $_SESSION[$_POST['key']] );
	}
	
?>