<?php

	$removed=0;
	if ($handle = opendir('../temp'))
	{
    	while (false !== ($file = readdir($handle)))
		{
        	if ($file != "." && $file != "..")
			{
				if( filemtime("../temp/".$file) < (time()-24*3600) )
				{
					$removed++;
					unlink("../temp/".$file);
//		            echo "$file ".date("d-m-Y H:i", filemtime("../temp/".$file))."\n";
//					if( $removed>5 ) break;
				}
			}
	    }
	    closedir($handle);
	}
	
	echo '<TempFileUpdate>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Removed>'.$removed.'</Removed>'."\n";
	echo '</TempFileUpdate>'."\n";

?>