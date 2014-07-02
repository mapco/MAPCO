<?php

	$results=q("SELECT * FROM shop_orders_types;", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$keys=array_keys($row);
	
//	echo '<CurrenciesGetResponse>'."\n";
//	echo '	<Ack>Success</Ack>'."\n";
	$results=q("SELECT * FROM shop_orders_types;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		echo '<OrderType>'."\n";
		for($i=0; $i<sizeof($keys); $i++)
		{
			if( !is_numeric($keys[$i]) )
				echo '	<'.$keys[$i].'><![CDATA['.$row[$keys[$i]].']]></'.$keys[$i].'>'."\n";
		}
		echo '</OrderType>'."\n";
	}
//	echo '</CurrenciesGetResponse>'."\n";

?>