<?php



//$res=q("SELECT * FROM paypal_accounts where title = 'PayPal Mapco' LIMIT 1;", $dbshop, __FILE__, __LINE__);
$res=q("SELECT * FROM paypal_accounts WHERE shop_id = ".$_SESSION["id_shop"]." LIMIT 1;", $dbshop, __FILE__, __LINE__);

$row=mysqli_fetch_array($res);

switch ($row["production"])
{
	case "0":
		define('API_USERNAME', $row["sandbox_API_USER"]);
		define('API_PASSWORD', $row["sandbox_API_PW"]);
		define('API_SIGNATURE', $row["sandbox_Signature"]);
		define('API_ENDPOINT', 'https://api-3t.sandbox.paypal.com/nvp');
		define('PAYPAL_URL', 'https://www.sandbox.paypal.com/webscr&amp;cmd=_express-checkout&amp;token=');

		define('IPN_ENDPOINT', 'ssl://www.sandbox.paypal.com');
		
		define('VERSION', '63.0');

		break;
		
	case "1":
		define('API_USERNAME', $row["API_USER"]);
		define('API_PASSWORD', $row["API_PW"]);
		define('API_SIGNATURE', $row["Signature"]);
		define('API_ENDPOINT', 'https://api-3t.paypal.com/nvp');
		define('PAYPAL_URL', 'https://www.paypal.com/webscr&amp;cmd=_express-checkout&amp;token=');
		define('IPN_ENDPOINT', 'ssl://ipnpb.paypal.com');
		
		define('VERSION', '63.0');

		break;
}
define('USE_PROXY',FALSE);
define('PROXY_HOST', '127.0.0.1');
define('PROXY_PORT', '808');


?>