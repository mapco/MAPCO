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
			while ($row=mysqli_fetch_array($res)) {$ArtList[$i]=$row["MPN"]; $i++;}
		}
		//ARTIKEL AUS PRICESUGGESTIONS LESEN
		if ($_POST["listtype"]=="PriceSuggestions")
		{
			$i=0;
			$res=q("select b.MPN from shop_price_suggestions a, shop_items b where a.item_id=b.id_item and a.imported='1';", $dbshop, __FILE__, __LINE__);
			while ($row=mysqli_fetch_array($res)) {$ArtList[$i]=$row["MPN"]; $i++;}
		}
	}
	//ARTIKELBEZEICHNUNGEN AUSLESEN
	$res_data=q("select a.id_item, a. title, b.MPN from shop_items_de a, shop_items b where a.id_item=b.id_item;", $dbshop, __FILE__, __LINE__);
	while ($row_data=mysqli_fetch_array($res_data)) {
		$item_data[$row_data["MPN"]]=$row_data["title"];
//		$item_group[$row_data["MPN"]]=$row_data["category_id"];
	}

	//VERKÄUFE MAPCO - ZEITRAUM
	$res=q("select * from ebay_orders_items where account_id = '1' AND CreatedDateTimestamp>='".$from."' AND CreatedDateTimestamp<='".$to."';",$dbshop, __FILE__, __LINE__);

	$item1_qnt =array();
	$item1_total =array();
	while ($row=mysqli_fetch_array($res)) {
		if (!isset($item1_qnt[$row["ItemSKU"]]))
		{
			$item1_qnt[$row["ItemSKU"]]=0;
			$item1_total[$row["ItemSKU"]]=0;			
		}
			$qnt=number_format($row["QuantityPurchased"]);
			$item1_qnt[$row["ItemSKU"]]+= $qnt;
			//$item1_total[$row["ItemSKU"]]+=number_format($row["TransactionPrice"],2)*$qnt;			
			$item1_total[$row["ItemSKU"]]+=$row["TransactionPrice"]*$qnt;			
	}
	

	//VERKÄUFE AP
	$res=q("select * from ebay_orders_items where account_id = '2' AND CreatedDateTimestamp>='".$from."' AND CreatedDateTimestamp<='".$to."';",$dbshop, __FILE__, __LINE__);
	$item2_qnt =array();
	$item2_total =array();
	while ($row=mysqli_fetch_array($res)) {
		if (!isset($item2_qnt[$row["ItemSKU"]]))
		{
			$item2_qnt[$row["ItemSKU"]]=0;
			$item2_total[$row["ItemSKU"]]=0;			
		}
			$qnt=number_format($row["QuantityPurchased"]);
			$item2_qnt[$row["ItemSKU"]]+= $qnt;
			//$item2_total[$row["ItemSKU"]]+=number_format($row["TransactionPrice"],2)*$qnt;			
			$item2_total[$row["ItemSKU"]]+=$row["TransactionPrice"]*$qnt;			
	}
	
	//VERKÄUFE VERGLEICHSZEITRAUM
	if($comparison)
	{
		//VERKÄUFE MAPCO - VERGLEICHSZEITRAUM
		$res=q("select * from ebay_orders_items where account_id = '1' AND CreatedDateTimestamp>='".$comp_from."' AND CreatedDateTimestamp<='".$comp_to."';",$dbshop, __FILE__, __LINE__);

		$comp_item1_qnt =array();
		$comp_item1_total =array();
		while ($row=mysqli_fetch_array($res)) {
			if (!isset($comp_item1_qnt[$row["ItemSKU"]]))
			{
				$comp_item1_qnt[$row["ItemSKU"]]=0;
				$comp_item1_total[$row["ItemSKU"]]=0;			
			}
				$qnt=number_format($row["QuantityPurchased"]);
				$comp_item1_qnt[$row["ItemSKU"]]+= $qnt;
				//$comp_item1_total[$row["ItemSKU"]]+=number_format($row["TransactionPrice"],2)*$qnt;			
				$comp_item1_total[$row["ItemSKU"]]+=$row["TransactionPrice"]*$qnt;			
		}
		
		//VERKÄUFE AP - VERGLEICHSZEITRAUM
		$res=q("select * from ebay_orders_items where account_id = '2' AND CreatedDateTimestamp>='".$comp_from."' AND CreatedDateTimestamp<='".$comp_to."';",$dbshop, __FILE__, __LINE__);

		$comp_item2_qnt =array();
		$comp_item2_total =array();
		while ($row=mysqli_fetch_array($res)) {
			if (!isset($comp_item2_qnt[$row["ItemSKU"]]))
			{
				$comp_item2_qnt[$row["ItemSKU"]]=0;
				$comp_item2_total[$row["ItemSKU"]]=0;			
			}
				$qnt=number_format($row["QuantityPurchased"]);
				$comp_item2_qnt[$row["ItemSKU"]]+= $qnt;
				//$comp_item2_total[$row["ItemSKU"]]+=number_format($row["TransactionPrice"],2)*$qnt;			
				$comp_item2_total[$row["ItemSKU"]]+=$row["TransactionPrice"]*$qnt;
		}
		
	} // IF VERGLEICHSZEITRAUM
	
	$masterlist = array();
	$comp_masterlist = array();
