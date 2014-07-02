<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_amazon_index.php">Amazon</a>';
	echo ' > Berichte';
	echo '</p>';

	echo '<h1>Berichte</h1>';


function toArray($xml)
{
        $array = json_decode(json_encode($xml), TRUE);
        /*
        foreach ( array_slice($array, 0) as $key => $value ) {
            if ( empty($value) ) $array[$key] = NULL;
            elseif ( is_array($value) ) $array[$key] = toArray($value);
        }
*/
        return $array;
}

function xmlObjToArr($obj) { 
        $namespace = $obj->getDocNamespaces(true); 
        $namespace[NULL] = NULL; 
        
        $children = array(); 
        $attributes = array(); 
        $name = strtolower((string)$obj->getName()); 
        
        $text = trim((string)$obj); 
        if( strlen($text) <= 0 ) { 
            $text = NULL; 
        } 
        
        // get info for all namespaces 
        if(is_object($obj)) { 
            foreach( $namespace as $ns=>$nsUrl ) { 
                // atributes 
                $objAttributes = $obj->attributes($ns, true); 
                foreach( $objAttributes as $attributeName => $attributeValue ) { 
                    $attribName = strtolower(trim((string)$attributeName)); 
                    $attribVal = trim((string)$attributeValue); 
                    if (!empty($ns)) { 
                        $attribName = $ns . ':' . $attribName; 
                    } 
                    $attributes[$attribName] = $attribVal; 
                } 
                
                // children 
                $objChildren = $obj->children($ns, true); 
                foreach( $objChildren as $childName=>$child ) { 
                    $childName = strtolower((string)$childName); 
                    if( !empty($ns) ) { 
                        $childName = $ns.':'.$childName; 
                    } 
                    $children[$childName][] = xmlObjToArr($child); 
                } 
            } 
        } 
        
        return array( 
            'name'=>$name, 
            'text'=>$text, 
            'attributes'=>$attributes, 
            'children'=>$children 
        ); 
    } 




	function amazon_sendrequest($request,$loc="")
	{
		$method = "GET";
		$uri = "/";

		if (!$loc)
		{
			$AWSAccessKeyId = "AKIAIVV6BQ6NVVWUWEUA";
			$Marketplace = "A1PA6795UKMFR9";
			$Merchant = "A3UOJO2H7UZY88";
			$SecretKey = "B8k51dOOQFeWoaAmdcvTrOVEb7AyFvQJ0XlzEpMe";
			$host = "mws.amazonservices.de";
		}
		elseif ($loc == "uk")
		{
			$AWSAccessKeyId = "key";
			$Marketplace = "key";
			$Merchant = "key";
			$SecretKey = "key";
			$host = "mws.amazonaws.co.uk";
		}

//		$request .= "&AWSAccessKeyId=$AWSAccessKeyId&Marketplace=$Marketplace&Merchant=$Merchant&Timestamp=".gmdate("Y-m-d\TH:i:s\Z")."&Version=2009-01-01&SignatureVersion=2&SignatureMethod=HmacSHA256";
		$request .= "&AWSAccessKeyId=$AWSAccessKeyId&Marketplace=$Marketplace&Merchant=$Merchant&Timestamp=".gmdate("Y-m-d\TH:i:s\Z")."&Version=2009-01-01&SignatureVersion=2&SignatureMethod=HmacSHA256";

		// Clean up and sort
		$request = explode('&',$request);
		
		foreach ($request as $key => $value)
		{
			$t = explode("=",$value);
			$params[$t[0]] = $t[1];
		}
		unset($request);

		ksort($params);

		foreach ($params as $param=>$value)
		{
			$param = str_replace("%7E", "~", rawurlencode($param));
			$value = str_replace("%7E", "~", rawurlencode($value));
			$canonicalized_query[] = $param."=".$value;
		}

		$canonicalized_query = implode("&", $canonicalized_query);

		// create the string to sign
		$string_to_sign = $method."\n".$host."\n".$uri."\n".$canonicalized_query;
		
		// calculate HMAC with SHA256 and base64-encoding
		$signature = base64_encode(hash_hmac("sha256", $string_to_sign, $SecretKey, true));
		
		// encode the signature for the request
		$signature = str_replace("%7E", "~", rawurlencode($signature));
		
		// create request
		$request = "https://".$host.$uri."?".$canonicalized_query."&Signature=".$signature;

		$ch = curl_init();
//		curl_setopt($ch,CURLOPT_HEADER, 0);
		curl_setopt($ch,CURLOPT_USERAGENT,"AmazonQuery/1.0 (Language=Amazon)");
//		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
//		curl_setopt($ch,CURLOPT_URL, $request);
		
//		echo $novaresponse = curl_exec($ch);
//		curl_close($ch);
		

//		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept-language:de-de", "Accept: application/json")); 
		$url=$request;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		return($response);
	}

	//CHECK FOR ANSWER
	$request = "Action=GetReportList";
