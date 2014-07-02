<?php

	$res=q("SELECT * FROM dhl_retourlabel_parameters;", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res)==0)
	{
		echo '<Get_DHL_Retourlabel_ParameterResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Keine DHL RetourLabel Parameter gefunden</shortMsg>'."\n";
		echo '		<longMsg>Keine DHL RetourLabel Parameter gefunden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</Get_DHL_Retourlabel_ParameterResponse>'."\n";
		exit;
	}

	$xmldata='';
	
	while ($row=mysqli_fetch_array($res))
	{
		$xmldata.="	<parameter>\n";
		$xmldata.="		<country_code><![CDATA[".$row["country_code"]."]]></country_code>\n";
		$xmldata.="		<dhl_parameter><![CDATA[".$row["dhl_parameter"]."]]></dhl_parameter>\n";
		$xmldata.="	</parameter>\n";
	}
	

	echo "<Get_DHL_Retourlabel_ParameterResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo $xmldata;
	echo "</Get_DHL_Retourlabel_ParameterResponse>";


?>