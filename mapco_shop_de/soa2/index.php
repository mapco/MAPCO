<?php

/***
 *	@author: unknown
 *	SOA2 Service 
 *	- (description)
 *
 *	@params
 *	- API, APIRequest, APICleanRequest
 * 	-- 
 *
*******************************************************************************/

    //  Returns timestamp as FLOAT
	$start = microtime(true);

	session_start();
	include("../config.php");
	include("../functions/cms_tl.php");

    /**
     * @param $required
     */
    function check_man_params($required)
	{
		global $dbshop;

		//  CHECK FOR REQUIRED FIELDS
		while (list ($key, $val) = each ($required))
		{
			//	isset Field
			if (!isset($_POST[$key])) {

				//	clean output buffer
				ob_end_clean();

				echo '<'.$_POST["APIRequest"].'Response>'."\n";
				echo '	<Ack>Failure</Ack>' . "\n";
				echo '	<Service>soa2/check_man_params/key</Service>' . "\n";
				echo '	<Error>' . "\n";
				echo '		<Code>' . __LINE__ . '</Code>' . "\n";
				echo '		<shortMsg>Benötigtes Feld: ' . $key . ' nicht gefunden</shortMsg>' . "\n";
				echo '		<longMsg>Benötigtes Feld: ' . $key . ' nicht gefunden</longMsg>' . "\n";
				echo '	</Error>' . "\n";
				echo '</' . $_POST["APIRequest"] . 'Response>' . "\n";
				exit;
			}

		    //  matching Values
			if ($val == "numeric") 
			{
				for ($i=0; $i < sizeof($_POST[$key]);$i++)
				{
					if ($_POST[$key][$i] == "" || !is_numeric($_POST[$key][$i])) 
					{
						//	clean output buffer
						ob_end_clean();

						echo '<' . $_POST["APIRequest"] . 'Response>' . "\n";
						echo '	<Ack>Failure</Ack>' . "\n";
						echo '	<Service>soa2/check_man_params/numeric</Service>' . "\n";
						echo '	<Error>' . "\n";
						echo '		<Code>' . __LINE__ . '</Code>' . "\n";
						echo '		<shortMsg>Ungültiger Wert im Feld: ' . $key . '</shortMsg>' . "\n";
						echo '		<longMsg>Ungültiger Wert im Feld: ' . $key . '. Übermittelter Wert: ' . $_POST[$i][$key] . '</longMsg>' . "\n";
						echo '	</Error>' . "\n";
						echo '</' . $_POST["APIRequest"] . 'Response>' . "\n";
						exit;
					}
				}
			}

            //
			if ($val == "numericNN") 
			{
				for ($i=0; $i < sizeof($_POST[$key]);$i++)
				{
					if ($_POST[$key] == "" || !is_numeric($_POST[$key]) || $_POST[$key] == 0) 
					{
                        //	clean output buffer
						ob_end_clean();

						echo '<' . $_POST["APIRequest"] . 'Response>' . "\n";
						echo '	<Ack>Failure</Ack>' . "\n";
						echo '	<Service>soa2/check_man_params/numericNN</Service>' . "\n";
						echo '	<Error>' . "\n";
						echo '		<Code>' . __LINE__ . '</Code>' . "\n";
						echo '		<shortMsg>Ungültiger Wert im Feld: ' . $key . '</shortMsg>' . "\n";
						echo '		<longMsg>Ungültiger Wert im Feld: ' . $key . '. Übermittelter Wert: ' . $_POST[$i][$key] . '</longMsg>' . "\n";
						echo '	</Error>'."\n";
						echo '</'.$_POST["APIRequest"].'Response>'."\n";
						exit;
					}
				}
			}

            //
			if ($val == "numerictest") 
			{
				for ($i=0; $i < sizeof($_POST[$key]);$i++)
				{
					if ($_POST[$key][$i] == "" || !is_numeric($_POST[$key][$i])) 
					{
                        //	clean output buffer
						ob_end_clean();

						echo '<' . $_POST["APIRequest"] . 'Response>' . "\n";
						echo '	<Ack>Failure</Ack>' . "\n";
						echo '	<Service>soa2/check_man_params/numerictest</Service>' . "\n";
						echo '	<Error>' . "\n";
						echo '		<Code>' . __LINE__ . '</Code>' . "\n";
						echo '		<shortMsg>Ungültiger Wert im Feld: ' . $key . '</shortMsg>' . "\n";
						echo '		<longMsg>Ungültiger Wert im Feld: ' . $key . '. Übermittelter Wert: ' . $_POST[$i][$key] . '</longMsg>' . "\n";
						echo '	</Error>' . "\n";
						echo '</' . $_POST["APIRequest"] . 'Response>' . "\n";
						exit;
					}
				}
			}

            //
			if ($val == "textNN") 
			{
				for ($i = 0; $i < sizeof($_POST[$key]);$i++)
				{
					if ($_POST[$key][$i] == "") 
					{
                        //	clean output buffer
						ob_end_clean();

						echo '<' . $_POST["APIRequest"] . 'Response>' . "\n";
						echo '	<Ack>Failure</Ack>' . "\n";
						echo '	<Service>soa2/check_man_params/textNN</Service>' . "\n";
						echo '	<Error>' . "\n";
						echo '		<Code>' . __LINE__ . '</Code>' . "\n";
						echo '		<shortMsg>Ungültiger Wert im Feld: ' . $key . '</shortMsg>' . "\n";
						echo '		<longMsg>Leerer String im Feld: ' . $key . '</longMsg>' . "\n";
						echo '	</Error>' . "\n";
						echo '</' . $_POST["APIRequest"] . 'Response>' . "\n";
						exit;
					}
				}
			}

            //
			if ($val == "currency") 
			{
				for ($i = 0; $i < sizeof($_POST[$key]);$i++)
				//foreach ($_POST[$key] as $PostArrayField)
				{
					if (sizeof($_POST[$key]) == 1) $PostArrayField = $_POST[$key]; else $PostArrayField = $_POST[$key][$i];

					if ($PostArrayField == "") 
					{
                        //	clean output buffer
						ob_end_clean();

						echo '<' . $_POST["APIRequest"] . 'Response>' . "\n";
						echo '	<Ack>Failure</Ack>' . "\n";
						echo '	<Service>soa2/check_man_params/currency</Service>' . "\n";
						echo '	<Error>' . "\n";
						echo '		<Code>' . __LINE__ . '</Code>' . "\n";
						echo '		<shortMsg>Ungültiger Wert im Feld: ' . $key . '</shortMsg>' . "\n";
						echo '		<longMsg>Leerer String im Feld: ' . $key . '</longMsg>' . "\n";
						echo '	</Error>' . "\n";
						echo '</' . $_POST["APIRequest"] . 'Response>' . "\n";
						exit;
					} else {
						$res_currency = q("
						    SELECT *
						    FROM shop_currencies
						    WHERE currency_code = '" . $PostArrayField . "';", $dbshop, __FILE__, __LINE__);
						if  (mysqli_num_rows($res_currency) == 0) 
						{
                            //	clean output buffer
							ob_end_clean();

							echo '<' . $_POST["APIRequest"] . 'Response>' . "\n";
							echo '	<Ack>Failure</Ack>' . "\n";
							echo '	<Service>soa2/check_man_params/currency</Service>' . "\n";
							echo '	<Error>' . "\n";
							echo '		<Code>' . __LINE__ . '</Code>' . "\n";
							echo '		<shortMsg>Ungültiger Wert im Feld: ' . $key . '</shortMsg>' . "\n";
							echo '		<longMsg>' . $PostArrayField . ' ist kein gültiger Währungscode</longMsg>' . "\n";
							echo '	</Error>' . "\n";
							echo '</' . $_POST["APIRequest"] . 'Response>' . "\n";
							exit;
						}
					}
				}
			}

            //
			if ($val == "countrycode") 
			{
				for ($i = 0; $i < sizeof($_POST[$key]);$i++)
				{
					if (sizeof($_POST[$key]) == 1) $PostArrayField = $_POST[$key]; else $PostArrayField = $_POST[$key][$i];

					if ($PostArrayField == "") 
					{
                        //	clean output buffer
						ob_end_clean();

						echo '<' . $_POST["APIRequest"] . 'Response>' . "\n";
						echo '	<Ack>Failure</Ack>' . "\n";
						echo '	<Service>soa2/check_man_params/countrycode</Service>' . "\n";
						echo '	<Error>' . "\n";
						echo '		<Code>' . __LINE__ . '</Code>' . "\n";
						echo '		<shortMsg>Ungültiger Wert im Feld: ' . $key . '</shortMsg>' . "\n";
						echo '		<longMsg>Leerer String im Feld: ' . $key . '</longMsg>' . "\n";
						echo '	</Error>' . "\n";
						echo '</' . $_POST["APIRequest"] . 'Response>' . "\n";
						exit;
					} else {
						$res_country = q("
						    SELECT *
						    FROM shop_countries
						    WHERE country_code = '" . $PostArrayField . "';", $dbshop, __FILE__, __LINE__);
						if (mysqli_num_rows($res_country) == 0) 
						{
                            //	clean output buffer
							ob_end_clean();

							echo '<' . $_POST["APIRequest"] . 'Response>' . "\n";
							echo '	<Ack>Failure</Ack>' . "\n";
							echo '	<Service>soa2/check_man_params/countrycode</Service>' . "\n";
							echo '	<Error>' . "\n";
							echo '		<Code>' . __LINE__ . '</Code>' . "\n";
							echo '		<shortMsg>Ungültiger Wert im Feld: ' . $key . '</shortMsg>' . "\n";
							echo '		<longMsg>' . $PostArrayField . ' ist kein gültiger Ländercode</longMsg>' . "\n";
							echo '	</Error>' . "\n";
							echo '</' . $_POST["APIRequest"] . 'Response>' . "\n";
							exit;
						}
					}
				}
			}

            //
			if ($val == "db_handle") 
			{
				for ($i = 0; $i < sizeof($_POST[$key]);$i++)
				{
					if ($_POST[$key][$i] != "dbweb" && $_POST[$key][$i] != "dbshop") 
					{
                        //	clean output buffer
						ob_end_clean();

						echo '<' . $_POST["APIRequest"] . 'Response>' . "\n";
						echo '	<Ack>Failure</Ack>' . "\n";
						echo '	<Error>' . "\n";
						echo '		<Code>' . __LINE__ . '</Code>' . "\n";
						echo '		<shortMsg>Ungültiger Wert im Feld: ' . $key . '</shortMsg>' . "\n";
						echo '		<longMsg>' . $key . ' ist keine DB-Resource</longMsg>' . "\n";
						echo '	</Error>' . "\n";
						echo '</' . $_POST["APIRequest"] . 'Response>' . "\n";
						exit;
					}
				}
			}

		}

	}

	//ImageThumbnail fix
	$public = false;
	if (isset($_POST["APIRequest"])
		AND ($_POST["APIRequest"] == "ImageThumbnail"
		OR $_POST["APIRequest"] == "CartAdd"
		OR $_POST["APIRequest"] == "CartUpdate"
		OR $_POST["APIRequest"] == "CategoryGetImagePath"
		OR $_POST["APIRequest"] == "PromotionBoxGetItems"
		OR $_POST["APIRequest"] == "CategoryBox"
		OR $_POST["APIRequest"] == "PromotionBox"
		OR $_POST["APIRequest"] == "ArticlesUnreadGet"
		OR $_POST["APIRequest"] == "MailSend2"
		OR $_POST["APIRequest"] == "ArticleTranslationGet"
		OR $_POST["APIRequest"] == "ErrorAdd"
		OR $_POST["APIRequest"] == "CheckoutOrderSet"
		OR $_POST["APIRequest"] == "OrderAdd"
		OR $_POST["APIRequest"] == "OrderItemAdd"
		OR $_POST["APIRequest"] == "VariableUnset"
		OR $_POST["APIRequest"] == "CheckoutGuestSet"
		OR $_POST["APIRequest"] == "CheckoutPriceCorrection"))
		// OR $_POST["APIRequest"] == "PaymentsNotificationSet_PayPal"
	{
		$public = true;
	} else {

		//	usertoken security check
		if (isset($_POST["usertoken"])) 
		{
			$response = q("
				SELECT *
				FROM cms_users
				WHERE user_token = '" . $_POST["usertoken"] . "'", $dbweb, __FILE__, __LINE__ );
			if (mysqli_num_rows($response) == 0 ) 
			{
				show_error(9876, 1, __FILE__, __LINE__, print_r($_POST, true));
				exit;
			}
			$cms_users = mysqli_fetch_assoc( $response );
			if (isset($_SESSION['id_user']) AND $cms_users['id_user'] != $_SESSION['id_user']) 
			{
				show_error(9880, 8, __FILE__, __LINE__, 'session-user-id: ' . $_SESSION['id_user'] . ' user(token): ' . $cms_users['id_user']);
			}
			$_SESSION["id_user"] = $cms_users['id_user'];
			$_SESSION["userrole_id"] = $cms_users['userrole_id'];
		}

		//security check
/*
		if ( $_SERVER["REMOTE_ADDR"]=="85.10.215.73")
		{
			$_SESSION["id_user"]=1;
			$_SESSION["userrole_id"]=1;
		}
		else
*/

		if ((!isset($_SESSION["id_user"])
			OR !($_SESSION["id_user"] > 0))
			AND $_POST["APIRequest"] != "UserLogin"
			AND $_POST["APIRequest"] != "UserPasswordRequestMailSend"
			AND $_POST["APIRequest"] != "CartItemsGet"
			AND $_POST["APIRequest"] != "CartMerge"
			AND $_POST["APIRequest"] != "BackendUserroleCheck"
			AND $_POST["APIRequest"] !="UserRegister") 
		{
			error(__FILE__, __LINE__, "Unerlaubter Zugriff auf Service!");
			header("HTTP/1.0 404 Not Found");
			exit;
		}
	}

	//	check for valid API name
	if (!isset($_POST["API"])) 
	{
		/*
		echo '<ServiceResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>API ungültig.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte keine gültige API gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ServiceResponse>'."\n";
		*/
		show_error(9793,8, __FILE__, __LINE__, "API-Aufruf: ".$_POST["API"]);
		exit;
	}

	//	check for valid Request name
	if (!isset($_POST["APIRequest"])) 
	{
		/*
		echo '<ServiceResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Servicename ungültig.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte kein gültiger Service gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ServiceResponse>'."\n";
		*/
		show_error(9794,8, __FILE__, __LINE__, "APIRequest-Aufruf: ".$_POST["APIRequest"]);
		exit;
	}

	//	check for valid Action
	$file = "../../APIs/" . $_POST["API"] . "/" . $_POST["APIRequest"] . ".php";
	if (!file_exists($file)) 
	{
	/*
		echo '<ServiceResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Service ungültig. ('.$file.')</shortMsg>'."\n";
		echo '		<longMsg>Es exisitiert kein Service mit dem angegebenen Namen.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ServiceResponse>'."\n";
	*/
		show_error(9795,8, __FILE__, __LINE__, "Service-Aufruf: " . $file);
		exit;
	}

	/**
	 * show error function
	 *
	 */
	function show_error($id_errorcode, $id_errortype, $file, $line, $text = "", $show_error = true)
	{
		global $dbweb;

		$results = q("
			SELECT *
			FROM cms_errorcodes
			WHERE errortype_id = " . $id_errortype . "
			AND errorcode = " . $id_errorcode . "
			LIMIT 1;", $dbweb, __FILE__, __LINE__);
		if (mysqli_num_rows($results) == 0) 
		{	
			$responseXML = post(PATH."soa2/", array( 
				"API" => "cms", 
				"APIRequest" => 
				"ErrorAdd", 
				"id_errortype" => 8, 
				"id_errorcode" => 9796, 
				"file" => $file, 
				"line" => $line, 
				"text" => "ErrorTypeID: " . $id_errortype . " ErrorCode: " . $id_errorcode . " " . print_r($_POST, true) . " FEHLERTEXT: " . $text 
			));

			$use_errors = libxml_use_internal_errors(true);
			try
			{
				$response = new SimpleXMLElement($responseXML);
			}
			catch(Exception $e)
			{
				$id_error = 0;
			}
			libxml_clear_errors();
			libxml_use_internal_errors($use_errors);
			if ($response->Ack[0]=="Success")
			{
				$id_error = (int)$response->id_error[0];
			} else {
				$id_error = 0;
			}

			if ($show_error) 
			{
				//	clean output buffer
				ob_end_clean();

				echo '<' . $_POST["APIRequest"] . 'Response>' . "\n";
				echo '	<Ack>Error</Ack>' . "\n";
				echo '	<Error>' . "\n";
				echo '		<ErrorID>' . $id_error . '</ErrorID>' . "\n";
				echo '		<Code>9796</Code>' . "\n";
				echo '		<shortMsg>Unbekannter Fehler</shortMsg>' . "\n";
				echo '		<longMsg>Unbekannter Fehler</longMsg>' . "\n";
				echo '	<Error>' . "\n";
				echo '</' . $_POST["APIRequest"] . 'Response>' . "\n";
			}
			
			$error = array();
			$error['API'] 					= $_POST['API'];
			$error['APIRequest'] 			= $_POST['APIRequest'];
			$error['Ack'] 					= 'Error';
			$error['Error']				= array();
			$error['Error']['ErrorID']	= $id_error;
			$error['Error']['Code']		= 9796;
			$error['Error']['shortMsg']	= 'Unbekannter Fehler';
			$error['Error']['longMsg']	= 'Unbekannter Fehler';
			$error['Error']['text']		= '';

		} else {
			
			$responseXML = post(PATH."soa2/", array( 
				"API" => "cms", 
				"APIRequest" => "ErrorAdd", 
				"id_errortype" => $id_errortype, 
				"id_errorcode" => $id_errorcode, 
				"file" => $file, 
				"line" => $line, 
				"text" => $text 
			));
			
			$use_errors = libxml_use_internal_errors(true);
			try
			{
				$response = new SimpleXMLElement($responseXML);
			}
			catch(Exception $e)
			{
				$id_error = 0;
			}
			libxml_clear_errors();
			libxml_use_internal_errors($use_errors);
			if ($response->Ack[0]=="Success")
			{
				$id_error = (int)$response->id_error[0];
			} else {
				$id_error = 0;
			}
			
			$row = mysqli_fetch_array($results);
			if ($show_error) 
			{
				//	clean output buffer
				ob_end_clean();

				echo '<' . $_POST["APIRequest"] . 'Response>' . "\n";
				echo '	<Ack>' . $row["type"] . '</Ack>' . "\n";
				echo '	<' . $row["type"] . '>' . "\n";
				echo '		<ErrorID>' . $id_error . '</ErrorID>' . "\n";
				echo '		<Code>' . $id_errorcode . '</Code>' . "\n";
				echo '		<shortMsg>' . $row["shortMsg"] . '</shortMsg>' . "\n";
				echo '		<longMsg>' . $row["longMsg"] . '</longMsg>' . "\n";
				echo '		<text><![CDATA[' . $text . ']]></text>' . "\n";
				echo '	</' . $row["type"] . '>' . "\n";
				echo '</' . $_POST["APIRequest"] . 'Response>' . "\n";
			}

			$error = array();
			$error['API'] 							= $_POST['API'];
			$error['APIRequest'] 					= $_POST['APIRequest'];
			$error['Ack'] 							= $row['type'];
			$error[$row['type']]					= array();
			$error[$row['type']]['ErrorID']		= $id_error;
			$error[$row['type']]['Code']		= $id_errorcode;
			$error[$row['type']]['shortMsg']	= $row["shortMsg"];
			$error[$row['type']]['longMsg']		= $row["longMsg"];
			$error[$row['type']]['text']		= $text;
		}
		return $error;
	}

	/**
	 * track error function
	 *
	 */
	function track_error($id_errorcode, $id_errortype, $file, $line, $text = "")
	{
		post(PATH."soa2/", array( 
			"API" => "cms", 
			"APIRequest" => "ErrorAdd", 
			"id_errortype" => $id_errortype, 
			"id_errorcode" => $id_errorcode, 
			"file" => $file, 
			"line" => $line, 
			"text" => $text 
		));
	}

	//AUSGABE PUFFERN, WEGEN FEHLERMELDUNG ABFANGEN

	ob_start();

	//	clean api request result
	if (isset($_POST['APICleanRequest']) && $_POST['APICleanRequest'] == true) {
		//start service
		include($file);
		$end = microtime(true);
		$diff = $start - $end;
	} else {

		//	xml api result
		echo '<' . $_POST["APIRequest"] . 'Response>' . "\n";
		echo '	<Ack>Success</Ack>' . "\n";
		//start service
		include($file);
		$end = microtime(true);
		$diff = $start - $end;
		echo '	<Runtime>' . $diff . '</Runtime>' . "\n";
		echo '</'. $_POST["APIRequest"] . 'Response>' . "\n";
	}
	//  flush output buffer
	ob_end_flush();
