<?

	$sql="select * from cms_conversations_posts where id_conv = '".$_POST["conv_id"]."' order by post_date";
	$results=q($sql, $dbweb, __FILE__, __LINE__);
	while( $row=mysql_fetch_array($results) )  
		{
			$sql2 = "select title, article from cms_articles where id_article = '".$row["id_cms_article"]."'";
			$results2=q($sql2, $dbweb, __FILE__, __LINE__);
			while( $row2=mysql_fetch_array($results2) ) 
			{
				$msg_history.=$row2["article"];
			}
		}
		
	echo $msg_history;
?>