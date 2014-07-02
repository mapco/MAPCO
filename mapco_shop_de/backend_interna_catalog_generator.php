<?php
	include("config.php");

	function startsWith($check, $startStr)
	{
        if (!is_string($check) || !is_string($startStr) || strlen($check)<strlen($startStr)) {
            return false;
        }

        return (substr($check, 0, strlen($startStr)) === $startStr);
    }

	//redirect e8b4e947 / 2169bbd1 / a8a60b92
	if ( ($_SERVER['REQUEST_URI']=="/e8b4e947" or $_SERVER['REQUEST_URI']=="/2169bbd1" or $_SERVER['REQUEST_URI']=="/a8a60b92") and $_SERVER["SERVER_NAME"]==LIVE)
	{
		url_error("php", $_SERVER['REQUEST_URI'], $_SERVER['REMOTE_ADDR']);
		header("HTTP/1.1 301 Moved Permanently");
		header("location: ".PATHLANG );
		exit;
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

	//show content
	if ( !isset($_GET["url"]) or $_GET["url"]=="" )
	{
		$_GET["rcid"]=9999;
		if($_SESSION["id_site"]==1) include("home.php");
		else include("templates/".TEMPLATE."/home.php");
	}
	else
	{
		//AUTOLOGIN
		if ( startsWith($_GET["url"], "autologin/"))
		{
			$_GET["url"]=substr($_GET["url"], strlen("autologin/")+51);
		}

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
				if($_SESSION["id_site"]==1) include('shop_item.php');
				else include("templates/".TEMPLATE."/shop_item.php");
				
			}
			else
			{
				header("HTTP/1.0 404 Not Found");
				exit;
			}
		}
		//itemstatus
		elseif ( $_GET["url"]!="online-shop/status/" and startsWith($_GET["url"], "online-shop/status/") )
		{
			//get id_item
			$cut=substr($_GET["url"], 19, 1000);
			$_GET["id_item"]=substr($cut, 0, strpos($cut, "/"));
			if ( is_numeric($_GET["id_item"]) )
			{
				//get title
				$title=stripslashes(substr($cut, strpos($cut, "/")+1, 1000));
				include('shop_status.php');
			}
			else
			{
				header("HTTP/1.0 404 Not Found");
				exit;
			}
		}
		//Fuhrpark
		elseif ( $_GET["url"]!="online-shop/fuhrpark/" and startsWith($_GET["url"], "online-shop/fuhrpark/") )
		{
			//get id_item
			$cut=substr($_GET["url"], 21, 1000);
			$cut2=substr($cut, strpos($cut, "/")+1, 1000);
			$_GET[substr($cut, 0, strpos($cut, "/"))]=substr($cut2, 0, strpos($cut2, "/"));
			$cut=substr($cut2, strpos($cut2, "/")+1, 8);
			$_GET["kba"]=stripslashes(substr($cut, 0, strpos($cut, "/")));
			if ( is_numeric( substr($cut2, 0, strpos($cut2, "/")) ) )
			{
				include('user_carfleet.php');
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
		elseif ( $_GET["url"]!="portal/aktuelles/news/details/" and startsWith($_GET["url"], "portal/aktuelles/news/details/"))
		{
			include('news.php');
		}
		//Der Schrauber
		elseif ( $_GET["url"]=="derschrauber/" or  $_GET["url"]=="derschrauber" )
		{
			//get title
			$title='Der Schrauber von MAPCO';
			include('mapco_tv.php');
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
				if($_SESSION["id_site"]==1) include($link);
				else include(FRONTEND.$link);
			}
			else
			{
				header("HTTP/1.0 404 Not Found");
			}
		}
	}
?>
