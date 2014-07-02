<?php

	//	SOA2 SERVICE
	//	Get xml Result for shop payment types

	$results = q("
		SELECT * 
		FROM shop_payment_types;", $dbshop, __FILE__, __LINE__);
	$row = mysqli_fetch_array($results);
	$keys = array_keys($row);
	
	$results = q("
		SELECT * 
		FROM shop_payment_types;", $dbshop, __FILE__, __LINE__);
	$xml= "";
	while($row = mysqli_fetch_array($results))
	{
		$xml.= '	<PaymentType>' . "\n";
		for ($i = 0; $i < sizeof($keys); $i++)
		{
			if(!is_numeric($keys[$i]))
				$xml.= '		<' . $keys[$i] . '><![CDATA[' . $row[$keys[$i]] . ']]></' . $keys[$i] . '>' . "\n";
		}
		$xml.=  '	</PaymentType>' . "\n";
	}
	echo $xml;