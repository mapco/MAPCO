<?php

	/*************************
	********** SOA 2 *********
	*************************/
	
	$sql = 'SELECT * FROM crm_costumer_lists';

	if ( $_POST['type_id'] != "0" )
	{
		$sql .= ' WHERE type='.$_POST['type_id'];
		
		if ( $_POST['type_id'] == "1" )
		{
			$sql .= ' AND firstmod_user='.$_SESSION["id_user"];
		}
	}
	
	$sql .= ';';

	$res=q($sql, $dbweb, __FILE__, __LINE__);
	while ($row_list=mysqli_fetch_assoc($res))
	{ 
		$lists[$row_list['id_list']]=$row_list;
		
		$res2=q("SELECT COUNT(id) AS ccount FROM crm_costumer_lists_customers WHERE list_id=".$row_list['id_list'].";", $dbweb, __FILE__, __LINE__);
		$row2 = mysqli_fetch_assoc($res2);
		$lists[$row_list['id_list']]['ccount'] = $row2['ccount'];
	}
	$num_rows = mysqli_num_rows($res);
	
	$xml = '';

	//if ( sizeof($lists) > 0 )
	//{
		foreach($lists as $list_id => $list)
		{
			$xml .= '<customer_list>'."\n";
			$xml .= '	<customer_list_id>'.$list_id.'</customer_list_id>'."\n";
			foreach($list as $key => $value)
			{
				if ( !is_numeric($value) )
				{
					$value = '<![CDATA['.$value.']]>';	
				}
				$xml .= '	<'.$key.'>'.$value.'</'.$key.'>'."\n";
			}
			$xml .= '</customer_list>'."\n";
		}
	//}
	
	$xml .= '<num_rows>'.$num_rows.'</num_rows>'."\n";
	print $xml;
?>