//VERKAUFSLISTEN AUS MAPCO & AP ZUSAMMENFÜHREN
	if(isset($_POST["mode"]) && $_POST["mode"]=="all") 
	{
		while(list ($key, $value) = each($item1_qnt)){
			$masterlist[$key]=0;
		}
		while(list ($key, $value) = each($item2_qnt)){
			$masterlist[$key]=0;
		}
		//VERGLEICHSZEITRAUM
		if($comparison)
		{
			while(list ($key, $value) = each($comp_item1_qnt)){
				$masterlist[$key]=0;
			}
			while(list ($key, $value) = each($comp_item2_qnt)){
				$masterlist[$key]=0;
			}
			$comp_masterlist=$masterlist;
			while(list ($key, $value) = each ($comp_masterlist)) {
				if ( isset($comp_item1_qnt[$key]) ) {$val1=$comp_item1_qnt[$key];} else {$val1=0;}
				if ( isset($comp_item2_qnt[$key]) ) {$val2=$comp_item2_qnt[$key];} else {$val2=0;}
				$comp_masterlist[$key]=number_format($val1)+number_format($val2);
			}

		}

		while(list ($key, $value) = each ($masterlist)) {
			if ( isset($item1_qnt[$key]) ) {$val1=$item1_qnt[$key];} else {$val1=0;}
			if ( isset($item2_qnt[$key]) ) {$val2=$item2_qnt[$key];} else {$val2=0;}
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
				if ( isset($comp_item1_qnt[$key]) ) {$val1=$comp_item1_qnt[$key];} else {$val1=0;}
				if ( isset($comp_item2_qnt[$key]) ) {$val2=$comp_item2_qnt[$key];} else {$val2=0;}
				$comp_masterlist[$key]=number_format($val1)+number_format($val2);
			}
		}

		while(list ($key, $value) = each ($masterlist)) {
			if ( isset($item1_qnt[$key]) ) {$val1=$item1_qnt[$key];} else {$val1=0;}
			if ( isset($item2_qnt[$key]) ) {$val2=$item2_qnt[$key];} else {$val2=0;}
			$masterlist[$key]=number_format($val1)+number_format($val2);
		}
	}
	arsort($masterlist);
	$dump=reset($masterlist);
	$gesamt_qnt=0;
	$gesamt_total=0;
	$gesamt_qnt1=0;
	$gesamt_total1=0;
	$gesamt_qnt2=0;
	$gesamt_total2=0;

	$comp_gesamt_qnt=0;
	$comp_gesamt_total=0;
	$comp_gesamt_qnt1=0;
	$comp_gesamt_total1=0;
	$comp_gesamt_qnt2=0;
	$comp_gesamt_total2=0;

