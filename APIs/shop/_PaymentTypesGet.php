<?
/**********************************************************
*	SOA2 Service
*		shop.PaymentTypesGet
*
*	@author: Christopher Händler <chaendler(at)mapco.de>
*	@version: 0.1
*	@modified: 	09/07/14
*
*********************/

	$QUERY_PAYMENTTYPES = "SELECT * FROM `shop_payment_types`";
	$ResultPaymentTypes = q($QUERY_PAYMENTTYPES, $dbshop, __FILE__, __LINE__);
	
	$xml = "<PaymentTypes>";
	while($PaymentTypes = mysqli_fetch_assoc($ResultPaymentTypes))
	{
		$xml.="<payment_type>\n";
		foreach ($PaymentTypes as $tkey => $tvalue)
		{
			$xml.="<".$tkey."><![CDATA[".$tvalue."]]></".$tkey.">\n";
		}
		$xml.="</payment_type>\n";
	}
	$xml .="</PaymentTypes>\n";
	
	echo $xml;
?>