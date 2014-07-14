<?php
	include("config.php");
	include("templates/".TEMPLATE."/header.php");
	include("templates/".TEMPLATE."/cms_leftcolumn.php");

	function str_replace_once($needle, $replace, $haystack) {
	// Looks for the first occurence of $needle in $haystack
	// and replaces it with $replace.
	$pos = strpos($haystack, $needle);
	if ($pos === false) {
	// Nothing found
	return $haystack;
	}
	return substr_replace($haystack, $replace, $pos, strlen($needle));
	}

	echo '<div id="mid_column">';

/*
//	echo '<h1>Impressum</h1>';
	if ( $_SESSION['id_site'] == 7 )
	{
		$results=q("SELECT * FROM cms_articles WHERE id_article=78087;", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$text=$row["article"];		
	}
	else
	{
		$results=q("SELECT * FROM cms_articles WHERE id_article=28289;", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$text=$row["article"];
	}
*/
	
	//GET SHOP DATA WITH SITE_ID
	$res_shop = q("SELECT * FROM shop_shops WHERE site_id = ".$_SESSION["id_site"]." AND shop_type = 1", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows( $res_shop ) == 0)
	{
		$shop["imprint_article_id"] = 0;
	}
	else
	{
		$shop = mysqli_fetch_assoc( $res_shop );
	}
	
	
	// GET IMPRESSUM
	if ($shop["imprint_article_id"] != 0)
	{
		$res_article = q("SELECT * FROM cms_articles WHERE id_article = ".$shop["imprint_article_id"], $dbweb, __FILE__, __LINE__);
		if (mysqli_num_rows( $res_article ) != 0 )
		{
			$article = mysqli_fetch_assoc( $res_article );
			
			echo $article["article"];
		}
	}
	
	
//	$text=str_replace_once('<span style="font-size: 14px;"><strong><span style="font-size:16px;">', '<h1>', $text);
//	$text=str_replace_once('</span></strong></span>', '</h1>', $text);
//	echo $text;
	
//	echo '<h2>Datenschutzerklärung</h2>';
	echo '<hr />';
	if ( $_SESSION['id_site'] == 7 )
	{
		$results=q("SELECT * FROM cms_articles WHERE id_article=78100;", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$text=$row["article"];

	}
	else
	{
		$results=q("SELECT * FROM cms_articles WHERE id_article=28294;", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$text=$row["article"];
	}
//	$text=str_replace_once('<span style="font-size:18px;"><strong>', '<h1>', $text);
//	$text=str_replace_once('</strong></span>', '</h1>', $text);
	echo $text;

	echo '</div>';

	include("templates/".TEMPLATE."/cms_rightcolumn.php");
	include("templates/".TEMPLATE."/footer.php");
?>