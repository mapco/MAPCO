<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["list"]) )
	{
		echo '<GartSortKeywordResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Keyword-Liste nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Keyword-Liste übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartSortKeywordResponse>'."\n";
		exit;
	}
/*	for($i=0; $i<sizeof($_POST["list"]); $i++)
	{
		echo $id_article=str_replace("keywordid", "", $_POST["list"][$i]);
		echo $id_article."+";
		//if ( $_POST["id_label"]>0 )
		//{
		//	q("UPDATE cms_articles_labels SET ordering=".($i+1)." WHERE article_id=".$id_article." AND label_id=".$_POST["id_label"].";", $dbweb, __FILE__, __LINE__);
	//	}
	//	else
		{
			//q("UPDATE cms_articles SET ordering=".($i+1)." WHERE id_article=".$id_article.";", $dbweb, __FILE__, __LINE__);
		}
	}
*/
	$list=$_POST["list"];
	$ordercount=0;
	while (list($key, $val) = each ($list)) 

	{
		if ($val!="") 
		{ 
			$ordercount++;
			$keywordid=number_format(str_replace("keywordid", "", $val));
			$sql="UPDATE shop_items_keywords SET ordering = ".$ordercount." WHERE id_keyword = ".$keywordid.";";
			q($sql, $dbshop, __FILE__, __LINE__);

		}
			 
	}
	
	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<GartSortKeywordResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</GartSortKeywordResponse>'."\n";

?>