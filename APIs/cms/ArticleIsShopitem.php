<?php

	/*************************
	********** SOA 2 *********
	***** Author Sven E. *****
	*** Lastmod 25.03.2014 ***
	*************************/
	
	$required=array("article_id"	=> "numeric");
	check_man_params($required);

	$results=q("SELECT id_item FROM shop_items WHERE article_id=".$_POST['article_id'].";", $dbshop, __FILE__, __LINE__);
	if(mysqli_num_rows($results) > 0)
	{
		$row = mysqli_fetch_assoc($results);
		$article_id = $_POST['article_id'];
		$shopitem = $row['id_item'];
	}
	else
	{
		$article_id = $_POST['article_id'];
		$shopitem = 0;
	}
	
	$xml = '<shopitem>'.$shopitem.'</shopitem>'."\n";
	$xml .= '<article_id>'.$article_id.'</article_id>'."\n";
	
	print $xml;
?>