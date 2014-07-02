<?php

	function GetLimit($page,$per_page)
	{
		$limit_first = ($page-1)*$per_page;
		
		$limit = " LIMIT ".$limit_first.", ".$per_page;
		
		return $limit;
	}

	$xml ='';

	/*************************
	********** SOA 2 *********
	*************************/
	
	$required=array("mode" => "numeric");
	check_man_params($required);

	$where = '';
	if ($_POST['mode'] == 2)
	{
		$required=array("page"	=> "numericNN", "per_page"	=>	"numericNN");
		check_man_params($required);
		$limit = GetLimit($_POST["page"],$_POST["per_page"]);
		
		$s = 0;
		
		if ( $_POST["search_value"] != 0 )
		{
			$s = 1;
			$search_value= $_POST["search_value"];
			$where_content = ' error_id LIKE '.$search_value.' OR file LIKE '.$search_value.' OR text LIKE '.$search_value;
		}
		
		if ( $_POST["filter_value"] != 0 && $_POST['filter_col'] != '' )		
		{
			if ($s==1){ $where .= ' AND'; }
			$where_content = ' '.$_POST["filter_col"].'='.$_POST["filter_value"];
			$s = 2;
		}
		
		if  ($s != 0)
		{
			$where = ' WHERE'.$where_content;
		}
	}
	else
	{
		$required=array("type_id" => "numericNN", "code_id" => "numericNN");
		check_man_params($required);
		
		$where = ' WHERE errortype_id = '.$_POST["type_id"].' AND error_id = '.$_POST["code_id"];
		if ($_POST['mode'] == 0)
		{
			$limit = ' LIMIT 5';
		}
		else
		{
			$required=array("page"	=> "numericNN", "per_page"	=>	"numericNN");
			check_man_params($required);
			$limit = GetLimit($_POST["page"],$_POST["per_page"]);
		}
	}

	if ( $_POST['mode'] != 0 )
	{
		$sql = 'SELECT COUNT(id_error) AS entries FROM cms_errors'.$where;
		$results = q($sql, $dbweb , __FILE__, __LINE__);
		$row = mysqli_fetch_assoc($results);
		$pages = $row['entries']/$_POST["per_page"];
		$pages = ceil($pages);
	}
	
	$sql = 'SELECT id_error, error_id, file, line, time, text FROM cms_errors'.$where." ORDER BY time DESC".$limit;
	
	//$xml.=	'<error>'.$sql.'</error>\n'

	$results = q($sql, $dbweb , __FILE__, __LINE__);
	while($row = mysqli_fetch_assoc($results))
	{
		$row['text'] = explode('<![CDATA[',$row['text']);
		$row['text'] =implode('<![ACDATA[',$row['text']);
		
		$row['text'] = explode(']]>',$row['text']);
		$row['text'] =implode(']A]>',$row['text']); 
		
		$xml.=	'<error>'."\n";
		$xml.=	'	<id_error><![CDATA['.$row['id_error'].']]></id_error>'."\n";
		$xml.=	'	<error_id><![CDATA['.$row['error_id'].']]></error_id>'."\n";
		$xml.=	'	<file><![CDATA['.$row['file'].']]></file>'."\n";
		$xml.=	'	<line><![CDATA['.$row['line'].']]></line>'."\n";
		$xml.=	'	<text><![CDATA['.$row['text'].']]></text>'."\n";
		$xml.=	'	<time><![CDATA['.$row['time'].']]></time>'."\n";
		$xml.=	'</error>'."\n";
	}
	
	$xml .= '<pages>'.$pages.'</pages>';
	
	echo $xml;

?>