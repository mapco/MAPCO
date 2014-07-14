<?php
	include("config.php");
	include("templates/".TEMPLATE."/header.php");
	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	include("functions/cms_show_article.php");
	
	echo 'aaa';
	
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

//	echo '<h1>Allgemeine Geschäftsbedingungen und Verbraucherinformationen</h1>';
	if ( $_SESSION['id_site'] == 7 )
	{
		$results=q("SELECT * FROM cms_articles WHERE id_article=78065;", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$text=$row["article"];
	}
	else
	{
		$results=q("SELECT * FROM cms_articles WHERE id_article=28290;", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$text=$row["article"];
	}
//	$text=str_replace_once('<span style="font-size:18px;">', '<h1>', $text);
//	$text=str_replace_once('</span>', '</h1>', $text);
	echo $text;
	
	echo '<hr />';
//	echo '<h1>Widerrufsrecht für Verbraucher</h1>';
	if ( $_SESSION['id_site'] == 7 )
	{
		$results=q("SELECT * FROM cms_articles WHERE id_article=78094;", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$text=$row["article"];
	}
	else
	{
		$results=q("SELECT * FROM cms_articles WHERE id_article=28291;", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$text=$row["article"];		
	}
//	$text=str_replace_once('<span style="font-size:18px;"><strong>', '<h2>', $text);
//	$text=str_replace_once('</strong></span>', '</h2>', $text);
	echo $text;

/*
	echo '<hr />';
//	echo '<h1>Rückgaberecht</h1>';
	$results=q("SELECT * FROM cms_articles WHERE id_article=28292;", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$text=$row["article"];
	$text=str_replace_once('<span style="font-size:18px;"><strong>', '<h2>', $text, 1);
	$text=str_replace_once('</strong></span>', '</h2>', $text);
	echo $row["article"];
*/
	
	echo '<hr />';
//	echo '<h1>Zahlung und Versand</h1>';
	if ( $_SESSION['id_site'] == 7 )
	{
		$results=q("SELECT * FROM cms_articles WHERE id_article=78104;", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$text=$row["article"];
	}
	else
	{
		$results=q("SELECT * FROM cms_articles WHERE id_article=28293;", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$text=$row["article"];		
	}
//	$text=str_replace_once('<span style="font-size:18px;"><strong>', '<h2>', $text);
//	$text=str_replace_once('</strong></span>', '</h2>', $text);
	echo $text;
	
	echo '</div>';

	include("templates/".TEMPLATE."/cms_rightcolumn.php");
	include("templates/".TEMPLATE."/footer.php");
?>