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

	//show homepage
	echo $_GET["url"];
	if ( !isset($_GET["url"]) or $_GET["url"]=="" )
	{
		if($_SESSION["id_site"]==1) include("home.php");
		else include("templates/".TEMPLATE."/home.php");
	}


		//MAPCO-TV
		elseif ( $_GET["url"]!="presse/mapco-tv/" and startsWith($_GET["url"], "presse/mapco-tv/") )
		{
			echo 'aaa';
			//get id_item
			$cut=substr($_GET["url"], 16, 1000);
			$_GET["id_article"]=substr($cut, 0, strpos($cut, "/"));
			if ( is_numeric($_GET["id_article"]) )
			{
				//get title
				$cut=substr($_GET["url"], 16, 1000);
				$title=stripslashes(substr($cut, strpos($cut, "/")+1, 1000));
				include('home.php');
			}
			else
			{
				header("HTTP/1.0 404 Not Found");
				exit;
			}
		}
		//Pressemitteilungen
		elseif ( $_GET["url"]!="presse/pressemitteilungen/" and startsWith($_GET["url"], "presse/pressemitteilungen/") )
		{
			//get id_item
			$cut=substr($_GET["url"], 26, 1000);
			$_GET["id_article"]=substr($cut, 0, strpos($cut, "/"));
			if ( is_numeric($_GET["id_article"]) )
			{
				//get title
				$cut=substr($_GET["url"], 26, 1000);
				$title=stripslashes(substr($cut, strpos($cut, "/")+1, 1000));
				include('home.php');
			}
			else
			{
				header("HTTP/1.0 404 Not Found");
				exit;
			}
		}
		//Presseberichte
		elseif ( $_GET["url"]!="presse/presseberichte/" and startsWith($_GET["url"], "presse/presseberichte/") )
		{
			//get id_item
			$cut=substr($_GET["url"], 22, 1000);
			$_GET["id_article"]=substr($cut, 0, strpos($cut, "/"));
			if ( is_numeric($_GET["id_article"]) )
			{
				//get title
				$cut=substr($_GET["url"], 22, 1000);
				$title=stripslashes(substr($cut, strpos($cut, "/")+1, 1000));
				include('home.php');
			}
			else
			{
				header("HTTP/1.0 404 Not Found");
				exit;
			}
		}
		//newsletter
		elseif ( $_GET["url"]!="newsletter/" and startsWith($_GET["url"], "newsletter/") )
		{
			//get id_item
			$cut=substr($_GET["url"], 11, 1000);
			$_GET["id_article"]=substr($cut, 0, strpos($cut, "/"));
			if ( is_numeric($_GET["id_article"]) )
			{
				//get title
				$cut=substr($_GET["url"], 11, 1000);
				$title=stripslashes(substr($cut, strpos($cut, "/")+1, 1000));
				include('newsletter_web.php');
			}
			else
			{
				header("HTTP/1.0 404 Not Found");
				exit;
			}
		}
		//fahrzeugsuche
		elseif ( $_GET["url"]!="fahrzeugsuche/" and startsWith($_GET["url"], "fahrzeugsuche/") )
		{
			//get id_vehicle
			$cut=substr($_GET["url"], 14, 1000);
			$_GET["id_vehicle"]=substr($cut, 0, strpos($cut, "/"));
			if ( is_numeric($_GET["id_vehicle"]) )
			{
				include('shop_searchbycar.php');
			}
			else
			{
				header("HTTP/1.0 404 Not Found");
				exit;
			}
		}
		//kba-suche
		elseif ( $_GET["url"]!="kba-suche/" and startsWith($_GET["url"], "kba-suche/") )
		{
			//get id_vehicle
			$cut=substr($_GET["url"], 10, 1000);
			$_GET["kbanr"]=substr($cut, 0, strpos($cut, "/"));
			if ( $_GET["kbanr"] )
			{
				include('shop_searchbycar.php');
			}
		}
		//vehicle_id-suche
		elseif ( $_GET["url"]!="vehicle_id-suche/" and startsWith($_GET["url"], "vehicle_id-suche/") )
		{
			//get id_vehicle
			$cut=substr($_GET["url"], 17, 1000);
			$_GET["vehicle_id"]=substr($cut, 0, strpos($cut, "/"));
			$cut2=substr($cut, (strlen($_GET["vehicle_id"])+1), 1000);
			$_GET["kbanr2"]=substr($cut2, 0, strpos($cut2, "/"));
			if ( $_GET["vehicle_id"] )
			{
				include('shop_searchbycar.php');
			}
		}
		//Freitext-Suche
		elseif (startsWith($_GET["url"], "suche/"))
		{
			$_POST["search"]=substr($_GET["url"], 6, strlen($_GET["url"])-7);
			$_GET["url"]="suche/";
			include("shop_search.php");
		}
		//Freitext-Suche
		elseif (startsWith($_GET["url"], "oe-nummern-suche/"))
		{
			$_POST["search"]=substr($_GET["url"], 17, strlen($_GET["url"])-18);
			$_GET["url"]="suche/";
			include("shop_search_oe.php");
		}
		//Regional-Center
		elseif (startsWith($_GET["url"], "regional-center/"))
		{
			$cut=substr($_GET["url"], 16, 1000);
			$domain=substr($cut, 0, strpos($cut, "/"));
			header("HTTP/1.1 303 See Other");
			header("location: http://www.mapco-".$domain.".de");
			exit;
		}
		//Regional-Center Italien
		elseif (startsWith($_GET["url"], "italia/"))
		{
			$cut=substr($_GET["url"], 7, 1000);
			$domain=substr($cut, 0, strpos($cut, "/"));
			header("HTTP/1.1 303 See Other");
			header("location: http://www.mapco-".$domain.".eu");
			exit;
		}
		//Newsletter unsubscribe
		elseif (startsWith($_GET["url"], "unsubscribe/"))
		{
			$_GET["unsubscribe"]=1;
			$_GET["email"]=stripslashes(substr($_GET["url"], 12, 1000));
			include('home.php');
		}
		//Newsletter unsubscribe
		elseif (startsWith($_GET["url"], "registrieren/"))
		{
			include('register.php');
		}
		//used vehicles results
		elseif ( $_GET["url"]!="portal/gebrauchtwagen/fahrzeugliste/" and startsWith($_GET["url"], "portal/gebrauchtwagen/fahrzeugliste/") and !startsWith($_GET["url"], "portal/gebrauchtwagen/fahrzeugliste/fahrzeugdetails/"))
		{
			$cut=substr($_GET["url"], 36, 1000);
			$cut=substr($cut, 0, strpos($cut, "/"));
			$_GET["page"]=$cut;
			include('used-cars-results.php');
		}
		//used vehicle detail page
		elseif ( $_GET["url"]!="portal/gebrauchtwagen/fahrzeugliste/fahrzeugdetails/" and startsWith($_GET["url"], "portal/gebrauchtwagen/fahrzeugliste/fahrzeugdetails/"))
		{
			include('used-car.php');
		}
		//events
		elseif ( $_GET["url"]!="portal/aktuelles/event/details/" and startsWith($_GET["url"], "portal/aktuelles/event/details/"))
		{
			include('events.php');
		}
		//news
		elseif ( $_GET["url"]!="portal/aktuelles/news/details/variable:wert|variable2:wert2/" and startsWith($_GET["url"], "portal/aktuelles/news/details/"))
		{
			include('news.php');
		}
		//Der Schrauber
		elseif ( $_GET["url"]=="derschrauber/" or $_GET["url"]=="derschrauber" )
		{
			//get title
			$title='Der Schrauber von MAPCO';
			include('mapco_tv.php');
		}
		else
		{
			//get all menus
			$menus=array();
			$results=q("SELECT id_menu FROM cms_menus WHERE site_id IN(0, ".$_SESSION["id_site"].");", $dbweb, __FILE__, __LINE__);
			while( $row=mysqli_fetch_array($results) )
			{
				$menus[]=$row["id_menu"];
			}
			//get all menus
			$menuitems=array();
			$menuitem_ids=array();
			$results=q("SELECT * FROM cms_menuitems WHERE menu_id IN(".implode(", ", $menus).") AND NOT alias='';", $dbweb, __FILE__, __LINE__);
			while( $row=mysqli_fetch_array($results) )
			{
				$menuitem_ids[]=$row["id_menuitem"];
				$menuitems[$row["id_menuitem"]]=$row;
			}
			//static links
			$results=q("SELECT * FROM cms_menuitems_languages WHERE alias='".mysqli_real_escape_string($dbweb, $_GET["url"])."' AND language_id=".$_SESSION["id_language"]." AND menuitem_id IN(".implode(", ", $menuitem_ids).") LIMIT 1;", $dbweb, __FILE__, __LINE__);
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
				unset($menus);
				unset($menuitem_ids);
				unset($menuitems);
				if($menuitems[$row["menuitem_id"]]["local"]==1) include(FRONTEND.$link);
				else include($link);
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
					echo "<h1>!404 Not Found</h1>";
					echo "The page that you have requested could not be found.";
					exit;
				}
			}
		}
?>