//	$request = "Action=GetReportList&StartDate=".gmdate("Y-m-d\TH:i:s\Z",time()-86400);
//	$request = "Action=GetReportList&ReportTypeList=_GET_FLAT_FILE_ORDERS_DATA_&StartDate=".gmdate("Y-m-d\TH:i:s\Z",time()-86400);
	$results = amazon_sendrequest($request,$country);
	$xml = new SimpleXMLElement($results);
	$array = json_decode(json_encode($xml), TRUE);


	//report list
	echo '<ul class="orderlist" style="width:250px;">';
	echo '<li id="ebay_accounts_header" style="width:238px; background:#ccc;">Berichte</li>';
	for($i=0; $i<sizeof($array["GetReportListResult"]["ReportInfo"]); $i++)
	{
		echo '<li style="width:238px;">';
		if ( isset($_GET["id_report"]) and $_GET["id_report"]==$array["GetReportListResult"]["ReportInfo"][$i]["ReportId"] ) $style=' style="font-weight:bold;"'; else $style='';
		echo '	<a'.$style.' href="backend_amazon_reports.php?id_report='.$array["GetReportListResult"]["ReportInfo"][$i]["ReportId"].'">Bericht #'.$array["GetReportListResult"]["ReportInfo"][$i]["ReportId"].'</a>';
		echo '</li>';
	}
	
	//next report list
	while( $array["GetReportListResult"]["NextToken"]!="" )
	{
		$request = "Action=GetReportListByNextToken&NextToken=".$array["GetReportListResult"]["NextToken"];
		$results = amazon_sendrequest($request,$country);
		$xml = new SimpleXMLElement($results);
		$array = json_decode(json_encode($xml), TRUE);

		for($i=0; $i<sizeof($array["GetReportListByNextTokenResult"]["ReportInfo"]); $i++)
		{
			echo '<li style="width:238px;">';
			if ( isset($_GET["id_report"]) and $_GET["id_report"]==$array["GetReportListByNextTokenResult"]["ReportInfo"][$i]["ReportId"] ) $style=' style="font-weight:bold;"'; else $style='';
			echo '	<a'.$style.' href="backend_amazon_reports.php?id_report='.$array["GetReportListByNextTokenResult"]["ReportInfo"][$i]["ReportId"].'">Bericht #'.$array["GetReportListByNextTokenResult"]["ReportInfo"][$i]["ReportId"].'</a>';
			echo '</li>';
		}
	}
	echo '</ul>';


	//report details
	if ( isset($_GET["id_report"]) and $_GET["id_report"]>0 )
	{
		echo '<div style="width:1000px; float:left;">';
		echo '<h2>Bericht #'.$_GET["id_report"].'</h2>';
		$request = "Action=GetReport&ReportId=".$_GET["id_report"];
		$results = amazon_sendrequest($request,$country);
		$lines=explode("\n", $results);
		echo '<table style="float:left;">';
		for($i=0; $i<sizeof($lines); $i++)
		{
			echo '<tr>';
			$cells=explode("\t", $lines[$i]);
			for($j=0; $j<sizeof($cells); $j++)
			{
				echo '<td>'.$cells[$j].'</td>';
			}
			echo '</tr>';
		}
		echo '</table>';
		echo '</div>';
	}

	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>