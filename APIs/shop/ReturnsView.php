<?php

	//AUSLESEN TABELLENZUORDNUNGEN
	$results=q("SELECT * FROM shop_returns_platform;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) ) {
		$r_platform[$row["ID"]]=$row["platform"];
	}
	
	$results=q("SELECT * FROM shop_returns_rAction;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) ) {
		$r_rAction[$row["ID"]]=$row["rAction"];
	}
	
	$results=q("SELECT * FROM shop_returns_rReason;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) ) {
		$r_rReason[$row["ID"]]=$row["rReason"];
	}


	$sql="SELECT * FROM shop_returns ORDER BY date_order DESC;";
	$results=q($sql, $dbshop, __FILE__, __LINE__);
	
	$i=0;
	while ($row=mysqli_fetch_array($results))
	{
		$returns[$i]=$row;
		$i++;
	}
	
	//FILTER STATUS
	if ($_POST["returnsViewFilterState"]=="open" || $_POST["returnsViewFilterState"]=="closed")
	{
		$entries=$returns;
		$returns=array();
		$j=0;
		for ($i=0; $i<sizeof($entries); $i++)
		{
			if ($entries[$i]["state"]==$_POST["returnsViewFilterState"])
			{
				$returns[$j]=$entries[$i];
				$j++;
			}
		}
	}
	//FILTER PLATFORM
	if ($_POST["returnsViewFilterPlatform"]!="all")
	{
		$entries=$returns;
		$returns=array();
		$j=0;
		for ($i=0; $i<sizeof($entries); $i++)
		{
			if ($entries[$i]["platform"]==$_POST["returnsViewFilterPlatform"])
			{
				$returns[$j]=$entries[$i];
				$j++;
			}
		}
	}
	//FILTER TYPE
	if ($_POST["returnsViewFilterType"]=="return" || $_POST["returnsViewFilterType"]=="exchange")
	{
		$entries=$returns;
		$returns=array();
		$j=0;
		for ($i=0; $i<sizeof($entries); $i++)
		{
			if ($entries[$i]["rAction"]==$_POST["returnsViewFilterType"])
			{
				$returns[$j]=$entries[$i];
				$j++;
			}
		}
	}

	//FILTER rREASON

	if ($_POST["returnsViewFilterReason"]!="all")
	{
		$entries=$returns;
		$returns=array();
		$j=0;
		for ($i=0; $i<sizeof($entries); $i++)
		{
			if ($entries[$i]["rReason"]==$_POST["returnsViewFilterReason"])
			{
				$returns[$j]=$entries[$i];
				$j++;
			}
		}
	}

	
	//FILTER DATE
	if ($_POST["returnsViewFilterDateFrom"]!="" || $_POST["returnsViewFilterDateTo"]!="")
	{
		$from=mktime(0,0,0,substr($_POST["returnsViewFilterDateFrom"],3,2), substr($_POST["returnsViewFilterDateFrom"],0,2), substr($_POST["returnsViewFilterDateFrom"],6));
		$to=mktime(0,0,0,substr($_POST["returnsViewFilterDateTo"],3,2), substr($_POST["returnsViewFilterDateTo"],0,2), substr($_POST["returnsViewFilterDateTo"],6))+86399;

		if ($from<$to)
		{
			$entries=$returns;
			$returns=array();
			$j=0;
			for ($i=0; $i<sizeof($entries); $i++)
			{
				if ($entries[$i]["date_order"]>=$from && $entries[$i]["date_order"]<=$to)
				{
					$returns[$j]=$entries[$i];
					$j++;
				}
			}
		}
	}
	
	
		
