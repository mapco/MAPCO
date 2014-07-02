<?php

include("config.php");

/*
if($_POST["action"]=="ap_image")
{
	$originals=array();
	$results=q("SELECT * FROM shop_items_files WHERE item_id=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$results2=q("SELECT * FROM cms_files WHERE id_file=".$row["file_id"].";", $dbweb, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$originals[$row2["original_id"]]=$row2["original_id"];
	}
	
}


	$results=q("SELECT * FROM shop_items;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		q("INSERT INTO cms_articles (imageprofile_id) VALUES(4);", $dbweb, __FILE__, __LINE__);
		$id_article=mysqli_insert_id($dbweb);
		q("UPDATE shop_items SET article_id = ".$id_article." WHERE id_item=".$row["id_item"].";", $dbshop, __FILE__, __LINE__);
		q("INSERT INTO cms_articles_labels (article_id, label_id) VALUES(".$id_article.", 11)", $dbweb, __FILE__, __LINE__);
		echo $row["id_item"].' erledigt<br />';
	}

	$results=q("SELECT * FROM shop_items_files;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$results2=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		q("INSERT INTO cms_articles_images (article_id, file_id, imageformat_id) VALUES(".$row2["article_id"].", ".$row["file_id"].", 8);", $dbweb, __FILE__, __LINE__);
		echo $row["item_id"].' erledigt<br />';
	}
*/
	
	
	$results=q("SELECT * FROM shop_items_files;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$results2=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$results3=q("SELECT * FROM cms_files WHERE id_file=".$row["file_id"].";", $dbweb, __FILE__, __LINE__);
		$row3=mysqli_fetch_array($results3);
//		q("INSERT INTO cms_articles_images (article_id, file_id, original_id, imageformat_id) VALUES(".$row2["article_id"].", ".$row3["original_id"].", 0, 0);", $dbweb, __FILE__, __LINE__);
		q("UPDATE cms_articles_images SET original_id=".$row3["original_id"]." WHERE file_id=".$row["file_id"].";", $dbweb, __FILE__, __LINE__);
		echo $row["item_id"].' erledigt<br />';
	}



?>