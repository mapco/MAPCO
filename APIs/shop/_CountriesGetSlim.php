<?php

	$results=q("SELECT id_country,country FROM shop_countries;", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$keys=array_keys($row);
	

	$results=q("SELECT id_country,country FROM shop_countries;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		echo '<shop_countries>'."\n";
		for($i=0; $i<sizeof($keys); $i++)
		{
			if( !is_numeric($keys[$i]) )
				echo '	<'.$keys[$i].'><![CDATA['.$row[$keys[$i]].']]></'.$keys[$i].'>'."\n";
		}
		echo '</shop_countries>'."\n";
	}


?>