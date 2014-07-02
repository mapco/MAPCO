<?php
	include("../config.php");

	function cyrillic($text)
	{
		$pattern     = array("À", "Á", "Â", "Ã", "Ä", "Å", "È", "É", "Ê", "Ë", "Ì", "Î", "Ð", "Ñ", "Ò", "Ó", "Ô", "Õ", "Ö", "×", "Ø", "Ý", "ß", "à", "á", "â", "ã", "ä", "å", "æ", "ç", "è", "é", "ê", "ë", "ì", "í", "î", "ï", "ð", "ñ", "ò", "ó", "ô", "õ", "ö", "÷", "ø", "û", "ü", "ý", "þ", "ÿ");
		$replacement = array("А", "Б", "В", "Г", "Д", "Е", "И", "Й", "К", "Л", "М", "О", "Р", "С", "Т", "У", "Ф", "Х", "Ц", "Ч", "Ш", "Э", "Я", "a", "б", "в", "г", "д", "e", "ж", "з", "и", "й", "к", "л", "м", "н", "о", "п", "р", "с", "т", "у", "ф", "х", "ц", "ч", "ш", "ы", "ь", "э", "ю", "я");
		for ($i=0; $i<sizeof($pattern); $i++)
		{
			$text=ereg_replace($pattern[$i], $replacement[$i], $text);
		}
		return($text);
	}
	
	function get_title($artnr)
	{
		//Generische Bezeichnung
		$results=q("SELECT Bez FROM t_200, t_320, t_030 WHERE t_200.ArtNr='".$artnr."' AND t_200.GART=t_320.GenArtNr AND t_320.BezNr=t_030.BezNr AND SprachNr='001';", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		return($row["Bez"].' ('.$artnr.')');
	}
	
	function get_short_description($artnr)
	{
		//Kurzbeschreibung erzeugen
		$j=0;
		$criteria=array();
		$results2=q("SELECT * FROM t_210, t_050, t_030 WHERE t_210.ArtNr='".$artnr."' AND SprachNr='001' AND t_210.KritNr=t_050.KritNr AND t_050.BezNr=t_030.BezNr;", $dbshop, __FILE__, __LINE__);
		while ($row2=mysqli_fetch_array($results2))
		{
			if ($row2["Typ"]=="K")
			{
				if (is_numeric($row2["KritVal"]))
				{
					$results3=q("SELECT Bez FROM t_052, t_030 WHERE t_052.TabNr=".$row2["TabNr"]." AND t_052.Schl=".$row2["KritVal"]." AND t_052.BezNr=t_030.BezNr AND SprachNr='001';", $dbshop, __FILE__, __LINE__);
				}
				else
				{
				$results3=q("SELECT Bez FROM t_052, t_030 WHERE t_052.TabNr=".$row2["TabNr"]." AND t_052.Schl='".$row2["KritVal"]."' AND t_052.BezNr=t_030.BezNr AND SprachNr='001';", $dbshop, __FILE__, __LINE__);
				}
				$row3=mysqli_fetch_array($results3);
				$kritwert=$row3["Bez"];
			}
			else $kritwert=$row2["KritVal"];
			$bez=$row2["Bez"];
			if ($sprache[$i]=="ru")
			{
				$title=cyrillic($title);
				$bez=cyrillic($bez);
				$kritwert=cyrillic($kritwert);
			}
			$criteria[$j][0]=$bez;
			$criteria[$j][1]=$kritwert;
			$j++;
		}
		$short_description="";
		for($i=0; $i<sizeof($criteria); $i++)
		{
			$short_description.=$criteria[$i][0].': '.$criteria[$i][1];
			if (($i+1)<sizeof($criteria)) $short_description.='; ';
		}
		return($short_description);
	}
	
	function get_description($artnr)
	{
		return("");
	}
	
	function get_price($artnr)
	{
		$results=q("SELECT Preis FROM t_201 WHERE ArtNr='".$artnr."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		return($row["Preis"]/100);
	}
	
	$sprache = array("de", "en", "ru", "fr", "it", "zh");

	//Neue Artikel?
	$results=q("SELECT * FROM t_200;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$results2=q("SELECT * FROM shop_items WHERE MPN='".$row["ArtNr"]."';", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($results2)==0)
		{
			q("INSERT INTO shop_items (title, short_description, description, price) VALUES('".get_title($row["ArtNr"])."', '".get_short_description($row["ArtNr"])."', '".get_description($row["ArtNr"])."', '".get_price($row["ArtNr"])."');", $dbshop, __FILE__, __LINE__);
		}
		$item_id=mysqli_insert_id($dbshop);
		q("INSERT INTO shop_items (id_item, MPN, lastmod) VALUES('".$item_id."', '".$row["ArtNr"]."', '".time()."');", $dbshop, __FILE__, __LINE__);
		echo 'Artikel Nr. '.$row["ArtNr"].' aktualisiert.<br />';
	}
?>