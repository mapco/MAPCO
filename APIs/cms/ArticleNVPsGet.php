<?php
	/*************************
	********** SOA 2 *********
	*************************/
	
	$required=array("item_id"	=> "numeric", "lang"	=> "text");
	
	check_man_params($required);
	
	$item_id = $_POST['item_id'];
	$lang = $_POST['lang'];
		
	$xml = '';
		
	$results=q("SELECT * FROM shop_items_nvp_categories ORDER BY ordering ASC;", $dbshop, __FILE__, __LINE__);
	while($row = mysqli_fetch_assoc($results))
	{	
		$xml .= '<category>'."\n";
		foreach($row as $key => $value)
		{
			$xml .= '<'.$key.'>'.$value.'</'.$key.'>'."\n";
		}
	
		$results2=q("SELECT COUNT(id) AS counting FROM shop_items_nvp WHERE item_id=".$item_id." AND category_id=".$row['id']." AND language_id=".$lang.";", $dbshop, __FILE__, __LINE__);
				
		while($row2 = mysqli_fetch_assoc($results2))
		{	
			$xml .= '<counting>'.$row2['counting'].'</counting>'."\n";
		}		
		$xml .= '</category>'."\n";
	}
	
	
		
	print $xml;
?>