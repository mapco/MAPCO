<?php
	
	$results=q("SELECT * FROM ebay_accounts ORDER BY ordering;", $dbshop, __FILE__, __LINE__);

	//output
	echo '<Accounts>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	while( $row=mysqli_fetch_array($results) )
	{
		echo '<Account>'."\n";
		$keys=array_keys($row);
		
		for($i=0; $i<sizeof($keys); $i++)
		{
			if( !is_numeric($keys[$i]) )
				echo '	<'.$keys[$i].'>'.str_replace("&", "&amp;", ($row[$keys[$i]])).'</'.$keys[$i].'>'."\n";
		}
		echo '</Account>'."\n";
	}
	echo '</Accounts>'."\n";

?>