//	echo '<div style="border-style:solid; border-color:#666; border-width:1px;">';
	echo '	<div style="width:250px; float:left" onmouseover="style.backgroundColor=\'#B0C4DE\'" onmouseout="style.backgroundColor=\'#ffffff\'">';
	echo '		Anzeige nach Status';
	echo '		<select name="ViewFilterState" id="returns_ViewFilterState" size="1" onchange="setViewFilterState(); view();">';
	echo '			<option value="all">Alle</option>';
		$results3=q("SELECT * FROM shop_returns_state;", $dbshop, __FILE__, __LINE__);
		while( $row3=mysqli_fetch_array($results3) )
		{
			if ( $row3["ID"]==$_POST["returnsViewFilterState"] ) { $selected=' selected="selected"'; } else { $selected=""; }
			echo '<option'.$selected.' value="'.$row3["ID"].'">'.$row3["state"].'</option>';
		}
	echo '		</select>';
	echo '	</div>';
	echo '	<div style="width:250px; float:left;" onmouseover="style.backgroundColor=\'#B0C4DE\'" onmouseout="style.backgroundColor=\'#ffffff\'">';
	echo '		Plattform';
	echo '		<select name="ViewFilterPlatform" id="returns_ViewFilterPlatform" size="1" onchange="returnsViewFilterPlatform=this.value; view();">';
	echo '			<option value="all">Alle</option>';
		$results3=q("SELECT * FROM shop_returns_platform;", $dbshop, __FILE__, __LINE__);
		while( $row3=mysqli_fetch_array($results3) )
		{
			if ( $row3["ID"]==$_POST["returnsViewFilterPlatform"] ) { $selected=' selected="selected"'; } else { $selected=""; }
			echo '<option'.$selected.' value="'.$row3["ID"].'">'.$row3["platform"].'</option>';
		}
	echo '		</select>';
	echo '	</div>';

	echo '	<div style="width:300px; float:left;" onmouseover="style.backgroundColor=\'#B0C4DE\'" onmouseout="style.backgroundColor=\'#ffffff\'">';
	echo '		Anzeige nach Rückgabetyp';
	echo '		<select name="ViewFilterType" id="returns_ViewFilterType" size="1" onchange="returnsViewFilterType=this.value; view();">';
	echo '			<option value="all">Alle</option>';
		$results3=q("SELECT * FROM shop_returns_rAction;", $dbshop, __FILE__, __LINE__);
		while( $row3=mysqli_fetch_array($results3) )
		{
			if ( $row3["ID"]==$_POST["returnsViewFilterType"] ) { $selected=' selected="selected"'; } else { $selected=""; }
			echo '<option'.$selected.' value="'.$row3["ID"].'">'.$row3["rAction"].'</option>';
		}
	echo '		</select>';
	echo '	</div>';
	echo '	<div style="width:350px; float:left" onmouseover="style.backgroundColor=\'#B0C4DE\'" onmouseout="style.backgroundColor=\'#ffffff\'">';
	echo '		Anzeige nach Rückgabegrund';
	echo '		<select name="ViewFilterReason" id="returns_ViewFilterReason" size="1" onchange="returnsViewFilterReason=this.value; view();">';
	echo '			<option value="all">Alle</option>';
		$results3=q("SELECT * FROM shop_returns_rReason;", $dbshop, __FILE__, __LINE__);
		while( $row3=mysqli_fetch_array($results3) )
		{
			if ( $row3["ID"]==$_POST["returnsViewFilterReason"] ) { $selected=' selected="selected"'; } else { $selected=""; }
			echo '<option'.$selected.' value="'.$row3["ID"].'">'.$row3["rReason"].'</option>';
		}
	echo '		</select>';
	echo '	</div>';
	
	echo '	<div style="width:400px; float:left" onmouseover="style.backgroundColor=\'#B0C4DE\'" onmouseout="style.backgroundColor=\'#ffffff\'">';
	echo '		Kaufdatum von&nbsp;<input type="text" name="from" id="returnsViewFilterDateFrom" size="10" value="'.$_POST["returnsViewFilterDateFrom"].'" onchange="returnsViewFilterDateFrom=this.value;" />';
	echo '		&nbsp;bis&nbsp;<input type="text" name="to" id="returnsViewFilterDateTo" size="10" value="'.$_POST["returnsViewFilterDateTo"].'" onchange="returnsViewFilterDateTo=this.value;" />';
	echo '		<button name="btn1" id="filter_date" onclick="view()">anzeigen</button>';
	echo '	</div>';
//	echo '</div>';
	
echo '<script type="text/javascript">
					$(function()
					{
						$( "#returnsViewFilterDateFrom" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
					});

					$(function()
					{
						$( "#returnsViewFilterDateTo" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1 });
					});
