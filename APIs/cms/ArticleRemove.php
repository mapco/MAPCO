<?php

	check_man_params(array("id_article"	=> "numeric"));

	//remove images
	$results=q("SELECT * FROM cms_articles_images WHERE article_id=".$_POST["id_article"].";", $dbweb, __FILE__, __LINE__);
	if( mysqli_num_rows($results)>0 )
	{
		echo 'Erst Bilder löschen.';
		exit;
	}

	//remove file attachments
	$results=q("SELECT * FROM cms_articles_files WHERE article_id=".$_POST["id_article"].";", $dbweb, __FILE__, __LINE__);
	if( mysqli_num_rows($results)>0 )
	{
		echo 'Erst Anhänge löschen.';
		exit;
	}

	//remove videos
	$results=q("SELECT * FROM cms_articles_videos WHERE article_id=".$_POST["id_article"].";", $dbweb, __FILE__, __LINE__);
	if( mysqli_num_rows($results)>0 )
	{
		echo 'Erst Videos löschen.';
		exit;
	}

	//remove GARTs
	$results=q("SELECT * FROM cms_articles_gart WHERE article_id=".$_POST["id_article"].";", $dbweb, __FILE__, __LINE__);
	if( mysqli_num_rows($results)>0 )
	{
		echo 'Erst GARTs löschen.';
		exit;
	}

	//remove shopitems
	$results=q("SELECT * FROM cms_articles_shopitems WHERE article_id=".$_POST["id_article"].";", $dbweb, __FILE__, __LINE__);
	if( mysqli_num_rows($results)>0 )
	{
		echo 'Erst Shopartikel löschen.';
		exit;
	}

	//remove labels
	q("DELETE FROM cms_articles_labels WHERE article_id=".$_POST["id_article"].";", $dbweb, __FILE__, __LINE__);

	//remove translations
	$results=q("SELECT * FROM cms_articles WHERE article_id=".$_POST["id_article"].";", $dbweb, __FILE__, __LINE__);
	if( mysqli_num_rows($results)>0 )
	{
		echo 'Erst Übersetzungen löschen.';
		exit;
	}

	//remove article
	q("DELETE FROM cms_articles WHERE id_article=".$_POST["id_article"].";", $dbweb, __FILE__, __LINE__);
						   
?>