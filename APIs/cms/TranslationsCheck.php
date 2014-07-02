<?php

	$t=array();
	$results=q("SELECT * FROM cms_translations;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$t[]=$row;
	}

	for( $i=0; $i<sizeof($t); $i++ )
	{
		if( $t[$i][2]=="" and $t[$i][3]=="" and $t[$i][4]=="" and $t[$i][5]=="" and $t[$i][6]=="" and $t[$i][7]=="" and $t[$i][8]=="" )
		{
			for($j=0; $j<sizeof($t); $j++)
			{
				if( $j!=$i )
				{
					for($k=2; $k<9; $k++)
					{
						q("DELETE FROM cms_translations WHERE id_translation=".$t[$i][0].";", $dbweb, __FILE__, __LINE__);
						if( $t[$i][1]===$t[$j][$k] ) echo $t[$i][0].' '.$t[$i][1]."\n";
					}
				}
			}
		}
	}
?>