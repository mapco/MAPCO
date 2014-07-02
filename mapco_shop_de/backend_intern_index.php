<?php
	include("config.php");

	function startsWith($check, $startStr)
	{
        if (!is_string($check) || !is_string($startStr) || strlen($check)<strlen($startStr)) {
            return false;
        }

        return (substr($check, 0, strlen($startStr)) === $startStr);
    }
	

	//redirect other URLs
	if ($_SERVER['HTTP_HOST'] != "localhost" and $_SERVER["SERVER_NAME"]!=LIVE)
	{
		$url=PATH;
		if (isset($_GET["url"])) $url.=$_GET["url"];
		header("HTTP/1.1 301 Moved Permanently");
		header("location: ".$url);
		exit;
	}
	
	//get language
	if ( isset($_GET["url"]) and strpos($_GET["url"], "/") == 2 )
	{
		$lang=substr($_GET["url"], 0, 2);
		$results=q("SELECT * FROM cms_languages WHERE code='".$lang."';", $dbweb, __FILE__, __LINE__);
		if ( mysqli_num_rows($results)>0 )
		{
			$_GET["url"]=substr($_GET["url"], 3, 4000);
			$_GET["lang"]=$lang;
		}
		else $_GET["lang"]="de";
	}
	else $_GET["lang"]="de";

	//show content
	if ( !isset($_GET["url"]) or $_GET["url"]=="" )
	{
		include("home.php");
	}
	else
	{
		//shopitems
		if ( $_GET["url"]!="online-shop/autoteile/" and startsWith($_GET["url"], "online-shop/autoteile/") )
		{
			//get id_item
			$cut=substr($_GET["url"], 22, 1000);
			$_GET["id_item"]=substr($cut, 0, strpos($cut, "/"));
			if ( is_numeric($_GET["id_item"]) )
			{
				//get title
				$cut=substr($_GET["url"], 22, 1000);
				$title=stripslashes(substr($cut, strpos($cut, "/")+1, 1000));
				include('shop_item.php');
			}
			else
			{
				header("HTTP/1.0 404 Not Found");
				exit;
			}
		}
		//bill
		elseif ( $_GET["url"]!="online-shop/bestellung/" and startsWith($_GET["url"], "online-shop/bestellung/") )
		{
			//get id_item
			$cut=substr($_GET["url"], 23, 1000);
			$_GET["id_order"]=substr($cut, 0, strpos($cut, "/"));
			if ( is_numeric($_GET["id_order"]) )
			{
				include('shop_user_order.php');
			}
			else
			{
				header("HTTP/1.0 404 Not Found");
				exit;
			}
		}
		//articles
		elseif ( $_GET["url"]!="news/" and startsWith($_GET["url"], "news/") )
		{
			//get id_item
			$cut=substr($_GET["url"], 5, 1000);
			$_GET["id_article"]=substr($cut, 0, strpos($cut, "/"));
			if ( is_numeric($_GET["id_article"]) )
			{
				//get title
				$cut=substr($_GET["url"], 5, 1000);
				$title=stripslashes(substr($cut, strpos($cut, "/")+1, 1000));
				include('home.php');
			}
			else
			{
				header("HTTP/1.0 404 Not Found");
				exit;
			}
		}
		elseif ( $_GET["url"]!="presse/mapco-tv/" and startsWith($_GET["url"], "presse/mapco-tv/") )
		{
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
		elseif ( $_GET["url"]!="portal/aktuelles/news/details/" and startsWith($_GET["url"], "portal/aktuelles/news/details/"))
		{
			include('news.php');
		}
		else
		{
			$results=q("SELECT * FROM cms_menuitems WHERE alias='".mysqli_real_escape_string($dbweb, $_GET["url"])."' LIMIT 1;", $dbweb, __FILE__, __LINE__);
			if (mysqli_num_rows($results)>0)
			{
				$row=mysqli_fetch_array($results);
				$_GET["id_menuitem"]=$row["id_menuitem"];
				$parse=parse_url($row["link"]);
				$link=$parse["path"];
				if (isset($parse["query"])) $vars=explode("&", $parse["query"]); else $vars=array();
				for($i=0; $i<sizeof($vars); $i++)
				{
					$var=explode("=", $vars[$i]);
					$_GET[$var[0]]=$var[1];
				}
				include($link);
			}
			else
			{
				header("HTTP/1.0 404 Not Found");
			}
		}
	}
?>