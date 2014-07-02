<?php
//	include("Wartung.php");
//	exit;
	include("config.php");


	if (!function_exists("startsWith"))
	{
		function startsWith($check, $startStr)
		{
			if (!is_string($check) || !is_string($startStr) || strlen($check)<strlen($startStr)) {
				return false;
			}
	
			return (substr($check, 0, strlen($startStr)) === $startStr);
		}
	}

	//redirect other URLs
	if ($_SERVER['HTTP_HOST'] != "localhost" and $_SERVER["SERVER_NAME"]!=LIVE)
	{
		$url=PATHLANG;
		if (isset($_GET["url"])) $url.=$_GET["url"];
		header("HTTP/1.1 301 Moved Permanently");
		header("location: ".$url);
		exit;
	}

	//get all menus
	$menus=array();
	$results=q("SELECT id_menu FROM cms_menus WHERE site_id IN(0, ".$_SESSION["id_site"].");", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$menus[]=$row["id_menu"];
	}

	//get all menuitems
	$menuitems=array();
	$menuitem_ids=array();
	$results=q("SELECT * FROM cms_menuitems WHERE menu_id IN(".implode(", ", $menus).");", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$menuitem_ids[]=$row["id_menuitem"];
		$menuitems[$row["id_menuitem"]]=$row;
	}

	//static links
	if( $_GET["url"]=="" )
	{
		$results=q("SELECT * FROM cms_menuitems WHERE home=1 AND id_menuitem IN(".implode(", ", $menuitem_ids).") LIMIT 1;", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_assoc($results);
		$results=q("SELECT * FROM cms_menuitems_languages WHERE menuitem_id=".$row["id_menuitem"]." AND language_id=".$_SESSION["id_language"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
		if( mysqli_num_rows($results)==0 )
		{
			$results=q("SELECT * FROM cms_menuitems_languages WHERE menuitem_id=".$row["id_menuitem"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
		}
	}
	else
	{
		$results=q("SELECT * FROM cms_menuitems_languages WHERE alias='".mysqli_real_escape_string($dbweb, $_GET["url"])."' AND language_id=".$_SESSION["id_language"]." AND menuitem_id IN(".implode(", ", $menuitem_ids).") LIMIT 1;", $dbweb, __FILE__, __LINE__);
	}
	//static links other languages
	if (mysqli_num_rows($results)==0)
	{
		$results=q("SELECT * FROM cms_menuitems_languages WHERE alias='".mysqli_real_escape_string($dbweb, $_GET["url"])."' AND menuitem_id IN(".implode(", ", $menuitem_ids).") LIMIT 1;", $dbweb, __FILE__, __LINE__);
		//translation available?
		if (mysqli_num_rows($results)>0)
		{
			$row=mysqli_fetch_assoc($results);
			$results=q("SELECT * FROM cms_menuitems_languages WHERE menuitem_id=".$row["menuitem_id"]." AND language_id=".$_SESSION["id_language"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
			$row=mysqli_fetch_assoc($results);
			$url=PATH.$row["alias"];
			header("HTTP/1.1 301 Moved Permanently");
			header("location: ".$url);
			exit;
		}
		else $results=q("SELECT * FROM cms_menuitems_languages WHERE alias='".mysqli_real_escape_string($dbweb, $_GET["url"])."' AND menuitem_id IN(".implode(", ", $menuitem_ids).") LIMIT 1;", $dbweb, __FILE__, __LINE__);
	}
	if (mysqli_num_rows($results)>0)
	{
		$row=mysqli_fetch_array($results);
		$_GET["id_menuitem"]=$row["menuitem_id"];
		$parse=parse_url($menuitems[$row["menuitem_id"]]["link"]);
		$link=$parse["path"];
		if (isset($parse["query"])) $vars=explode("&", $parse["query"]); else $vars=array();
		for($i=0; $i<sizeof($vars); $i++)
		{
			$var=explode("=", $vars[$i]);
			$_GET[$var[0]]=$var[1];
		}
		//seo stuff
		$meta_title=$row["meta_title"];
		$meta_description=$row["meta_description"];
		$meta_keywords=$row["meta_keywords"];
		//redirect to link
		if($menuitems[$row["menuitem_id"]]["local"]==1) include(FRONTEND.$link);
		else include($link);
		unset($menus);
		unset($menuitem_ids);
		unset($menuitems);
		exit;
	}
	else
	{
		//get all menuitems
		$menuitems=array();
		$menuitem_ids=array();
		$results=q("SELECT * FROM cms_menuitems WHERE dynamic=1 AND menu_id IN(".implode(", ", $menus).");", $dbweb, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			$menuitem_ids[]=$row["id_menuitem"];
			$menuitems[$row["id_menuitem"]]=$row;
		}
		//dynamic links
		$found=false;
		if( sizeof($menuitem_ids)>0 )
		{
			$results=q("SELECT * FROM cms_menuitems_languages WHERE language_id=".$_SESSION["id_language"]." AND menuitem_id IN(".implode(", ", $menuitem_ids).");", $dbweb, __FILE__, __LINE__);
			while( $row=mysqli_fetch_assoc($results) )
			{
				if( startsWith($_GET["url"], $row["alias"]) !== false )
				{
					$_GET["static"]=$row["alias"];
					$found=true;
					break;
				}
			}
		}
		//dynamic links other languages
		if( !$found )
		{
			$found=false;
			if( sizeof($menuitem_ids)>0 )
			{
				$results=q("SELECT * FROM cms_menuitems_languages WHERE menuitem_id IN(".implode(", ", $menuitem_ids).");", $dbweb, __FILE__, __LINE__);
				while( $row=mysqli_fetch_assoc($results) )
				{
					if( startsWith($_GET["url"], $row["alias"]) !== false )
					{
						//translation available?
						if (mysqli_num_rows($results)>0)
						{
							$results2=q("SELECT * FROM cms_menuitems_languages WHERE menuitem_id=".$row["menuitem_id"]." AND language_id=".$_SESSION["id_language"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
							if ( mysqli_num_rows( $results2 ) > 0 )
							{
								$row2=mysqli_fetch_assoc($results2);
								$url=PATH.str_replace($row["alias"], $row2["alias"], $_GET["url"]);
								header("HTTP/1.1 301 Moved Permanently");
								header("location: ".$url);
								exit;
							}
						}
						$_GET["static"]=$row["alias"];
						$found=true;
						break;
					}
				}
			}
		}
		if ($found)
		{
			//get normal GET variables
			$_GET["id_menuitem"]=$row["menuitem_id"];
			$parse=parse_url($menuitems[$row["menuitem_id"]]["link"]);
			$link=$parse["path"];
			if (isset($parse["query"])) $vars=explode("&", $parse["query"]); else $vars=array();
			for($i=0; $i<sizeof($vars); $i++)
			{
				$var=explode("=", $vars[$i]);
				$_GET[$var[0]]=$var[1];
			}
			//get SEO GET variables
			$getvars=substr($_GET["url"], strlen($row["alias"]));
			$getvars=explode("/", $getvars);
			for($i=0; $i<sizeof($getvars); $i++)
			{
				$_GET["getvars".($i+1)]=$getvars[$i];
			}
			//seo stuff
			$meta_title=$row["meta_title"];
			$meta_description=$row["meta_description"];
			$meta_keywords=$row["meta_keywords"];
			//redirect to link
			unset($menus);
			unset($menuitem_ids);
			unset($menuitems);
			if($menuitems[$row["menuitem_id"]]["local"]==1) include(FRONTEND.$link);
			else include($link);
			exit;
		}
		else
		{
			header('HTTP/1.0 404 Not Found');
			echo "<h1>404 Not Found</h1>";
			echo "The page that you have requested could not be found.";
			exit;
		}
	}
?>
