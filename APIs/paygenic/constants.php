<?php
//include("../../mapco_shop_de/config.php");

	define('PayGenicCreditCardURL', 'https://test-paygenic.easycash.com/servlet_jsp/paypage/credit/creditCard.xhtml');
	define('PayGenicDirectDebitURL', 'https://test-paygenic.easycash.com/servlet_jsp/paypage/directDebit/directDebit.xhtml');
	define('PayGenicSofortURL', 'https://test-paygenic.easycash.com/servlet_jsp/paypage/sofort/sofortueberweisung.xhtml');
	
	define('BlowFishKey', 'dwqg8icsg4kl7');
	define('MerchantID', 'mapco_gmbh');
	
	//define('URLSuccess', PATH.'shop_cart.php');
	//define('URLFailure', PATH.'shop_cart.php');
	//define('URLNotify', PATH.'shop_cart.php');
	define('URLSuccess', 'https://www.mapco.de/paygenictest.php');
	define('URLFailure', 'https://www.mapco.de/paygenicTest.php');
	define('URLNotify', 'https://www.mapco.de/APIs/paygenic/notifications.php');

?>