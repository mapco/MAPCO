<?php

exit;
	if( !isset($_SESSION) ) session_start();

	if (!function_exists("error"))
	{
		function error($file, $line, $msg)
		{
		//	if ( isset($_SESSION["userrole_id"]) and $_SESSION["userrole_id"]==1 )
			{
				die('ERROR #'.$line.' in '.$file.': '.$msg);
			}
		//	else
			{
				$report='';
				//get user
				if ( isset($_SESSION["id_user"]) ) $report.="Benutzer: ".$_SESSION["id_user"]; else $report .= "Benutzer: unbekannt<br />";
				//get IP
				$report .= 'IP-Adresse: <a href="http://www.utrace.de/?query='.$_SERVER['REMOTE_ADDR'].'">'.$_SERVER['REMOTE_ADDR']."</a><br />";
				//get URL
				$url =(isset($_SERVER['HTTPS'])?'https':'http').'://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];  
				$report .= "URL: ".$url."<br />";
				//get script
				$report .= "Skript: ".$file."<br />";
				//get line
				$report .= "Zeile: ".$line."<br />";
				//get postvars
				$report .= "POST: ".print_r($_POST, true)."<br />";
				//get error message
				$report .= "<br />Fehlermeldung: ".$msg."<br />";
				
				//mail error
				$header  = 'MIME-Version: 1.0' . "\r\n";
				$header .= 'Content-type: text/html; charset=utf-8' . "\r\n";
				$header .= "From: MAPCO-Server <server@mapco.de> \r\n";
//				$header .= 'Reply-To: '.$_POST["firstname"].' '.$_POST["lastname"].' <'.$_POST["email"]."> \r\n";
				mail("habermann.jens@gmail.com", "Fehler in ".$file.", Zeile ".$line, $report, $header);
				mail("developer@mapco.de", "Fehler in ".$file.", Zeile ".$line, $report, $header);
				die("An error occured. The administrators have been informed.");
			}
		}
	}
	
	//OLD
	if (!function_exists("error_logs"))
	{
		function error_logs($file, $line, $msg)
		{
			global $dbshop;
			q("INSERT INTO cms_errors (error, file, line, timestamp) VALUES ('".$msg."', '".$file."', ".$line.", ".time().");", $dbshop, __FILE__, __LINE__);
		}
	}
	
	//NEW
	if (!function_exists("error__log"))
	{
		function error__log($errortype_id, $error_id=0, $file, $line, $msg)
		{
			global $dbweb;
			q("INSERT INTO cms_errors (errortype_id, error_id, file, line, text, time) VALUES(".$errortype_id.", ".$error_id.", '".mysqli_real_escape_string($dbweb, $file)."', ".$line.", '".mysqli_real_escape_string($dbweb, $msg)."', ".time().");", $dbweb, __FILE__, __LINE__);
		}
	}
	
	//url error
	if (!function_exists("url_error"))
	{
		function url_error($table, $url, $ip)
		{
			global $dbshop;
			
			$results=q("SELECT * FROM error_".$table." WHERE url='".$url."' AND ip='".$ip."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($results)>0)
			{
				$row=mysqli_fetch_array($results);
				q("UPDATE error_".$table." SET count=".($row["count"]+1).", lastmod=".time()." WHERE id_error=".$row["id_error"].";", $dbshop, __FILE__, __LINE__);
			}
			else
			{
				q("INSERT INTO error_".$table." (url, ip, lastmod) VALUES('".$url."', '".$ip."', ".time().");", $dbshop, __FILE__, __LINE__);
			}
		}
	}	

	if (!function_exists("q"))
	{
		function q($query, $db, $file="", $line="", $error_txt="")
		{
			global $dbweb;
			global $dbshop;
			global $debug;
			global $queries;
			if ( isset($debug) )
			{
				$start=strpos($query, "FROM ")+5;
				$stop=strpos($query, " ", $start)-$start;
				$tablename=substr($query, $start, $stop);
				if ( !isset($queries[$tablename]) ) $queries[$tablename]=1; else $queries[$tablename]++;
			}
			$starttime=time()+microtime();
			$results = mysqli_query($db, $query) or error($file, $line, "<br />".$query."<br />".mysqli_error($db)."<br />".$error_txt);
			$stoptime=time()+microtime();
			$time=$stoptime-$starttime;
			if( $time>1 )
			{
				mysqli_query($dbweb, "INSERT INTO cms_errors_sql (query, time) VALUES('".mysqli_real_escape_string($dbweb, $query)."', '".$time."');");
			}
			return $results;
		}
	} 

	if (!function_exists("q_insert"))
	{
		function q_insert($table, $data, $db, $file="", $line="", $error_txt="")
		{
			$keys=array_keys($data);
			$query="INSERT INTO `".$table."` (".implode(", ", $keys).")";
			$query .= " VALUES (";
			for($i=0; $i<sizeof($keys); $i++)
			{
				$query.="'".mysqli_real_escape_string($db, $data[$keys[$i]])."'";
				if( ($i+1)<sizeof($keys) ) $query.=", ";
			}
			$query .= ");";
			
			$results = q($query, $db, $file, $line, $error_txt);
			return $results;
		}
	} 

	if (!function_exists("q_update"))
	{
		function q_update($table, $data, $where, $db, $file="", $line="", $error_txt="")
		{
			$query="UPDATE ".$table." SET ";
			$keys=array_keys($data);
			for($i=0; $i<sizeof($keys); $i++)
			{
				$query.="`".mysqli_real_escape_string($db, $keys[$i])."`='".mysqli_real_escape_string($db, $data[$keys[$i]])."'";
				if( ($i+1)<sizeof($keys) ) $query.=", ";
			}
			$query.=" ".$where;
			
			$results = q($query, $db, $file, $line, $error_txt);
			return $results;
		}
	} 

	if (!function_exists("post"))
	{
		function post($url, $postvars)
		{
			$ch = curl_init();
			session_write_close(); //endless loop fix
			curl_setopt ($ch, CURLOPT_COOKIE, session_name() . "=" . session_id()); //endless loop fix
			curl_setopt	($ch, CURLOPT_FORBID_REUSE, true); 
			curl_setopt	($ch, CURLOPT_FRESH_CONNECT, true);
			curl_setopt ($ch, CURLOPT_POST, true);
			curl_setopt ($ch, CURLOPT_POSTFIELDS, $postvars);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt ($ch, CURLOPT_URL, $url);
			$response = curl_exec ($ch);
			if( $response===false )	$response=curl_error($ch);
			curl_close($ch);
			return($response);
		}
	}
	
	
	if (!function_exists("ip2country"))
	{
		function ip2country($IP)
		{ 
			global $dbweb;

//			$IP = sprintf("%u",IP2Long($IP)); 
			$results = q("SELECT * FROM cms_ip2country WHERE IP_from <= ".$IP." AND IP_to >= ".$IP." LIMIT 1;", $dbweb, __FILE__, __LINE__); 
			if(mysqli_num_rows($results) == 0)
			{ 
				return("DE");
			}
			else
			{ 
				$row = mysqli_fetch_array($results); 
				return($row["country2"]);
			}
		}
	}

	if (!function_exists("cutout"))
	{
		function cutout($text, $from, $to)
		{
			while( ($start = strpos($text, $from)) !== false )
			{
				$end=strpos($text, $to, $start)+strlen($to);
				$text2=substr($text, 0, $start);
				$text2.=substr($text, $end, strlen($text));
				$text=$text2;
			}
			return($text);
		}
	}

	//connect to databases
	if( !isset($dbweb) )
	{
		$dbweb=mysqli_connect("localhost", "dedi473_14", "Merci2664!", "admapco_mapcoweb");
		q("SET NAMES utf8", $dbweb, __FILE__, __LINE__);
	}
	if( !isset($dbshop) )
	{
		$dbshop=mysqli_connect("localhost","mapcoshop","merci2664", "admapco_mapcoshop");
		q("SET NAMES utf8", $dbshop, __FILE__, __LINE__);
	}
/*
	$dblenkung24=mysqli_connect("localhost","db_franchise","atn99c4T", "admapco_franchise");
	q("SET NAMES utf8", $dblenkung24, __FILE__, __LINE__);
*/


	//DDoS handling
	$ist_bot=0;
	$tofindbot = array("Teoma", "alexa", "froogle", "Gigabot", "inktomi",
    "looksmart", "URL_Spider_SQL", "Firefly", "NationalDirectory",
    "Ask Jeeves", "TECNOSEEK", "InfoSeek", "WebFindBot", "girafabot",
    "crawler", "www.galaxy.com", "Googlebot", "Scooter", "Slurp",
    "msnbot", "appie", "FAST", "WebBug", "Spade", "ZyBorg", "rabaz",
    "Baiduspider", "Feedfetcher-Google", "TechnoratiSnoop", "Rankivabot",
    "Mediapartners-Google", "Sogou web spider", "WebAlta Crawler","TweetmemeBot",
    "Butterfly","Twitturls","Me.dium","Twiceler","bing","microsoft","yahoo"); // bot erkennung
	foreach($tofindbot as $element)
	{
		if (stristr(getEnv("HTTP_USER_AGENT"),$element) == TRUE)
		{
			$ist_bot = 1; 
		}
	}  
	
	if ($ist_bot<>1)
	{
		if ($_SERVER['REMOTE_ADDR']=="") $ip=0; else $ip=ip2long($_SERVER['REMOTE_ADDR']);
//		$ip=ip2long("216.52.185.187"); //USA TEST IP
		$results=q("SELECT * FROM cms_connections_blacklist WHERE ip=".$ip." AND time>".time().";", $dbweb, __FILE__, __LINE__);
		if( mysqli_num_rows($results)>0 )
		{
			header("HTTP/1.1 503 Service Unavailable");
			die();
		}
		if (!isset($_SESSION["origin"]) or $_SESSION["origin"]=="") $_SESSION["origin"]=ip2country($ip);
		q("INSERT INTO cms_connections (ip, time) VALUES (".$ip.", ".time().");", $dbweb, __FILE__, __LINE__);
		q("DELETE FROM cms_connections WHERE time<".(time()-60).";", $dbweb, __FILE__, __LINE__);
		$results=q("SELECT * FROM cms_connections WHERE ip=".$ip.";", $dbweb, __FILE__, __LINE__);
		if( mysqli_num_rows($results)>500 )
		{
			$results=q("SELECT * FROM cms_connections_whitelist WHERE ip=".$ip.";", $dbweb, __FILE__, __LINE__);
			if( mysqli_num_rows($results)==0 )
			{
				q("INSERT INTO cms_connections_blacklist (ip, time) VALUES (".$ip.", ".(time()+24*3600).");", $dbweb, __FILE__, __LINE__);
				$text  = '<a href="http://www.ip-adress.com/ip_lokalisieren/'.$_SERVER["REMOTE_ADDR"].'" target="_blank">ip-address.com</a>';
				$text .= '<br /><a href="http://www.utrace.de/?query='.$_SERVER["REMOTE_ADDR"].'" target="_blank">ip-address.com</a>';
	
				//rewrite .htaccess
				$htaccess=file_get_contents("htaccess.bak");
				if( $htaccess !== false )
				{
					$results=q("SELECT * FROM cms_connections_blacklist WHERE time>".(time()-24*3600).";", $dbweb, __FILE__, __LINE__);
					if( mysqli_num_rows($results)>0 )
					{
						$htaccess .= "\n\n#cms_connections_blacklist\n";
						while( $row=mysqli_fetch_array($results) )
						{
							$htaccess .= 'Deny from '.long2ip($row["ip"])."\n";
						}
					}
					$handle=fopen(".htaccess", "w");
					fwrite($handle, $htaccess);
					fclose($handle);
				}
	//			mail("jhabermann@mapco.de", "Mögliche DDoS-Attacke von ".$_SERVER["REMOTE_ADDR"]." verhindert", $text);
				exit;
			}
		}
	}

	//detect site
	$results=q("SELECT * FROM cms_sites;", $dbweb, __FILE__, __LINE__);
	while( $site=mysqli_fetch_array($results) )
	{
		if ( strpos($_SERVER["SERVER_NAME"], $site["domain"] ) !== false )
		{
			if (!defined("LOCAL")) define("LOCAL", "localhost/MAPCO/mapco_shop_de");
			if (!defined("LIVE")) define("LIVE", "www.".$site["domain"]);
			if (!defined("TEMPLATE")) define("TEMPLATE", $site["template"]);
			$_SESSION["id_site"]=$site["id_site"];
			break;
		}
	}
	if( isset($_SESSION["id_user"]) )
	{
		//detect wrong site
		$results=q("SELECT * FROM cms_users_sites WHERE user_id=".$_SESSION["id_user"]." AND site_id=".$_SESSION["id_site"].";", $dbweb, __FILE__, __LINE__);
		if( mysqli_num_rows($results)==0 )
		{
			session_start();
			session_destroy();
/*
			$data=array();
			$data["errortype_id"]=1;
			$data["error_id"]=9783;
			$data["file"]=$_SERVER['REQUEST_URI'];
			$data["line"]=__LINE__;
			$data["text"]=print_r($_SESSION, true).print_r($_POST, true);
			$data["time"]=time();
			q_insert("cms_errors", $data, $dbweb, __FILE__, __LINE__);
			$results=q("SELECT * FROM cms_users_sites WHERE user_id=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
			$row=mysqli_fetch_array($results);
			$results=q("SELECT * FROM cms_sites WHERE id_site=".$row["site_id"].";", $dbweb, __FILE__, __LINE__);
			$row=mysqli_fetch_array($results);
			header("Location: http://".$row["domain"]);
			exit;
*/
		}
	}
	//set default site if necessary
	if ( !defined("LOCAL") ) define("LOCAL", "localhost/MAPCO/mapco_shop_de");
	if ( !defined("LIVE") ) define("LIVE", "www.mapco.de");
	if ( !defined("TEMPLATE") ) define("TEMPLATE", "shop");
	if ( !defined("TEMPLATE_BACKEND") ) define("TEMPLATE_BACKEND", "backend");
	if ( !isset($_SESSION["id_site"]) ) $_SESSION["id_site"]=1;
	if ( !defined("FRONTEND") ) define("FRONTEND", "templates/".TEMPLATE."/");
	if( !isset($_SESSION["id_shop"]) and isset($dbshop) )
	{
		$results=q("SELECT * FROM shop_shops WHERE site_id=".$_SESSION["id_site"].";", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)>0 )
		{
			$row=mysqli_fetch_array($results);
			$_SESSION["id_shop"]=$row["id_shop"];
		}
	}
	


