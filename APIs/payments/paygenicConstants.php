<?php

	$res=q("SELECT * FROM paygenic_accounts WHERE id_account = 1;", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($res);
	
	if ($row["production"]==1)
	{
		define('PayGenicCreditCardURL', 'https://paygenic.easycash.com/servlet_jsp/paypage/credit/creditCard.xhtml');
		define('PayGenicDirectDebitURL', 'https://paygenic.easycash.com/servlet_jsp/paypage/directDebit/directDebit.xhtml');
		define('PayGenicSofortURL', 'https://paygenic.easycash.com/servlet_jsp/paypage/sofort/sofortueberweisung.xhtml');
	
		define('BlowFishKey', $row["BlowFishKey_live"]);
		define('MerchantID',  $row["MerchantID_live"]);
	
		define('URLSuccess', PATHLANG."online-shop/kasse/");
		define('URLFailure', PATHLANG."online-shop/kasse/");
		//define('URLSuccess', PATH.'shop_cart.php');
		//define('URLFailure', PATH.'shop_cart.php');
		//define('URLNotify', PATH.'/PayPalIPNHandler.php');
		define('URLNotify', PATH.'/PaymentNotificationsHandler.php');
	}
	
	if ($row["production"]==0)
	{
		define('PayGenicCreditCardURL', 'https://test-paygenic.easycash.com/servlet_jsp/paypage/credit/creditCard.xhtml');
		define('PayGenicDirectDebitURL', 'https://test-paygenic.easycash.com/servlet_jsp/paypage/directDebit/directDebit.xhtml');
		define('PayGenicSofortURL', 'https://test-paygenic.easycash.com/servlet_jsp/paypage/sofort/sofortueberweisung.xhtml');
	
		define('BlowFishKey', $row["BlowFishKey_test"]);
		define('MerchantID',  $row["MerchantID_test"]);
	
	//	define('URLNotify', 'https://www.mapco.de/PaymentNotificationsHandler.php');
	//	define('URLSuccess', 'https://www.mapco.de/shop_cart.php');
	//	define('URLFailure', 'https://www.mapco.de/shop_cart.php');
		define('URLSuccess', PATHLANG."online-shop/kasse/");
		define('URLFailure', PATHLANG."online-shop/kasse/");
		define('URLNotify', PATH.'/PaymentNotificationsHandler.php');
		
	}

?>