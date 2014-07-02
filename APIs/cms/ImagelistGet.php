<?php
	$items=array();
	if( $_SESSION["id_user"]==21371 or $_SESSION["id_user"]==24493 ) $query="SELECT * FROM shop_items WHERE active>0 AND menuitem_id=105;";
	else $query="SELECT * FROM shop_items WHERE active>0;";
	$results=q($query, $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$items[$row["article_id"]]=$row["MPN"];
	}

	$files=array();
	if( $_SESSION["id_user"]==21371 or $_SESSION["id_user"]==24493 ) $query="SELECT * FROM cms_files WHERE imageformat_id=19;";
	else $query="SELECT * FROM cms_files WHERE imageformat_id=9;";
	$results=q($query, $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$files[$row["original_id"]]=$row;
	}

	$alphabet=array("a", "b", "c", "d", "e", "f", "g", "h");
	$id_article=0;
	echo '<ImagelistGetResponse>';
	$results=q("SELECT * FROM cms_articles_images ORDER BY article_id, ordering;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if( $id_article != $row["article_id"] )
		{
			$i=0;
			$id_article=$row["article_id"];
		}
		if( isset($items[$row["article_id"]]) )
		{
//			$results2=q("SELECT * FROM cms_files WHERE original_id=".$row["file_id"]." AND imageformat_id=9;", $dbweb, __FILE__, __LINE__);
//			if( mysqli_num_rows($results2)>0 )
			if( isset($files[$row["file_id"]]) )
			{
//				$row2=mysqli_fetch_array($results2);
				$row2=$files[$row["file_id"]];
				$dir=floor($row2["id_file"] / 1000);
				$filename=$items[$row["article_id"]].$alphabet[$i];
				$i++;
				$filename=str_replace("/", "_", $filename);
				echo '	<Image filename="'.$filename.'.jpg">/files/'.$dir.'/'.$row2["id_file"].'.'.$row2["extension"].'</Image>'."\n";
			}
		}
	}
	echo '</ImagelistGetResponse>';
?>