<?php

	check_man_params(array("article_id"	=> "numeric",
						   "label_id"	=> "numeric"));
						   
	//get ordering
	$results=q("SELECT COUNT(id) FROM cms_articles_labels WHERE label_id=".$_POST["label_id"].";", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$ordering=$row[0]+1;
	
	$insert_data=array();
	$insert_data["article_id"]=$_POST["article_id"];
	$insert_data["label_id"]=$_POST["label_id"];
	$insert_data["ordering"]=$ordering;
	
	$results=q_insert("cms_articles_labels", $insert_data, $dbweb, __FILE__, __LINE__);

?>