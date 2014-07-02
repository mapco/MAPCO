<?php
	$starttime = time()+microtime();

	if ( !isset($_POST["id_keyword"]) )
	{
		echo '<GartGetKeywords>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Keyword-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Keyword-ID angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</GartGetKeywords>'."\n";
		exit;
	}

	$res=q("SELECT * FROM shop_items_keywords WHERE id_keyword= '".$_POST["id_keyword"]."';", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res)==0)
	{
		echo '<GartGetKeywords>'."\n";
		echo '	<Ack>Warning</Ack>'."\n";
		echo '	<Warning>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Keyword nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte kein Keyword zur Keyword-ID gefunden werden.</longMsg>'."\n";
		echo '	</Warning>'."\n";
		echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
		echo '</GartGetKeywords>'."\n";
		exit;
	}
	else 
	{
		//performance
		$stoptime = time()+microtime();
		$time = $stoptime-$starttime;

		echo '<GartGetKeywords>'."\n";
		echo '	<Ack>Success</Ack>'."\n";
		echo '	<Success>'."\n";
		while ($row=mysqli_fetch_array($res))
		{
			echo '<keyword_'.$row["language_id"].'><![CDATA['.$row["keyword"].']]></keyword_'.$row["language_id"].'>';
		}		
		echo '	</Success>'."\n";
		echo '	<Runtime>'.number_format($time, 2).'</Runtime>'."\n";
		echo '</GartGetKeywords>'."\n";
	}

?>