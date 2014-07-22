<?php
/*
*	MLOADER DEFINES START
*/
	define('M_PATH_BASE', dirname(__FILE__) );							// PATH TO MAPCO_SHOP_DE
	define( 'DS', DIRECTORY_SEPARATOR );								// directory separator shortcut
	$parts = explode( DS, M_PATH_BASE );		// path to array
	array_pop( $parts );						// remove last element to get root path
	
	// directory path defines
	define('M_PATH_ROOT',			implode( DS, $parts ) );			// PATH TO ROOT
	define('M_PATH_LIBRARIES',		M_PATH_ROOT.DS.'libraries');		// PATH TO LIB
	define('M_PATH_API',			M_PATH_ROOT.DS.'APIs');				// PATH TO API
	define('M_PATH_TEMPLATES',		M_PATH_BASE.DS.'templates');		// PATH TO TEMPLATES
	
	// include the MLoader.class
	require_once( M_PATH_LIBRARIES.DS.'MLoader.class.php');
	MLoader::setup();
	
/*
*	MLOADER DEFINES END
*/

	//GET["url"] is set by .htaccess

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

	/*
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
	*/

	if (!function_exists("q"))
	{
		function q($query, $db, $file = "", $line = "", $error_txt = "")
		{
			global $dbweb, $dbshop, $count_db_calls;
			$count_db_calls++;
			//$starttime=time()+microtime();
			$results = mysqli_query($db, $query) or error($file, $line, "<br />" . $query . "<br />" . mysqli_error($db) . "<br />" . $error_txt);
			/*
			$stoptime=time()+microtime();
			$time=$stoptime-$starttime;

			if( $time>1 )
			{
				mysqli_query($dbweb, "INSERT INTO cms_errors_sql (query, time) VALUES('".mysqli_real_escape_string($dbweb, $query)."', '".$time."');");
			}
			*/
			return $results;
		}
	}

    if (!function_exists("q_count"))
    {
        function q_count($table, $where = null, $db, $file = "", $line = "", $error_txt = "")
        {
			$query = "SELECT COUNT(*) FROM `" . $table . "`";
            empty($where) ? '' : ' WHERE ' . $where;
            $query.= $where;
			$result = mysqli_query($db, $query) or error($file, $line, "<br />" . $query . "<br />" . mysqli_error($db) . "<br />" . $error_txt);
			$row = $result->fetch_row();
			return $row[0];
        }
    }

	if (!function_exists("q_insert"))
	{
		function q_insert($table, $data, $db, $file="", $line="", $error_txt="")
		{
			$keys=array_keys($data);
			$query="INSERT INTO `".$table."` (`".implode("`, `", $keys)."`)";
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
            $query = "UPDATE " . $table . " SET ";
            $keys = array_keys($data);
            for($i = 0; $i < sizeof($keys); $i++)
            {
                $query.= "`" . mysqli_real_escape_string($db, $keys[$i]) . "`='" . mysqli_real_escape_string($db, $data[$keys[$i]]) . "'";
                if (($i+1) < sizeof($keys) ) $query.= ", ";
            }
            $query.= " ".$where;

            $results = q($query, $db, $file, $line, $error_txt);
            return $results;
        }
    }

    /**
     *	Returns a insert result
     *	- use this within function without global $db
	 *	- set $field['lastInsertId'] for return lastInsertId
     */
    if (!function_exists("SQLSelect"))
    {
        /**
         * @param $field
         * @param $data
         * @param $db
         * @param string $file
         * @param string $line
         * @param string $error_txt
         * @return bool|int|mysqli_result|string
         */
        function SQLInsert($field, $data, $db, $file = "", $line = "", $error_txt = "")
        {
            global $dbshop, $dbweb;
            if ($db == 'shop' OR $db == null) 
			{
                $db = $dbshop;
            }
            if ($db == 'web') 
			{
                $db = $dbweb;
            }
            $keys = array_keys($data);
            $query = "INSERT INTO `" . $field['table'] . "` (`" . implode("`, `", $keys) . "`)";
            $query.= " VALUES (";
            for ($i = 0; $i < sizeof($keys); $i++)
            {
                $query.= "'" . mysqli_real_escape_string($db, $data[$keys[$i]]) . "'";
                if (($i + 1) < sizeof($keys)) $query.= ", ";
            }
            $query.= ");";
            $results = mysqli_query($db, $query) or error($file, $line, "<br />" . $query . "<br />" . mysqli_error($db) . "<br />" . $error_txt);
            if ($field['lastInsertId'] != null) 
			{
                $latestInsertId = mysqli_insert_id($db);
                return $latestInsertId;
            }
            return $results;
        }
    }

	/**
	 *	Returns a select result
	 *	- use this within function without global $db
	 */
	if (!function_exists("SQLSelect"))
	{
        /**
         * @param $table
         * @param $select
         * @param null $where
         * @param null $order
         * @param int $first
         * @param int $limit
         * @param null $db
         * @param string $file
         * @param string $line
         * @param string $error_txt
         * @return array|null
         */
        function SQLSelect($table, $select, $where = null, $order = null, $first = 0, $limit = 1, $db = null, $file = "", $line = "", $error_txt = "")
		{
			global $dbshop, $dbweb;

			if ($db == 'shop' OR $db == null) 
			{
				$db = $dbshop;
			}
			if ($db == 'web') 
			{
				$db = $dbweb;
			}
			settype($first, 'integer');
			settype($limit, 'integer');
			$run = 0;
			$query = 'SELECT ' . $select . ' FROM ' . $table;
			if (!empty($where)) 
			{
				$query.= ' WHERE ' . $where;
			}
			if (!empty($order)) 
			{
				$query.= ' ORDER BY ' . $order;
			}
			if (!empty($limit)) 
			{
				$query.= ' LIMIT ' . $first . ',' . $limit;
			}
			$data = mysqli_query($db, $query) OR error($file, $line, "\n" . $query . "\n" . mysqli_error($db) . "\n" . $error_txt);
			if ($limit == 1) 
			{
				$new_result = mysqli_fetch_assoc($data);
			} else {
				while ($sql_result = mysqli_fetch_assoc($data)) 
				{
					$new_result[$run] = $sql_result;
					$run++;
				}
			}

			//	this is the same like $data->close() == (Objektorientierter Stil)
			//	we use mysqli_free_result($data) == (Prozeduraler Stil)
			mysqli_free_result($data);
			if (!empty($new_result)) 
			{
				return $new_result;
			}
		}
	}

	/**
	 *	Returns a count result
	 *	- use this within function without global $db
	 */
	if (!function_exists("SQLCount"))
	{
        /**
         * @param $table
         * @param null $where
         * @param null $db
         * @param string $file
         * @param string $line
         * @param string $error_txt
         * @return mixed
         */
        function SQLCount($table, $where = null, $db = null, $file = "", $line = "", $error_txt = "")
		{
			global $dbshop, $dbweb;
			if ($db == 'shop' OR $db == null) 
			{
				$db = $dbshop;
			}
			if ($db == 'web') 
			{
				$db = $dbweb;
			}
			$query = 'SELECT COUNT(*) FROM ' . $table;
			$query.= empty($where) ? '' : ' WHERE ' . $where;
			$data = mysqli_query($db, $query) OR error($file, $line, "\n" . $query . "\n" . mysqli_error($db) . "\n" . $error_txt);
			$result = mysqli_fetch_row($data);
			mysqli_free_result($data);
			return $result[0];
		}
	}

	/**
	 *	Update a SQL table
	 *	- use this within function without global $db
	 */
	if (!function_exists("SQLUpdate"))
	{
        /**
         * @param $table
         * @param $data
         * @param int $where
         * @param null $db
         * @param string $file
         * @param string $line
         * @param string $error_txt
         * @return bool|mysqli_result
         */
        function SQLUpdate($table, $data, $where = 0, $db = null, $file = "", $line = "", $error_txt = "")
		{
			global $dbshop, $dbweb;
			if ($db == 'shop' OR $db == null) 
			{
				$db = $dbshop;
			}
			if ($db == 'web') {
				$db = $dbweb;
			}
			settype($id, 'integer');
			$cells = array_keys($data);
			$values = array_values($data);

			$max = count($cells);
			$set = ' SET ';
			for ($run = 0; $run < $max; $run++)
			{
				$set.= mysqli_real_escape_string($db, $cells[$run]) . '="' . mysqli_real_escape_string($db, $values[$run]);
				if ($run != $max - 1)
					$set.= '", ';
			}
			$set.= '" ';
			$update = 'UPDATE ' . $table . $set;

			$case = strstr($where, 'CASE');
			if (!empty($where) && $case === false) 
			{
				$update.= ' WHERE ' . $where;
			} else {
				if ($case !== false) 
				{
					$update.= 	', ' . $where;
				}
			}
			$result = mysqli_query($db, $update) OR error($file, $line, "\n" . $update . "\n" . mysqli_error($db) . "\n" . $error_txt);
			return $result;
		}
	}

	if (!function_exists("post"))
	{
		function post($url, $postvars, $port=80)
		{
			$ch = curl_init();
			session_write_close(); //endless loop fix
			curl_setopt ($ch, CURLOPT_COOKIE, session_name() . "=" . session_id()); //endless loop fix
			curl_setopt	($ch, CURLOPT_FORBID_REUSE, true);
			curl_setopt	($ch, CURLOPT_FRESH_CONNECT, true);
			if($port!=80) curl_setopt($ch, CURLOPT_PORT, $port);
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
			} else {
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
				$end = strpos($text, $to, $start) + strlen($to);
				$text2 = substr($text, 0, $start);
				$text2.= substr($text, $end, strlen($text));
				$text = $text2;
			}
			return($text);
		}
	}

	if (!function_exists("soa2"))
	{
        /**
         * @param $postfields
         * @param string $file
         * @param string $line
         * @param string $responseType (obj, arr, xml, obj_to_arr)
         * @return array|mixed|SimpleXMLElement|string
         */
        function soa2($postfields, $file = "", $line = "", $responseType = 'obj')
		{
			$responseXML = post(PATH . "soa2/", $postfields);
			$use_errors = libxml_use_internal_errors(true);
			try
			{
				$response = new SimpleXMLElement($responseXML);

			}
			catch(Exception $e)
			{
				//XML FEHLERHAFT
				$errorXML = "";
				$errorXML.= "<?xml version='1.0'?>" . "\n";
				$errorXML.= "<" . $postfields["APIRequest"] . "Response>" . "\n";
				$errorXML.= "	<Ack>Error</Ack>" . "\n";
				$errorXML.= "	<Error>" . "\n";
				$errorXML.= "		<Code>9756</Code>" . "\n";
				$errorXML.= "		<shortMsg>Invalid XML. Antwort vom Service fehlerhaft.</shortMsg>" . "\n";
				$errorXML.= "		<longMsg>Invalid XML. Antwort vom Service fehlerhaft. Service aufgerufen durch " . $file . " Zeile " . $line . "</longMsg>" . "\n";
				$errorXML.= "		<Response><![CDATA[" . $responseXML . "]]></Response>" . "\n";
				$errorXML.= "	</Error>" . "\n";
				echo $errorXML .= "</" . $postfields["APIRequest"] . "Response>" . "\n";

				post(PATH . "soa2/", array(
					"API" => "cms",
					"APIRequest" => "ErrorAdd",
					"id_errortype" => 1,
					"id_errorcode" => 9756,
					"file" => __FILE__,
					"line" => __LINE__,
					"text" => "POSTFIELDS: " . print_r($postfields, true) . $responseXML
				));
				exit;
			}
			libxml_clear_errors();
			libxml_use_internal_errors($use_errors);

			if ($responseType == 'obj') 
			{
				// returns an object
				return $response;
			} 
			elseif ($responseType == 'arr') 
			{
                foreach($response as $key => $value)
				{
					if (is_object($value)) 
					{
						$var[$key] = (array)$value;
					}
				}
				return $var;
			}
			elseif ($responseType == 'obj_to_arr')
			{
				$responseArray = json_decode(json_encode($response), true);
				//$responseArray = objectToArray($response);
				return $responseArray;
			} else {
				// returns a xml
				return $responseXML;
			}
		}
	}
	
	if (!function_exists("objectToArray"))
	{	
		/**
		 * Convert an object to an array
		 *
		 * @param  object  $object The object to convert
		 * @return array
		 */
		function objectToArray($object)
		{
			if (!is_object($object) && !is_array($object))
			{
				return $object;
			}
			
			if (is_object($object))
			{
				$object = get_object_vars($object);
				return array_map('objectToArray', $object);	
			}
		}
	}


	//connect to databases
	if (!isset($dbweb))
	{
		$dbweb = mysqli_connect("localhost", "dedi473_14", "Merci2664!", "admapco_mapcoweb");
		q("SET NAMES utf8", $dbweb, __FILE__, __LINE__);
	}
	if (!isset($dbshop))
	{
		$dbshop = mysqli_connect("localhost","mapcoshop","merci2664", "admapco_mapcoshop");
		q("SET NAMES utf8", $dbshop, __FILE__, __LINE__);
	}
