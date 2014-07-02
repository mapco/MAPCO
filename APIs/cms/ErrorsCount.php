<?php

	/*************************
	********** SOA 2 *********
	*************************/
	
	$required=array("latest"	=> "numeric");
	
	check_man_params($required);

	$latest= $_POST["latest"];
		
	$errortypes = array();
	
	$result = q('SELECT * FROM cms_errortypes',$dbweb, __FILE__, __LINE__);
	while($row = mysqli_fetch_array($result))
	{
		$errortypes[$row['id_errortype']] = $row['title'];
	}
		
	$count = array();
	
	$sql = 'SELECT c.type AS type, c.shortMsg AS shortMsg, c.longMsg AS longMsg, e.errortype_id AS errortype_id, e.error_id AS error_id, e.time AS time FROM cms_errors AS e, cms_errorcodes AS c WHERE time>'.$latest.' AND c.errortype_id=e.errortype_id AND c.errorcode=e.error_id';
	
	$results = q($sql, $dbweb , __FILE__, __LINE__);
	while($row = mysqli_fetch_assoc($results))
	{
		if( isset($count[$row['errortype_id']][$row['error_id']] ))
		{
			$count[$row['errortype_id']][$row['error_id']]['counting']++;
			$count[$row['errortype_id']][$row['error_id']]['type'] = $row['type'];
			$count[$row['errortype_id']][$row['error_id']]['shortMsg'] = $row['shortMsg'];
			$count[$row['errortype_id']][$row['error_id']]['longMsg'] = $row['longMsg'];
			if ( $row['time'] > $count[$row['errortype_id']][$row['error_id']]['time'] )
			{
				$count[$row['errortype_id']][$row['error_id']]['time'] = $row['time'];
			}
		}
		else
		{
			$count[$row['errortype_id']][$row['error_id']]['counting']=1;
			$count[$row['errortype_id']][$row['error_id']]['type'] = $row['type'];
			$count[$row['errortype_id']][$row['error_id']]['shortMsg'] = $row['shortMsg'];
			$count[$row['errortype_id']][$row['error_id']]['longMsg'] = $row['longMsg'];
			if ( $row['time'] > $count[$row['errortype_id']][$row['error_id']]['time'] )
			{
				$count[$row['errortype_id']][$row['error_id']]['time'] = $row['time'];
			}
		}
	}
	
	$xml ='<errorcount>'."\n";
	foreach($count as $errortype => $type)
	{
		$x = 0;
		$xml.='	<errortypes>'."\n";
		foreach($type as $errorcode => $data)
		{
			$xml .='		<errorcodes>'."\n";
			$xml.= '			<error_type_id>'.$errortype.'</error_type_id>'."\n";
			$xml.= '			<error_type>'.$errortypes[$errortype].'</error_type>'."\n";
			$xml.= '			<errorcode>'.$errorcode.'</errorcode>'."\n";
			$xml.= '			<codecount>'.$data['counting'].'</codecount>'."\n";
			$xml.= '			<type><![CDATA['.$data['type'].']]></type>'."\n";
			$xml.= '			<shortMsg><![CDATA['.$data['shortMsg'].']]></shortMsg>'."\n";
			$xml.= '			<longMsg><![CDATA['.$data['longMsg'].']]></longMsg>'."\n";
			$xml.= '			<time><![CDATA['.$data['time'].']]></time>'."\n";
			$xml .='		</errorcodes>'."\n";
			$x = $x + $counting;
		}
		$xml.= '		<typecount>'.$x.'</typecount>'."\n";
		$xml .='	</errortypes>'."\n";
	}
	$xml .='	</errorcount>'."\n";
	//$xml .='</data>'."\n";
	
	echo $xml;

?>