//			echo "+++".$_SESSION["id_shop"];
	//check for testserver
	if ( !defined("PATH") )
	{
		if ($_SERVER['HTTP_HOST'] == "localhost")
		{
			define("PATH", "http://".LOCAL."/");
		}
		else
		{

			if ( isset($_SERVER["HTTPS"]) and $_SERVER["HTTPS"]!="" ) define("PATH", "https://".LIVE."/");
			else define("PATH", "http://".LIVE."/");
/*
			if ( $site["ssl"] ) define("PATH", "https://".LIVE."/");
			else define("PATH", "http://".LIVE."/");
*/
		}
	}

	// Umsatzsteuerwert
	if ( !defined("UST") ) define ("UST", 19);
	
	// Pepper
	if ( !defined("PEPPER") ) define ("PEPPER", "8c166031e6c8d96a1d0ed23a49570059");

	//get language
	if( !defined("PATHLANG") )
	{
		if ( isset($_GET["url"]) and strpos($_GET["url"], "/") == 2 )
		{
			$lang=substr($_GET["url"], 0, 2);
			if( $lang=="de" )
			{
				$_GET["lang"]="de";
				$_GET["url"]=substr($_GET["url"], 3, 4000);
				url_error("lang", $_SERVER['REQUEST_URI'], $_SERVER['REMOTE_ADDR']);
				header("HTTP/1.1 301 Moved Permanently");
				header("location: ".PATH.$_GET["url"]);
				exit;
			}
			else
			{
				$results=q("SELECT * FROM cms_languages WHERE code='".$lang."';", $dbweb, __FILE__, __LINE__);
				if ( mysqli_num_rows($results)>0 )
				{
					$_GET["url"]=substr($_GET["url"], 3, 4000);
					$_GET["lang"]=$lang;
				}
				else 
				{
					$_GET["lang"]="de";
					url_error("lang", $_SERVER['REQUEST_URI'], $_SERVER['REMOTE_ADDR']);
					header("HTTP/1.1 301 Moved Permanently");
					header("location: ".PATH.$_GET["url"]);
					exit;
				}
			}
		}
		else 
		{
			if( isset($_SESSION["lang"]) ) $_GET["lang"]=$_SESSION["lang"];
			else $_GET["lang"]="de";
		}

		$_SESSION["lang"]=$_GET["lang"];
		if( $_SESSION["lang"]=="de" ) define("PATHLANG", PATH); else define("PATHLANG", PATH.$_GET["lang"]."/");
	}
	
	//LASTVISIT
	if ( isset($_SESSION["id_user"]) and $_SESSION["id_user"]>0)
	{
		q("UPDATE cms_users SET lastvisit=".time()." WHERE id_user=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
	}
?>