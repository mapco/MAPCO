<?php
	$from=strtotime($_POST["from"]);
	$to=strtotime($_POST["to"])+3600*24;
	$comp_from=strtotime($_POST["comp_from"]);
	$comp_to=strtotime($_POST["comp_to"])+3600*24;

	if(isset($_POST["Notnull"]) && $_POST["Notnull"]>0) {$notnull=true;} else {$notnull=false;}
	if(isset($_POST["comp_from"]) && $_POST["comp_from"]>0 && isset($_POST["comp_to"]) && $_POST["comp_to"]>0) {$comparison=true;} else {$comparison=false;}
	
	if(isset($_POST["mode"]) && $_POST["mode"]=="list") 
	{
		$ArtList=array();
		//ARTIKEL AUS ARTIKELLISTE LESEN
		if ($_POST["listtype"]=="ArtList")
		{
			$i=0;
			$res=q("select b.MPN from shop_lists_items a, shop_items b where a.list_id = '".$_POST["ArtList"]."' and a.item_id=b.id_item;", $dbshop, __FILE__, __LINE__);
			while ($row=mysql_fetch_array($res)) {$ArtList[$i]=$row["MPN"]; $i++;}
		}
		//ARTIKEL AUS PRICESUGGESTIONS LESEN
		if ($_POST["listtype"]=="PriceSuggestions")
		{
			$i=0;
			$res=q("select b.MPN from shop_price_suggestions a, shop_items b where a.item_id=b.id_item;", $dbshop, __FILE__, __LINE__);
			while ($row=mysql_fetch_array($res)) {$ArtList[$i]=$row["MPN"]; $i++;}
		}
	}

	//ARTIKELBEZEICHNUNGEN AUSLESEN
	$res_data=q("select a.id_item, a. title, b.MPN from shop_items_de a, shop_items b where a.id_item=b.id_item;", $dbshop, __FILE__, __LINE__);
	while ($row_data=mysql_fetch_array($res_data)) {
		$item_data[$row_data["MPN"]]=$row_data["title"];
	}

	//VERKÄUFE MAPCO - ZEITRAUM
	$res=q("select * from ebay_orders_items where account_id = '1' AND CreatedDateTimestamp>='".$from."' AND CreatedDateTimestamp<='".$to."';",$dbshop, __FILE__, __LINE__);

	$item1 =array();
	while ($row=mysql_fetch_array($res)) {
		if (!isset($item1[$row["ItemSKU"]]))
		{
			$item1[$row["ItemSKU"]]=0;
		}
			$item1[$row["ItemSKU"]]+= number_format($row["QuantityPurchased"]);
	}
	

	//VERKÄUFE AP
	$res=q("select * from ebay_orders_items where account_id = '2' AND CreatedDateTimestamp>='".$from."' AND CreatedDateTimestamp<='".$to."';",$dbshop, __FILE__, __LINE__);

	$item2 =array();
	while ($row=mysql_fetch_array($res)) {
		if (!isset($item2[$row["ItemSKU"]]))
		{
			$item2[$row["ItemSKU"]]=0;
		}
			$item2[$row["ItemSKU"]]+= number_format($row["QuantityPurchased"]);
		
	}
	
	//VERKÄUFE VERGLEICHSZEITRAUM
	if($comparison)
	{
		//VERKÄUFE MAPCO - VERGLEICHSZEITRAUM
		$res=q("select * from ebay_orders_items where account_id = '1' AND CreatedDateTimestamp>='".$comp_from."' AND CreatedDateTimestamp<='".$comp_to."';",$dbshop, __FILE__, __LINE__);

		$comp_item1 =array();
		while ($row=mysql_fetch_array($res)) {
			if (!isset($comp_item1[$row["ItemSKU"]]))
			{
				$comp_item1[$row["ItemSKU"]]=0;
			}
				$comp_item1[$row["ItemSKU"]]+= number_format($row["QuantityPurchased"]);
		}
		//VERKÄUFE AP - VERGLEICHSZEITRAUM
		$res=q("select * from ebay_orders_items where account_id = '2' AND CreatedDateTimestamp>='".$comp_from."' AND CreatedDateTimestamp<='".$comp_to."';",$dbshop, __FILE__, __LINE__);

		$comp_item2 =array();
		while ($row=mysql_fetch_array($res)) {
			if (!isset($comp_item2[$row["ItemSKU"]]))
			{
				$comp_item2[$row["ItemSKU"]]=0;
			}
				$comp_item2[$row["ItemSKU"]]+= number_format($row["QuantityPurchased"]);
		
		}
	} // IF VERGLEICHSZEITRAUM
	
	$masterlist = array();
	$comp_masterlist = array();
