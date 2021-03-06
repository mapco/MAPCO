  <?php
    $req = 'cmd=_notify-validate';

    foreach ($_POST as $key => $value) {
           $value = urlencode(stripslashes($value));
           $req .= "&$key=$value";
        }

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
            if (strcmp ($res, "VERIFIED") == 0) {
                //Process Order
            }else if (strcmp ($res, "INVALID") == 0) {
                //Send Email To You 
            }
        }

        fclose ($fp);
    }
    ?>