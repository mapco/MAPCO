<?php
	
	/*
		soa2-service
	*/

	$xml = '';
	
	$results2 = q( "SELECT * FROM cms_labels ORDER BY label", $dbweb, __FILE__, __LINE__ );
	
	while ( $cms_labels = mysqli_fetch_assoc( $results2 ) ) {
		$xml .= '<labels>' . "\n";
		foreach ( $cms_labels as $key => $value ) {
			$xml .= '  <' . $key . '><![CDATA[' . $value . ']]>>' . '</' . $key . '>' . "\n";
		}
		$xml .= '</labels>' . "\n";
	}
	
	echo $xml;

?>