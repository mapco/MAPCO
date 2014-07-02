<?php
	/***************************************************
	 * set alls shopitem article imageprofile_ids to 4 *
	 ***************************************************/
	include("config.php");

	//get all shopitem_article_ids
	$shopitem_article=array();
	$artnr2item=array();
	$results=q("SELECT * FROM cms_articles_labels WHERE label_id=11;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$shopitem_article[$row["article_id"]]=$row["article_id"];
	}
	
	$results=q("SELECT * FROM cms_articles;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if ( isset($shopitem_article[$row["id_article"]]) )
		{
			if ( $row["imageprofile_id"]!=4 )
			{
				echo "UPDATE cms_articles SET imageprofile_id=4 WHERE id_article=".$row["id_article"].";<br />";
				q("UPDATE cms_articles SET imageprofile_id=4 WHERE id_article=".$row["id_article"].";", $dbweb, __FILE__, __LINE__);
			}
		}
	}
?>