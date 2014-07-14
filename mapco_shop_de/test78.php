<?php
	exit;
	include("config.php");
	session_start();
	set_time_limit(3600);

	//add article images again
	$results=q("SELECT * FROM cms_files;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$original[$row["id_file"]]=$row["id_file"];
	}
	$i=0;
	$results=q("SELECT * FROM cms_articles_images2 WHERE imageformat_id=3;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$i++;
		if ( isset($original[$row["original_id"]]) )
		{
			echo $i." ".$row["file_id"]."<br />";
			q("INSERT INTO cms_articles_images (article_id, file_id, ordering, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$row["article_id"].", ".$row["file_id"].", 1, ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
		}
	}
	exit;

	//remove old shopitem images
	$shopitem=array();
	$results=q("SELECT * FROM cms_articles_labels WHERE label_id=11;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$shopitem[$row["article_id"]]=$row["article_id"];
	}
	$original=array();
	$results=q("SELECT * FROM cms_files WHERE imageformat_id=8;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$original[$row["original_id"]]=$row["id_file"];
	}
	$i=0;
	$results=q("SELECT * FROM cms_articles_images;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if ( !isset($original[$row["file_id"]]) )
		{
			$i++;
			q("DELETE FROM cms_articles_images WHERE id=".$row["id"].";", $dbweb, __FILE__, __LINE__);
//			$filename='files/'.floor(bcdiv($row["file_id"], 1000)).'/'.$row["file_id"].'.jpg';
			echo $i.' '.$row["id"];
			echo '<br />';
		}
	}
	exit;

	//remove non existing files
	$results=q("SELECT * FROM cms_files;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$filename='files/'.floor(bcdiv($row["id_file"], 1000)).'/'.$row["id_file"].'.jpg';
		if ( !file_exists($filename) )
		{
			$i++;
			$filename='files/'.floor(bcdiv($row["id_file"], 1000)).'/'.$row["id_file"].'.jpg';
			echo $i.' <a href="'.PATH.$filename.'" target="_blank">Datei '.$row["id_file"].' wurde gelöscht.</a><br />';
			q("DELETE FROM cms_files WHERE id_file=".$row["id_file"].";", $dbweb, __FILE__, __LINE__);
		}
	}

	//remove non existant files
	$results=q("SELECT * FROM cms_articles_images;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$results2=q("SELECT * FROM cms_files WHERE id_file=".$row["file_id"].";", $dbweb, __FILE__, __LINE__);
		if ( mysqli_num_rows($results)==0 )
		{
			$filename='files/'.floor(bcdiv($row["file_id"], 1000)).'/'.$row2["file_id"].'.jpg';
//			q("DELETE FROM cms_articles_images WHERE file_id=".$row["file_id"].";");
			echo '<a href="'.PATH.$filename.'" target="_blank">Datei '.$row["file_id"].' gelöscht.</a><br />';
		}
/*
		else
		{
			$row2=mysqli_fetch_array($results2)
			$filename='files/'.floor(bcdiv($row2["id_file"], 1000)).'/'.$row2["id_file"].'.'.$row2["extension"];
			if ( !file_exists($filename) )
			{
				echo '<a href="'.PATH.$filename.'" target="_blank">Datei '.$row["id_file"].' gelöscht.</a><br />';
				q("DELETE FROM cms_files WHERE original_id=".$row["id_file"].";", $dbweb, __FILE__, __LINE__);
				q("DELETE FROM cms_articles_files WHERE file_id=".$row["id_file"].";", $dbweb, __FILE__, __LINE__);
				q("DELETE FROM cms_articles_images WHERE file_id=".$row["id_file"].";", $dbweb, __FILE__, __LINE__);
				q("DELETE FROM cms_articles_videos WHERE file_id=".$row["id_file"].";", $dbweb, __FILE__, __LINE__);
				q("DELETE FROM cms_files WHERE id_file=".$row["id_file"].";", $dbweb, __FILE__, __LINE__);
			}
			else
			{
	//			echo '<a href="'.PATH.$filename.'" target="_blank">Datei '.$row["id_file"].' existiert.</a><br />';
			}
		}
*/
	}
	echo 'fertig.';
	exit;


	//create new cms_articles_images with originals only
	$original=array();
	$results=q("SELECT * FROM cms_files;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if ( $row["original_id"]!=0 )
		{
			$original[$row["id_file"]]=$row["original_id"];
		}
	}
	$ordering=array();
	$results=q("SELECT * FROM cms_articles_images;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if ( isset($original[$row["file_id"]]) ) $file_id=$original[$row["file_id"]];
		else $file_id=$row["file_id"];
		
		if ( !isset($ordering[$row["article_id"]][$file_id]) )
		{
			$ordering[$row["article_id"]][$file_id]=$file_id;
			if ( !isset($ordering[$row["article_id"]]["ordering"]) ) $ordering[$row["article_id"]]["ordering"]=1;
			else $ordering[$row["article_id"]]["ordering"]++;

			q("INSERT INTO cms_articles_images3 (article_id, file_id, ordering, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$row["article_id"].", ".$file_id.", ".$ordering[$row["article_id"]]["ordering"].", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
			q("UPDATE cms_files SET imageformat_id=".$row["imageformat_id"]." WHERE id_file=".$row["file_id"].";", $dbweb, __FILE__, __LINE__);
		}
	}
	echo 'fertig';
	exit;

	//REMOVE ALL NON-EXISTING PICTURES
	$i=0;
	$results=q("SELECT * FROM cms_articles_images;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$results2=q("SELECT * FROM cms_files WHERE id_file='".$row["file_id"]."';", $dbweb, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$filename='files/'.floor(bcdiv($row2["id_file"], 1000)).'/'.$row2["id_file"].'.'.$row2["extension"];
		if ( !file_exists($filename) )
		{
			$i++;
			echo $i.' Lösche '.$row["file_id"].'<br />';
			q("DELETE FROM cms_articles_images WHERE file_id=".$row["file_id"].";", $dbweb, __FILE__, __LINE__);
		}
	}
	exit;

	//move all images from shop_items_files to the new system
	$file2original=array();
	$results=q("SELECT * FROM cms_files WHERE original_id>0;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$file2original[$row["id_file"]]=$row["original_id"];
	}

	$item2article=array();
	$results=q("SELECT * FROM shop_items;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if ( $row["article_id"]==0 )
		{
			q("INSERT INTO cms_articles (title, firstmod, lastmod) VALUES('', ".time().", ".time().");", $dbweb, __FILE__, __LINE__);
			$article_id=mysqli_insert_id($dbweb);
			$row["article_id"]=$article_id;
			q("INSERT INTO cms_articles_labels (article_id, label_id) VALUES(".$article_id.", 11);", $dbweb, __FILE__, __LINE__);
			q("UPDATE shop_items SET article_id=".$article_id." WHERE id_item=".$row["id_item"].";", $dbshop, __FILE__, __LINE__);
		}
		$item2article[$row["id_item"]]=$row["article_id"];
	}

	$old=array();
	$results=q("SELECT * FROM shop_items_files;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$old[]=$row;
	}
	
	$new=array();
	$results=q("SELECT * FROM cms_articles_images WHERE imageformat_id=8;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$new[$row["file_id"]]=$row["file_id"];
	}
	
	$j=0;
	for($i=0; $i<sizeof($old); $i++)
	{
		if ( !isset($new[$old[$i]["file_id"]]) )
		{
			if ( isset($item2article[$old[$i]["item_id"]]) )
			{
				echo $j++;
				echo " ".$old[$i]["file_id"];
				echo '<br />';
//				echo $j.'. '.$old[$i]["file_id"].'<br />';
				q("INSERT INTO cms_articles_images (article_id, file_id, original_id, imageformat_id) VALUES(".$item2article[$old[$i]["item_id"]].", ".$old[$i]["file_id"].", ".$file2original[$old[$i]["file_id"]].", 8);", $dbweb, __FILE__, __LINE__);
			}
		}
	}
	
	
?>