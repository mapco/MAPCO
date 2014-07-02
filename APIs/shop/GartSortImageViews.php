<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["list"]) )
	{
		echo '<GartSortImageViewResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Artikelansicht-Liste nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Artikelansicht-Liste übermittelt werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartSortImageViewResponse>'."\n";
		exit;
	}
	$list=$_POST["list"];
	$ordercount=0;
	while (list($key, $val) = each ($list)) 

	{
		if ($val!="") 
		{ 
			$ordercount++;
			$imageviewid=number_format(str_replace("imageviewid", "", $val));
			$sql="UPDATE cms_views_gart SET ordering = ".$ordercount." WHERE id_view = ".$imageviewid.";";
			q($sql, $dbweb, __FILE__, __LINE__);

		}
			 
	}
	
	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
	
	echo '<GartSortImageViewResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
	echo '</GartSortImageViewResponse>'."\n";

?>