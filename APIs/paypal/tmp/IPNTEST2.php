    <?php
	include("templates/".TEMPLATE."/header.php");
	include("config.php");

	
    $req = 'cmd=_notify-validate';

    foreach ($_POST as $key => $value) {
           $value = urlencode(stripslashes($value));
           $req .= "&$key=$value";
        }

	//setting the curl parameters.
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,'ssl://www.sandbox.paypal.com');
	curl_setopt($ch, CURLOPT_VERBOSE, 1);

	//turning off the server and peer verification(TrustManager Concept).
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch,CURLOPT_POSTFIELDS,$req);

	$response = curl_exec($ch);
/*	if (curl_errno($ch)) {
          $result=q("insert into IPN_test (text) values ('blabla'".curl_error($ch)."');", $dbshop, __FILE__, __LINE__);
	 } else {
		 //closing the curl
			curl_close($ch);
	  }
	 */
$result=q("insert into IPN_test (text) values ('".curl_error($ch)."');", $dbshop, __FILE__, __LINE__);

/*
    // post back to PayPal system to validate
    $header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
    $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
    $header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
    $fp = fsockopen ('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);

    // assign posted variables to local variables
    $item_name          = $_POST['item_name'];
    $item_number        = $_POST['item_number'];
    $payment_status     = $_POST['payment_status'];
    $payment_amount     = $_POST['mc_gross'];
    $payment_currency   = $_POST['mc_currency'];
    $txn_id             = $_POST['txn_id'];
    $receiver_email     = $_POST['receiver_email'];
    $payer_email        = $_POST['payer_email'];

    if (!$fp) {

        // HTTP ERROR

    } else {
        fputs ($fp, $header . $req);
            while (!feof($fp)) {
            $res = fgets ($fp, 1024);
            if (strpos($res, "VERIFIED") === 0) {
				$tmp="VERIFIED||||";
				foreach ($_POST as $key => $value) {
					$value = urlencode(stripslashes($value));
					$tmp.= $key.'='.$value.'|';
				}

                $result=q("insert into IPN_test (text) values ('".$res."');", $dbshop, __FILE__, __LINE__);
            }else {
                $tmp="INVALID||||";
				foreach ($_POST as $key => $value) {
					$value = urlencode(stripslashes($value));
					$tmp.= $key.'='.$value.'|';
				}

                $result=q("insert into IPN_test (text) values ('".$res."');", $dbshop, __FILE__, __LINE__);

            }
        }

        fclose ($fp);
    }
*/
	//while (list($key, $val) = each ($response)) {$tmp.=$key.": ".$val." | ";}


    ?> 