/*
	$dblenkung24=mysqli_connect("localhost","db_franchise","atn99c4T", "admapco_franchise");
	q("SET NAMES utf8", $dblenkung24, __FILE__, __LINE__);
*/


	//DDoS handling
	$ist_bot=0;
	$tofindbot = array("your-server.de", "Teoma", "alexa", "froogle", "Gigabot", "inktomi",
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

	if ($_SERVER['REMOTE_ADDR']=="") $ip=0; else $ip=ip2long($_SERVER['REMOTE_ADDR']);
//		$ip=ip2long("216.52.185.187"); //USA TEST IP
	$results=q("SELECT * FROM cms_connections_blacklist WHERE ip=".$ip." AND time>".time().";", $dbweb, __FILE__, __LINE__);
	if( mysqli_num_rows($results)>0 )
	{
		header("HTTP/1.1 503 Service Unavailable");
		echo "The request was a valid request, but the server is refusing to respond to it. (blacklist)";
		die();
	}
	if (!isset($_SESSION["origin"]) or $_SESSION["origin"]=="") $_SESSION["origin"]=ip2country($ip);
	q("INSERT INTO cms_connections (ip, time) VALUES (".$ip.", ".time().");", $dbweb, __FILE__, __LINE__);
	q("DELETE FROM cms_connections WHERE time<".(time()-60).";", $dbweb, __FILE__, __LINE__);
	if ($ist_bot<>1)
	{
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
	//if ( !defined("LIVE") ) define("LIVE", "www.mapco-leipzig.de");
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

	if( $_SESSION['id_shop'] == 2 or $_SESSION['id_shop'] == 4 or $_SESSION['id_shop'] == 6 or $_SESSION['id_shop'] == 21 ) {
		define("UST", 19);
	} elseif ( $_SESSION[ 'id_shop' ] == 19 ) {
		define( "UST", 21 );
	} elseif ( $_SESSION[ 'id_shop' ] == 20 ) {
		define( "UST", 20 );
	} elseif ( isset( $_SESSION[ "bill_country_id" ] ) and $_SESSION[ "bill_country_id" ] != '' ) {
		$res = q( "SELECT * FROM shop_countries WHERE id_country=" . $_SESSION[ "bill_country_id" ], $dbshop, __FILE__, __LINE__ );
		if ( mysqli_num_rows( $res ) == 1 ) {
			$shop_countries = mysqli_fetch_assoc( $res );
			define( "UST", $shop_countries[ "VAT" ] );
		}
	} elseif ( isset( $_SESSION[ "origin" ] ) ) {
		$res2 = q( "SELECT * FROM shop_countries WHERE country_code='" . $_SESSION["origin"] . "'", $dbshop, __FILE__, __LINE__ );
		if ( mysqli_num_rows( $res2 ) == 1 ) {
			$shop_countries = mysqli_fetch_assoc( $res2 );
			define( "UST", $shop_countries[ "VAT" ] );
		}
	} else {
		define( "UST", 19 );
	}

	if ( !defined("UST") ) define ("UST", 19);

	//if ( $_SESSION[ 'id_user' ] == 49352 ) define ( "UST", 33 );
/*
	//UST Korrektur für autopartner und franchise ( nur Zwischenlösung )
	if( $_SESSION['id_shop'] == 2 or $_SESSION['id_shop'] == 4 or $_SESSION['id_shop'] == 6 or $_SESSION['id_shop'] == 20 or $_SESSION['id_shop'] == 21 ) {
		define ("UST", 19);
	}
	if ( $_SESSION[ 'id_shop' ] == 19 ) {
		define ( "UST", 21 );
	}
*/
	// Pepper
	if ( !defined("PEPPER") ) define ("PEPPER", "8c166031e6c8d96a1d0ed23a49570059");

	//get language
//	if( !defined("PATHLANG") )
	if( !isset($_SESSION['id_language']) or $_SESSION['id_language']=="" or !defined("PATHLANG") )
	{
		//get all available languages
		$code2id=array();
		$results=q("SELECT * FROM cms_languages;", $dbweb, __FILE__, __LINE__);
		while($row=mysqli_fetch_array($results)) $code2id[$row["code"]]=$row["id_language"];

		//get default language
		$results=q("SELECT b.id_language, b.code FROM cms_sites_languages AS a, cms_languages AS b WHERE a.site_id=".$_SESSION["id_site"]." AND a.language_id=b.id_language ORDER BY a.ordering LIMIT 1;", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$defaultlang=$row["code"];

		if ( isset($_GET["url"]) and strpos($_GET["url"], "/") == 2 )
		{
			$lang=substr($_GET["url"], 0, 2);
			$results=q("SELECT * FROM cms_sites_languages WHERE site_id=".$_SESSION["id_site"]." AND language_id='".$code2id[$lang]."' LIMIT 1;", $dbweb, __FILE__, __LINE__);
			if( mysqli_num_rows($results)==0 )
			{
				//language unknown
				$_SESSION["lang"]=$defaultlang;
				$_GET["url"]=substr($_GET["url"], 3);
				url_error("lang", $_SERVER['REQUEST_URI'], $_SERVER['REMOTE_ADDR']);
				header("HTTP/1.1 301 Moved Permanently");
				header("location: ".PATH.$_GET["url"]);
				exit;
			}
			else
			{
				//language = default language
				if( $lang==$defaultlang )
				{
					$_SESSION["lang"]=$defaultlang;
					$_GET["url"]=substr($_GET["url"], 3);
//					url_error("lang", $_SERVER['REQUEST_URI'], $_SERVER['REMOTE_ADDR']);
					header("HTTP/1.1 301 Moved Permanently");
					header("location: ".PATH.$_GET["url"]);
					exit;
				}
				else
				{
					//language known but not default language
					$_GET["url"]=substr($_GET["url"], 3);
					$_GET["lang"]=$lang;
				}
			}
		}
		else
		{
			$results=q("SELECT * FROM cms_languages WHERE code='".$_SESSION["lang"]."' LIMIT 1;", $dbweb, __FILE__, __LINE__);
			if( mysqli_num_rows($results)==0 ) $_SESSION["lang"]=$defaultlang;

			if( isset($_SESSION["lang"]) ) $_GET["lang"]=$_SESSION["lang"];
			else $_GET["lang"]=$defaultlang;
		}

		$_SESSION["lang"]=$_GET["lang"];
		if( !defined("PATHLANG") )
		{
			if( $_SESSION["lang"]==$defaultlang ) define("PATHLANG", PATH); else define("PATHLANG", PATH.$_GET["lang"]."/");
		}

		$results=q("SELECT * FROM cms_languages WHERE code='".$_SESSION["lang"]."' LIMIT 1;", $dbweb, __FILE__, __LINE__);
		if( mysqli_num_rows($results)==0 )
		{
			$results=q("SELECT * FROM cms_languages WHERE code='".$defaultlang."' LIMIT 1;", $dbweb, __FILE__, __LINE__);
		}
		$row=mysqli_fetch_array($results);
		$_SESSION["id_language"]=$row["id_language"];
	}

	//LASTVISIT
	if ( isset($_SESSION["id_user"]) and $_SESSION["id_user"]>0)
	{
		q("UPDATE cms_users SET lastvisit=".time()." WHERE id_user=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
	}


	/*
		DEFINE GLOBAL IMAGE PATH
	*/
	DEFINE("ICONS_16", 			"/images/icons/16x16/");
	DEFINE("ICONS_24", 			"/images/icons/24x24/");
	DEFINE("ICONS_32", 			"/images/icons/32x32/");

?>
