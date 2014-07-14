<?
/**********************************************************
*	SOA2 Service
*		shop.PaymentMethodById
*
*	@author: Christopher Händler <chaendler(at)mapco.de>
*	@version: 0.1
*	@modified: 	08/07/14
*
*********************/
		check_man_params
		(
			array
			(
				"payment_id" => "numericNN"
			)
		);

	$PaymentID = $_POST['payment_id'];

	$QUERY = "SELECT * FROM shop_payment_dev WHERE id_payment='".$PaymentID."' ";
	$Result = q($QUERY, $dbshop, __FILE__, __LINE__);
	$Payment = mysqli_fetch_assoc($Result);
		
	

	$xml = "<Payment>\n";
		foreach ($Payment as $key => $value) 
		{
			$xml.="<".$key."><![CDATA[".$value."]]></".$key.">\n";
		}
		
		/*
		*	PaymentCountries
		*/
		
		$xml .="<countries>\n";
		$QUERY_COUNTRIES = "SELECT * FROM shop_payment_countries WHERE payment_id='".$PaymentID."' ";	
		$ResultCountries = q($QUERY_COUNTRIES, $dbshop, __FILE__, __LINE__);
		while($PaymentCountries = mysqli_fetch_assoc($ResultCountries)) 
		{
			$xml.="<country>\n";
			foreach ($PaymentCountries as $ckey => $cvalue)
			{
				$xml.="<".$ckey."><![CDATA[".$cvalue."]]></".$ckey.">\n";
			}
			$xml.="</country>\n";
		}
		$xml .="</countries>\n";
		
		/*
		*	PaymentCustomerTypes
		*/
		$xml .="<customer_types>\n";
		$QUERY_CUSTOMER_TYPES = "SELECT * FROM shop_payment_customer_types WHERE payment_id='".$PaymentID."' ";	
		$ResultCustomerTypes = q($QUERY_CUSTOMER_TYPES, $dbshop, __FILE__, __LINE__);
		while($PaymentCustomerTypes = mysqli_fetch_assoc($ResultCustomerTypes))
		{
			$xml.="<customer_type>\n";
			foreach ($PaymentCustomerTypes as $tkey => $tvalue)
			{
				$xml.="<".$tkey."><![CDATA[".$tvalue."]]></".$tkey.">\n";
			}
			$xml.="</customer_type>\n";
		}
		$xml .="</customer_types>\n";
	
	$xml .="</Payment>\n";
	echo $xml;

?>