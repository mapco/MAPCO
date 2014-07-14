<?php
/**********************************************************
*	SOA2 Service
*		shop.CustomerTypesGet
*
*	@author: Christopher Händler <chaendler(at)mapco.de>
*	@version: 0.1
*	@modified: 	08/07/14
*
*********************/

	//echo '<CustomerTypesGetResponse>'."\n";
	//echo '	<Ack>Success</Ack>'."\n";
	
	$results=q("SELECT * FROM shop_customer_types;", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$keys=array_keys($row);
	
	$results=q("SELECT * FROM shop_customer_types;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		echo '<customer_types>'."\n";
		for($i=0; $i<sizeof($keys); $i++)
		{
			if( !is_numeric($keys[$i]) )
				echo '	<'.$keys[$i].'><![CDATA['.$row[$keys[$i]].']]></'.$keys[$i].'>'."\n";
		}
		echo '</customer_types>'."\n";
	}
	//echo '</CustomerTypesGetResponse>'."\n";