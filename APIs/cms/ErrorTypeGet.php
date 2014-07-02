<?php
	/*************************
	********** SOA 2 *********
	*************************/
	
	$xml = '';
	
	$result = q('SELECT * FROM cms_errortypes',$dbweb, __FILE__, __LINE__);
	while($row = mysqli_fetch_array($result))
	{
		$xml .= '<errortype>'."\n";
		$xml .= '	<id_errortype><![CDATA['.$row['id_errortype'].']]></id_errortype>'."\n";
		$xml .= '	<title><![CDATA['.$row['title'].']]></title>'."\n";
		$xml .= '</errortype>'."\n";
	}
	
	echo $xml;
?>