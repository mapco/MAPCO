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

	//show content
	if ( !isset($_GET["url"]) )
	{
		include("home.php");
	}
	else
	{
		//shopitems
		if ( $_GET["url"]!="online-shop/autoteile/" and startsWith($_GET["url"], "online-shop/autoteile/") )
		{
			//get language
			$cut=substr($_GET["url"], 22, 1000);
			$_GET["lang"]=substr($cut, 0, strpos($cut, "/"));
			//get id_item
			$cut=substr($_GET["url"], 25, 1000);
			$_GET["id_item"]=substr($cut, 0, strpos($cut, "/"));
			//get title
			$cut=substr($_GET["url"], 25, 1000);
			$_GET["title"]=substr($cut, strpos($cut, "/")+1, 1000);
			include('shop_item.php');
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
			$results=q("SELECT * FROM cms_menuitems WHERE alias='".$_GET["url"]."' LIMIT 1;", $dbweb, __FILE__, __LINE__);
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
//				header("HTTP/1.1 301 Moved Permanently");
//				header("location: ".PATH);
			}
		}
	}
?>