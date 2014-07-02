<?php
	$results=q("SELECT * FROM cms_articles_haendlerbund;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$url="https://www.hb-intern.de/www/hbm/api/live_rechtstexte.htm?APIkey=1IqJF0ap6GdDNF7HKzhFyciibdml8t4v&did=".$row["did"]."&AccessToken=".$row["AccessToken"];

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		//curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_USERAGENT, "INPUT DATA SCRIPT");
		curl_setopt($curl, CURLOPT_URL, $url);
		$result = curl_exec($curl);
		curl_close ($curl);

		if ( $result!="" )
		{
			//change Autopartner texts
			if( $row["AccessToken"]=="8f37732e-0511-421a-a52c-ab7d29ba1b19" )
			{
				$result=str_replace("den Gesch&auml;ftsf&uuml;hrer", "die Geschäftsführerin", $result);
				$result=str_replace("Detlev Seeliger", "Uta Seeliger", $result);
				$result=str_replace("Moosweg 1", "Gregor-von-Brück-Ring 1", $result);
				$result=str_replace("Borkheide", "Brück", $result);
				$result=str_replace("033845 600 30", "033844 75 82 80", $result);
				$result=str_replace("jhabermann@mapco.de", "info@ihr-autopartner.de", $result);
				$result=str_replace("HRB 3965", "HRB 7792", $result);
				$result=str_replace("Mapco Autotechnik GmbH", "Autopartner GmbH KFZ- und Teile-Handel", $result);
				$result=str_replace("nach Auftragsbest&auml;tigung", "nach Eingang der Bestellung", $result);
			}
			q("	UPDATE cms_articles
				SET article='".mysqli_real_escape_string($dbweb, $result)."',
					lastmod=".time().",
					lastmod_user=1
				WHERE id_article=".$row["article_id"].";", $dbweb, __FILE__, __LINE__);
			$xml .= '	<Updated>'.$row["title"].'</Updated>'."\n";
		}
		else
		{
			$xml .= '	<Error>'.$url.'</Error>'."\n";
		}
	}

	echo '<AGBUpdateResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo $xml;
	echo '<AGBUpdateResponse>'."\n";

?>