//AUSGABE

	echo '<table>';
	echo '<th></th><th colspan="6" style="text-align:center;">'.date("d.m.Y",$from).' - '.date("d.m.Y",$to-1).'</th>';
	if($comparison)
	{ echo '<th colspan="6" style="text-align:center;">'.date("d.m.Y",$comp_from).' - '.date("d.m.Y",$comp_to-1).'</th>';}
	echo '</tr>';
	echo '<tr><th style="width:300px;">Titel</th><th style="width:20px;"></th><th style="width:70px;">MAPCO</th><th style="width:20px;"></th><th style="width:70px;">AP</th><th style="width:20px;"></th><th style="width:80px;">GESAMT</th>';
	if($comparison)
	{ echo '<th style="width:20px;"></th><th style="width:70px;">MAPCO</th><th style="width:20px;"></th><th style="width:70px;">AP</th><th style="width:20px;"></th><th style="width:80px;">GESAMT</th>';}
	echo '</tr>';
		while (list($key, $value) = each($masterlist)) {
			if ( isset($item1_qnt[$key]) ) {$qnt1=$item1_qnt[$key]; $total1=$item1_total[$key];} else {$qnt1=0; $total1=0;}
			if ( isset($item2_qnt[$key]) ) {$qnt2=$item2_qnt[$key]; $total2=$item2_total[$key];} else {$qnt2=0; $total2=0;}
			
			if ( ($notnull && ( ($comparison==true && ($masterlist[$key]>0 || $comp_masterlist[$key]>0)) || ( $comparison==false && $masterlist[$key]>0))) || !$notnull) {

			$total3=$total1+$total2;
			echo '<tr>';
			//echo '	<td>'.$key.'</td>';
			echo '	<td>'.$item_data[$key].'</td>';
			echo '	<td style="text-align:right;">'.$qnt1.'</td>';
			echo '	<td style="text-align:right;">'.number_format($total1,2,",",".").' €</td>';
			echo '	<td style="text-align:right;">'.$qnt2.'</td>';
			echo '	<td style="text-align:right;">'.number_format($total2,2,",",".").' €</td>';
			echo '	<td style="background-color:#ddd; text-align:right;"><b>'.$masterlist[$key].'</b></td>';
			echo '	<td style="background-color:#ccc; text-align:right;"><b>'.number_format($total3,2,",",".").' €</b></td>';
			$gesamt_qnt+=$masterlist[$key];
			$gesamt_total+=$total3;
			$gesamt_qnt1+=$qnt1;
			$gesamt_total1+=$total1;
			$gesamt_qnt2+=$qnt2;
			$gesamt_total2+=$total2;
			
			if($comparison)
			{	if ( isset($comp_item1_qnt[$key]) ) {$comp_qnt1=$comp_item1_qnt[$key]; $comp_total1=$comp_item1_total[$key];} else {$comp_qnt1=0; $comp_total1=0;}
				if ( isset($comp_item2_qnt[$key]) ) {$comp_qnt2=$comp_item2_qnt[$key]; $comp_total2=$comp_item2_total[$key];} else {$comp_qnt2=0; $comp_total2=0;}
				$comp_total3=$comp_total1+$comp_total2;
				echo '<td style="text-align:right;">'.$comp_qnt1.'</td>';
				echo '<td style="text-align:right;">'.number_format($comp_total1,2,",",".").' €</td>';
				echo '<td  style="text-align:right;">'.$comp_qnt2.'</td>';
				echo '<td style="text-align:right;">'.number_format($comp_total2,2,",",".").' €</td>';
				echo '<td  style="background-color:#ddd; text-align:right;"><b>'.$comp_masterlist[$key].'</b></td>';
				echo '<td  style="background-color:#bbb; text-align:right;"><b>'.number_format($comp_total3,2,",",".").' €</b></td>';
				$comp_gesamt_qnt+=$comp_masterlist[$key];
				$comp_gesamt_total+=$comp_total3;
				$comp_gesamt_qnt1+=$comp_qnt1;
				$comp_gesamt_total1+=$comp_total1;
				$comp_gesamt_qnt2+=$comp_qnt2;
				$comp_gesamt_total2+=$comp_total2;

			}
			echo '</tr>';
		}
	}
	if (  ($comparison==true && ($gesamt_qnt>0 || $comp_gesamt_qnt>0)) || ( $comparison==false && $gesamt_qnt>0)) {
		echo '<tr style="background-color:#bbb">';
		//echo '	<td></td>';
		echo '	<td><b>Gesamt</b></td>';
		echo '	<td style="text-align:right;"><b>'.$gesamt_qnt1.'</b></td>';
		echo '	<td style="text-align:right;"><b>'.number_format($gesamt_total1,2,",",".").' €</b></td>';
		echo '	<td style="text-align:right;"><b>'.$gesamt_qnt2.'</b></td>';
		echo '	<td style="text-align:right;"><b>'.number_format($gesamt_total2,2,",",".").' €</b></td>';
		echo '	<td style="text-align:right;"><b>'.$gesamt_qnt.'</b></td>';
		echo '	<td style="text-align:right;"><b>'.number_format($gesamt_total,2,",",".").' €</b></td>';
		if($comparison)
		{
			echo '<td style="text-align:right;"><b>'.$comp_gesamt_qnt1.'</b></td>';
			echo '<td style="text-align:right;"><b>'.number_format($comp_gesamt_total1,2,",",".").' €</b></td>';
			echo '<td style="text-align:right;"><b>'.$comp_gesamt_qnt2.'</b></td>';
			echo '<td style="text-align:right;"><b>'.number_format($comp_gesamt_total2,2,",",".").' €</b></td>';
			echo '<td style="text-align:right;"><b>'.$comp_gesamt_qnt.'</b></td>';
			echo '<td style="text-align:right;"><b>'.number_format($comp_gesamt_total,2,",",".").' €</b></td>';
		}
		echo '</tr>';
	}
	echo '</table>';
		
?>