</script>';	

	echo '<br style="clear:both;" />';
	echo '<div id="view" style="display:inline; float:left;">';
	echo '</div>';

		// VIEW RÜCKGABEN
		
		echo '<table>';
		echo '<tr>';
			echo '<th> Status </th>';
			echo '<th> Käufer Name </th>';
			echo '<th> Käufer ID </th>';
			echo '<th> Plattform </th>';
			echo '<th> Kaufdatum </th>';
			echo '<th> Artikel </th>';
	//		echo '<th> Artikelgruppe </th>';
			echo '<th> Anzahl </th>';
			echo '<th> Vorgang </th>';
			echo '<th> Grund </th>';
			echo '<th> Gemeldet </th>';
			echo '<th> Erhalten </th>';
			echo '<th> Erstattung </th>';
	//		echo '<th> Gutschrift IDIMS </th>';
			echo '<th> Provisions-gutschrift </th>';
			echo '<th> <img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/32x32/page_add.png" alt="Neue Rückgabe / Neuen Umtausch eingeben" title="Neue Rückgabe / Neuen Umtausch eingeben" onclick="ReturnAdd()" /></th>';
		echo '</tr>';

	if (sizeof($returns)>0)
	{
		// DATEN AUS DATENBANK ZUR ANZEIGE
		for ($i=0; $i<sizeof($returns); $i++)
		{
			
		//DATUM VORBEREITUNG ZUR ÜBERGABE
			$date_order=$date_announced=$date_return=$date_refund=$date_refund_reshipment=$date_r_IDIMS=$date_case_closed=$date_exchange_sent="";

			if ($returns[$i]["date_order"]!=0) { $date_order = date("d.m.Y",$returns[$i]["date_order"]);}
			if ($returns[$i]["date_announced"]!=0) { $date_announced = date("d.m.Y",$returns[$i]["date_announced"]);}
			if ($returns[$i]["date_return"]!=0) { $date_return = date("d.m.Y",$returns[$i]["date_return"]);}
			if ($returns[$i]["date_refund"]!=0) { $date_refund = date("d.m.Y",$returns[$i]["date_refund"]);}
			if ($returns[$i]["date_refund_reshipment"]!=0) { $date_refund_reshipment = date("d.m.Y",$returns[$i]["date_refund_reshipment"]);}
			//if ($row["date_r_IDIMS"]!=0) { $date_r_IDIMS = date("d.m.Y",$row["date_r_IDIMS"]);}
			$date_demandEbayClosing1="";
			if ($returns[$i]["ebay_demand_closing1"]!=0) { $date_demandEbayClosing1 = date("d.m.Y",$returns[$i]["ebay_demand_closing1"]);}
			$date_demandEbayClosing2="";
			if ($returns[$i]["ebay_demand_closing2"]!=0) { $date_demandEbayClosing2 = date("d.m.Y",$returns[$i]["ebay_demand_closing2"]);}
			if ($returns[$i]["date_exchange_sent"]!=0) { $date_exchange_sent = date("d.m.Y",$returns[$i]["date_exchange_sent"]);}
			echo '<tr onmouseover="style.backgroundColor=\'#B0C4DE\'" onmouseout="style.backgroundColor=\'#ffffff\'">'; 
               if ($returns[$i]["state"]=="open" ) 
			   { echo '<td><img style="margin:0px 5px 0px 0px; border:0; padding:0; float:right;" src="'.PATH.'images/icons/24x24/warning.png" alt="Fall offen" title="Fall offen" /></td>';
			   }
               if ($returns[$i]["state"]=="closed" ) 
			   { echo '<td><img style="margin:0px 5px 0px 0px; border:0; padding:0; float:right;" src="'.PATH.'images/icons/24x24/accept.png" alt="Fall geschlossen" title="Fall geschlossen" /></td>';
			   }			   
				echo '<td>'.$returns[$i]["buyername"].'</td>';
				echo '<td>'.$returns[$i]["userid"].'</td>';
				echo '<td>'.$r_platform[$returns[$i]["platform"]].'</td>';
				echo '<td>'.date("d.m.Y", $returns[$i]["date_order"]).'</td>';
				echo '<td>'.$returns[$i]["MPU"].'</td>';
			//	echo '<td>'.$returns["articlegroup"][$i].'</td>';
				echo '<td>'.$returns[$i]["quantity"].'</td>';
				echo '<td>'.$r_rAction[$returns[$i]["rAction"]].'</td>';
				if ( !isset($r_rReason[$returns[$i]["rReason"]]) ) $r_rReason[$returns[$i]["rReason"]]='';
				echo '<td>'.$r_rReason[$returns[$i]["rReason"]].'</td>';

				if ($returns[$i]["date_announced"]<>0) {echo '<td>'.date("d.m.Y", $returns[$i]["date_announced"]).'</td>';} else { echo '<td> </td>';}
				if ($returns[$i]["date_return"]<>0) {echo '<td>'.date("d.m.Y", $returns[$i]["date_return"]).'</td>';} else { echo '<td> </td>';}
				if ($returns[$i]["date_refund"]<>0) {echo '<td>'.date("d.m.Y", $returns[$i]["date_refund"]).'</td>';} else { echo '<td> </td>';}
				//if ($returns["date_r_IDIMS"][$i]<>0) {echo '<td>'.date("d.m.Y", $returns["date_r_IDIMS"][$i]).'</td>';} else { echo '<td> </td>';}

				if ($returns[$i]["ebay_fee_refund"]=="1") {
					if ($returns[$i]["platform"]=="ebay_AP" || $returns[$i]["platform"]=="ebay_MAPCO")
					{
						echo '<td><img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/accept.png" alt="Ebay Provision gutgeschrieben/Rückgabe wurde eingeleitet" title="Ebay Provision gutgeschrieben/Rückgabe wurde eingeleitet"></td>';
					}
					else
					{
						echo '<td style="background-color:#c8c8c8"></td>';
					}
				}
				else {
					if ($returns[$i]["platform"]=="ebay_AP" || $returns[$i]["platform"]=="ebay_MAPCO")
					{
						echo '<td><img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/info.png" alt="Ebay Provision noch nicht zurückbekommen/Rückgabe wurde noch nichteingeleitet" title="Ebay Provision noch nicht zurückbekommen/Rückgabe wurde noch nichteingeleitet"></td>';
					}
					else
					{
						echo '<td style="background-color:#c8c8c8"></td>';
					}
				}

				
				echo '<td>';
				//for new version
//				echo '<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/page_search.png" alt="Fall aufrufen/Details" title="Fall aufrufen/Details" onclick="ReturnUpdate('.$row["id"].');" />';
				//ie bugfix
				$returns[$i]["rReason_detail"]=str_replace("\n", "", $returns[$i]["rReason_detail"]);
				$returns[$i]["rReason_detail"]=str_replace("\r", "", $returns[$i]["rReason_detail"]);
				//$row["rReason_detail"]=htmlentities($row["rReason_detail"], "ISO8859-1");
				$returns[$i]["rReason_detail"]=htmlentities($returns[$i]["rReason_detail"]);
				echo '<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/page_search.png" alt="Fall aufrufen/Details" title="Fall aufrufen/Details" onclick="ReturnUpdate( { id:\''.addslashes($returns[$i]["id"]).'\', state:\''.addslashes($returns[$i]["state"]).'\', platform:\''.addslashes($returns[$i]["platform"]).'\', userid:\''.$returns[$i]["userid"].'\', buyerName:\''.addslashes($returns[$i]["buyername"]).'\', MPU:\''.addslashes($returns[$i]["MPU"]).'\', quantity:\''.$returns[$i]["quantity"].'\', transactionID:\''.$returns[$i]["transactionID"].'\', invoiceID:\''.$returns[$i]["invoiceID"].'\', rAction:\''.$returns[$i]["rAction"].'\', rReason:\''.$returns[$i]["rReason"].'\', rReason_detail:\''.$returns[$i]["rReason_detail"].'\', exchange_MPU:\''.$returns[$i]["exchange_MPU"].'\', exchange_quantity:\''.$returns[$i]["exchange_quantity"].'\', date_exchange_sent:\''.$date_exchange_sent.'\', date_order:\''.$date_order.'\', date_announced:\''.$date_announced.'\', date_return:\''.$date_return.'\', date_refund:\''.$date_refund.'\', date_refund_reshipment:\''.$date_refund_reshipment.'\', date_demandEbayClosing1:\''.$date_demandEbayClosing1.'\', date_demandEbayClosing2:\''.$date_demandEbayClosing2.'\', ebayFeeRefundOK:\''.$returns[$i]["ebay_fee_refund"].'\', refund:\''.number_format($returns[$i]["refund"], 2, ',', '').'\', refund_reshipment:\''.number_format($returns[$i]["refund_reshipment"], 2, ',', '').'\'} );" />';
			echo '	</td>';
			echo '</tr>';
			
		} // WHILE ROW
		
	
	} // IF MYSQLI_AFFECTED_ROWS
		echo '</table>';
		
?>