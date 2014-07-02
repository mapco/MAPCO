<?php

	echo '<PartnerprogramsGetResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	$results=q("SELECT * FROM shop_partnerprograms ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		if( !isset($key) ) $keys=array_keys($row);
		echo '	<Partnerprogram>'."\n";
		for($i=0; $i<sizeof($keys); $i++)
		{
			if( !is_numeric($keys[$i]) )
				echo '		<'.$keys[$i].'><![CDATA['.$row[$keys[$i]].']]></'.$keys[$i].'>'."\n";
		}
		echo '	</Partnerprogram>'."\n";
	}
	echo '</PartnerprogramsGetResponse>'."\n";

?>