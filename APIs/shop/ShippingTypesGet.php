<?php

	//SOA2 SERVICE

	$results=q("SELECT * FROM shop_shipping_types;", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$keys=array_keys($row);
	
	$results=q("SELECT * FROM shop_shipping_types;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		echo '	<ShippingType>'."\n";
		for($i=0; $i<sizeof($keys); $i++)
		{
			if( !is_numeric($keys[$i]) )
				echo '		<'.$keys[$i].'><![CDATA['.$row[$keys[$i]].']]></'.$keys[$i].'>'."\n";
		}
		echo '	</ShippingType>'."\n";
	}

?>