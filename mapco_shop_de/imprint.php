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

//	echo '<h1>Impressum</h1>';
	$results=q("SELECT * FROM cms_articles WHERE id_article=28289;", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$text=$row["article"];
//	$text=str_replace_once('<span style="font-size: 14px;"><strong><span style="font-size:16px;">', '<h1>', $text);
//	$text=str_replace_once('</span></strong></span>', '</h1>', $text);
	echo $text;
	
//	echo '<h2>Datenschutzerklärung</h2>';
	echo '<hr />';
	$results=q("SELECT * FROM cms_articles WHERE id_article=28294;", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$text=$row["article"];
//	$text=str_replace_once('<span style="font-size:18px;"><strong>', '<h1>', $text);
//	$text=str_replace_once('</strong></span>', '</h1>', $text);
	echo $text;

	echo '</div>';

	include("templates/".TEMPLATE."/cms_rightcolumn.php");
	include("templates/".TEMPLATE."/footer.php");
?>