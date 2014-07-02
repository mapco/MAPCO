<?php
	/*************************
	********** SOA 2 *********
	*************************/
	
	$required=array("nvp_id"	=> "numeric");
	check_man_params($required);
	
	$xml = '';

	$results=q("SELECT id FROM shop_items_nvp WHERE id=".$_POST["nvp_id"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{ 
		$xml .= '	<Error>Zuordnung von Artikel und Shopartikel nicht gefunden</Error>'."\n";
	}
	else
	{ 
		q("DELETE FROM shop_items_nvp WHERE id = '".$_POST["nvp_id"]."';", $dbshop, __FILE__, __LINE__);	
	}
	print $xml;
?>