<?php
	///äöüÄÖÜ UTF-8
	if (!function_exists("url_encode"))
	{
		function url_encode($url)
		{
//			$url=utf8_decode($url);
			$search =  array("&", "$", "+", ",", "/", ":", ";", "=", "?", "@", " ", "ä", "ö", "ü", "Ä", "Ö", "Ü", "ß", "----", "---", "--");
			$replace = array("-", "-", "-", "-", "-", "-", "-", "-", "-", "-", "-", "ae", "oe", "ue", "AE", "OE", "UE", "ss", "-", "-", "-");
			$url=str_replace($search, $replace, $url);
//			$url=urlencode($url);
			$url=strtolower($url);
//			return( utf8_encode($url) );
			return( $url );
		}
	}
?>