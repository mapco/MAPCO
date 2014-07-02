<?php
/*
PayPal includes the following API Signature for making API
calls to the PayPal sandbox:

API Username 	sdk-three_api1.sdk.com
API Password 	QFZCWN5HZM8VBG7Q
API Signature 	A-IzJhZZjhg29XQ2qnhapuwxIDzyAZQ92FRP5dqBzVesOkzbdUONzmOU
//define('API_USERNAME', 'sdk-three_api1.sdk.com');
//define('API_PASSWORD', 'QFZCWN5HZM8VBG7Q');
//define('API_SIGNATURE', 'A-IzJhZZjhg29XQ2qnhapuwxIDzyAZQ92FRP5dqBzVesOkzbdUONzmOU');

****************************************************/
//include("../../mapco_shop_de/config.php");
$res=q("SELECT * FROM paypal_accounts where title = 'PayPal Mapco' LIMIT 1;", $dbshop, __FILE__, __LINE__);
$row=mysql_fetch_array($res);

switch ($row["production"])
{
	case "0":
		define('API_USERNAME', $row["sandbox_API_USER"]);
		define('API_PASSWORD', $row["sandbox_API_PW"]);
		define('API_SIGNATURE', $row["sandbox_Signature"]);
		define('API_ENDPOINT', 'https://api-3t.sandbox.paypal.com/nvp');
		define('PAYPAL_URL', 'https://www.sandbox.paypal.com/webscr&amp;cmd=_express-checkout&amp;token=');
		//define('PAYPAL_URL', 'https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&amp;token=');

		define('IPN_ENDPOINT', 'ssl://www.sandbox.paypal.com');
		break;
		
	case "1":
		define('API_USERNAME', $row["API_USER"]);
		define('API_PASSWORD', $row["API_PW"]);
		define('API_SIGNATURE', $row["Signature"]);
		define('API_ENDPOINT', 'https://api-3t.paypal.com/nvp');
		define('PAYPAL_URL', 'https://www.paypal.com/webscr&amp;cmd=_express-checkout&amp;token=');
		//define('PAYPAL_URL', 'https://www.paypal.com/webscr&cmd=_express-checkout&amp;token=');
		define('IPN_ENDPOINT', 'ssl://ipnpb.paypal.com');
		break;
}
/*
		define('API_USERNAME', $row["sandbox_API_USER"]);
		define('API_PASSWORD', $row["sandbox_API_PW"]);
		define('API_SIGNATURE', $row["sandbox_Signature"]);
		define('API_ENDPOINT', 'https://api-3t.sandbox.paypal.com/nvp');
		define('PAYPAL_URL', 'https://www.sandbox.paypal.com/webscr&amp;cmd=_express-checkout&amp;token=');
		//define('PAYPAL_URL', 'https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&amp;token=');
*/
		define('IPN_ENDPOINT', 'ssl://www.sandbox.paypal.com');
define('USE_PROXY',FALSE);
define('PROXY_HOST', '127.0.0.1');
define('PROXY_PORT', '808');


define('VERSION', '63.0');

?>