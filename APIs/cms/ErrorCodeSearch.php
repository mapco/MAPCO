<?php
	/*************************
	********** SOA 2 *********
	*************************/
	
	$required=array("search_value"	=> "text", "page"	=> "numeric", "per_page"	=> "numeric", "filter_col"	=> "text");
	
	check_man_params($required);
	
	$limit = '';
	$where = '';
	
	$search_value= $_POST["search_value"];
	$page = $_POST["page"];
	$per_page = $_POST["per_page"];
	$filter_col = $_POST["filter_col"];
	$filter_value = $_POST["filter_value"];

	if ($search_value != '' || $filter_value != 0 ) 
	{
		$where.='WHERE ';
		
		if ($search_value != '')
		{
			$where .= "errorcode LIKE '%".$search_value."%'";
			$where .= " OR shortMsg LIKE '%".$search_value."%' OR longMsg LIKE '%".$search_value."%'";
		}
		if ($filter_value != '')
		{
			if (strlen($where)>6){ $where .= ' OR '; }
			$where .= "errortype_id=".$filter_value;
		}
	}

	$sql = 'SELECT COUNT(id) AS entries FROM cms_errorcodes '.$where;
	$results = q($sql, $dbweb , __FILE__, __LINE__);
	$row = mysqli_fetch_assoc($results);
	$entries = $row['entries'];
	$pages = $entries/$per_page;
	$pages = ceil($pages);

	$limit_first = ($page-1)*$per_page;
	
	$limit = " LIMIT ".$limit_first.", ".$per_page;	

	$result = q('SELECT * FROM cms_errortypes',$dbweb, __FILE__, __LINE__);
	while($row = mysqli_fetch_array($result))
	{
		$errortypes[$row['id_errortype']] = $row['title'];
	}
	
	$count = array();

	$sql = "SELECT * FROM cms_errorcodes ".$where." ORDER BY id DESC ".$limit;

	$xml ='';
	$results = q($sql, $dbweb , __FILE__, __LINE__);
	while($row = mysqli_fetch_assoc($results))
	{
		$xml.=	'<codes>'."\n";
		$xml.=	'	<errorcode><![CDATA['.$row['errorcode'].']]></errorcode>'."\n";
		$xml.= '	<error_type><![CDATA['.$errortypes[$row['errortype_id']].']]></error_type>'."\n";
		$xml.=	'	<errortype_id>'.$row['errortype_id'].'</errortype_id>'."\n";
		$xml.=	'	<type><![CDATA['.$row['type'].']]></type>'."\n";
		$xml.=	'	<shortMsg><![CDATA['.$row['shortMsg'].']]></shortMsg>'."\n";
		$xml.=	'	<longMsg><![CDATA['.$row['longMsg'].']]></longMsg>'."\n";
		$xml.=	'</codes>'."\n";
	}

	($entries>250)? $xml.='<message>Mehr als 250 Fehlercodes gefunden! Bitte suche einschränken.</message>' : $xml.='<message></message>';
	$xml .= '<pages>'.$pages.'</pages>';

	echo $xml;

?>