//VERKAUFSLISTEN AUS MAPCO & AP ZUSAMMENFÜHREN
	if(isset($_POST["mode"]) && $_POST["mode"]=="all") 
	{
		while(list ($key, $value) = each($item1)){
			$masterlist[$key]=0;
		}
		while(list ($key, $value) = each($item2)){
			$masterlist[$key]=0;
		}
		//VERGLEICHSZEITRAUM
		if($comparison)
		{
			while(list ($key, $value) = each($comp_item1)){
				$masterlist[$key]=0;
			}
			while(list ($key, $value) = each($comp_item2)){
				$masterlist[$key]=0;
			}
			$comp_masterlist=$masterlist;
			while(list ($key, $value) = each ($comp_masterlist)) {
				if ( isset($comp_item1[$key]) ) {$val1=$comp_item1[$key];} else {$val1=0;}
				if ( isset($comp_item2[$key]) ) {$val2=$comp_item2[$key];} else {$val2=0;}
				$comp_masterlist[$key]=number_format($val1)+number_format($val2);
			}

		}
		while(list ($key, $value) = each ($masterlist)) {
			if ( isset($item1[$key]) ) {$val1=$item1[$key];} else {$val1=0;}
			if ( isset($item2[$key]) ) {$val2=$item2[$key];} else {$val2=0;}
			$masterlist[$key]=number_format($val1)+number_format($val2);
		}
	}

	if(isset($_POST["mode"]) && $_POST["mode"]=="list") 
	{
		for ($i=0; $i<sizeof($ArtList); $i++) {
			$masterlist[$ArtList[$i]]=0;
		}
		if($comparison)
		{	
			$comp_masterlist=$masterlist;
			while(list ($key, $value) = each ($comp_masterlist)) {
				if ( isset($comp_item1[$key]) ) {$val1=$comp_item1[$key];} else {$val1=0;}
				if ( isset($comp_item2[$key]) ) {$val2=$comp_item2[$key];} else {$val2=0;}
				$comp_masterlist[$key]=number_format($val1)+number_format($val2);
			}
		}
		while(list ($key, $value) = each ($masterlist)) {
			if ( isset($item1[$key]) ) {$val1=$item1[$key];} else {$val1=0;}
			if ( isset($item2[$key]) ) {$val2=$item2[$key];} else {$val2=0;}
			$masterlist[$key]=number_format($val1)+number_format($val2);
		}
	}

	arsort($masterlist);
	$gesamt=0;
	$comp_gesamt=0;
//AUSGABE
	echo '<table>';
	echo '<th></th><th></th><th colspan="3" style="text-align:center;">'.date("d.m.Y",$from).' - '.date("d.m.Y",$to-1).'</th>';
	if($comparison)
	{ echo '<th colspan="3" style="text-align:center;">'.date("d.m.Y",$comp_from).' - '.date("d.m.Y",$comp_to-1).'</th>';}
	echo '</tr>';
	echo '<tr><th style="width:70px;">SKU</th><th style="width:250px;">Titel</th><th style="width:70px;">MAPCO</th><th style="width:70px;">AP</th><th style="width:70px;">GESAMT</th>';
	if($comparison)
	{ echo '<th style="width:70px;">MAPCO</th><th style="width:70px;">AP</th><th style="width:70px;">GESAMT</th>';}
	echo '</tr>';

		while (list($key, $value) = each($masterlist)) {
			if ( isset($item1[$key]) ) {$val1=$item1[$key];} else {$val1=0;}
			if ( isset($item2[$key]) ) {$val2=$item2[$key];} else {$val2=0;}
	if ( ($notnull && ( ($comparison==true && ($masterlist[$key]>0 || $comp_masterlist[$key]>0)) || ( $comparison==false && $masterlist[$key]>0))) || !$notnull) {

			echo '<tr><td>'.$key.'</td><td>'.$item_data[$key].'</td><td>'.$val1.'</td><td>'.$val2.'</td><td style="background-color:#ccc"><b>'.$masterlist[$key].'</b></td>';
			$gesamt+=$masterlist[$key];
			if($comparison)
			{	if ( isset($comp_item1[$key]) ) {$comp_val1=$comp_item1[$key];} else {$comp_val1=0;}
				if ( isset($comp_item2[$key]) ) {$comp_val2=$comp_item2[$key];} else {$comp_val2=0;}
				echo '<td style="background-color:#ddd">'.$comp_val1.'</td><td  style="background-color:#ddd">'.$comp_val2.'</td><td  style="background-color:#bbb"><b>'.$comp_masterlist[$key].'</b></td>';
				$comp_gesamt+=$comp_masterlist[$key];
			}
		echo '</tr>';
		}
	}
	if (  ($comparison==true && ($gesamt>0 || $comp_gesamt>0)) || ( $comparison==false && $gesamt>0)) {
		echo '<tr><td></td><td></td><td></td><td style="background-color:#bbb"><b>Gesamt</b></td><td style="background-color:#bbb">'.$gesamt.'</td>';
		if($comparison)
		{echo '<td></td><td style="background-color:#bbb"><b>Gesamt</b></td><td style="background-color:#bbb">'.$comp_gesamt.'</td>';}
		echo '</tr>';
	}
	echo '</table>';


		
?>