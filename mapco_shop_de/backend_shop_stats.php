<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_shop_index.php">Online-Shop</a>';
	echo ' > <a href="backend_shop_orders.php">Bestellungen</a>';
	echo ' > Bestellung Nr. '.$_GET["id_order"];
	echo '</p>';

	echo '<h1>Statistiken</h1>';
	//Auswahl Zeitraum
	echo '<form method="post">';
	echo 'Zeitraum: <select name="timeframe" onchange="form.submit()">';
	
	if ($_POST["timeframe"]=="heute") $selected=' selected="selected"'; else $selected='';
	echo '	<option'.$selected.'>heute</option>';
	
	if ($_POST["timeframe"]=="gestern") $selected=' selected="selected"'; else $selected='';
	echo '	<option'.$selected.'>gestern</option>';
	
	if ($_POST["timeframe"]=="vorgestern") $selected=' selected="selected"'; else $selected='';
	echo '	<option'.$selected.'>vorgestern</option>';
	
	if ($_POST["timeframe"]=="diese Woche") $selected=' selected="selected"'; else $selected='';
	echo '	<option'.$selected.'>diese Woche</option>';
	
	echo '</select>';
	
	//Direkteingabe Zeitraum
	if ($_POST["tfday_from"]=="")
	{
		$_POST["tfday_from"]=date("d", time());
		$_POST["tfmon_from"]=date("m", time());
		$_POST["tfyear_from"]=date("Y", time());
		$_POST["tfday_to"]=date("d", time());
		$_POST["tfmon_to"]=date("m", time());
		$_POST["tfyear_to"]=date("Y", time());
	}
	echo '<br /><br />Direkteingabe: ';
	echo '<input type="text" name="tfday_from" value="'.$_POST["tfday_from"].'" style="width:30px;" /> . ';
	echo '<input type="text" name="tfmon_from" value="'.$_POST["tfmon_from"].'" style="width:30px;" /> . ';
	echo '<input type="text" name="tfyear_from" value="'.$_POST["tfyear_from"].'" style="width:60px;" /> - ';
	echo '<input type="text" name="tfday_to" value="'.$_POST["tfday_to"].'" style="width:30px;" /> . ';
	echo '<input type="text" name="tfmon_to" value="'.$_POST["tfmon_to"].'" style="width:30px;" /> . ';
	echo '<input type="text" name="tfyear_to" value="'.$_POST["tfyear_to"].'" style="width:60px;" /> ';
	echo '<input type="submit" name="tfbutton" value="'.t("OK").'" /> ';
	
	echo '</form>';
	echo '<hr />';


	if (isset($_POST["tfbutton"]))
	{
		$nowtime=getdate(time()-24*3600);
		$starttime=mktime(0, 0, 0, $_POST["tfmon_from"], $_POST["tfday_from"], $_POST["tfyear_from"]);
		$endtime=mktime(23, 59, 59, $_POST["tfmon_to"], $_POST["tfday_to"], $_POST["tfyear_to"]);
	}
	elseif ($_POST["timeframe"]=="gestern")
	{
		$nowtime=getdate(time()-24*3600);
		$starttime=mktime(0,0,0,$nowtime["mon"], $nowtime["mday"], $nowtime["year"]);
		$endtime=mktime(23,59,59,$nowtime["mon"], $nowtime["mday"], $nowtime["year"]);
	}
	elseif ($_POST["timeframe"]=="vorgestern")
	{
		$nowtime=getdate(time()-48*3600);
		$starttime=mktime(0,0,0,$nowtime["mon"], $nowtime["mday"], $nowtime["year"]);
		$endtime=mktime(23,59,59,$nowtime["mon"], $nowtime["mday"], $nowtime["year"]);
	}
	elseif ($_POST["timeframe"]=="diese Woche")
	{
		$nowtime=getdate(time());
		$starttime=mktime(0,0,0,$nowtime["mon"], $nowtime["mday"]-$nowtime["wday"]+1, $nowtime["year"]);
		$endtime=mktime(23,59,59,$nowtime["mon"], $nowtime["mday"]+7-$nowtime["wday"], $nowtime["year"]);
	}
	else
	{
		$nowtime=getdate(time());
		$starttime=mktime(0,0,0,$nowtime["mon"], $nowtime["mday"], $nowtime["year"]);
		$endtime=mktime(23,59,59,$nowtime["mon"], $nowtime["mday"], $nowtime["year"]);
	}


	$i=0;
	$average=0;
	$count=0;
	echo '<table>';
	$results=q("SELECT * FROM cms_users WHERE firstmod>=".$starttime." AND firstmod<=".$endtime.";", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$results2=q("SELECT * FROM shop_orders WHERE customer_id=".$row["id_user"]." ORDER BY firstmod;", $dbshop, __FILE__, __LINE__);
		$orders=mysqli_num_rows($results2);
		$row2=mysqli_fetch_array($results2);
		$id_order=$row2["id_order"];

		if ($orders>0)
		{
			$count++;
			$total=0;
			$results3=q("SELECT * FROM shop_orders_items WHERE order_id=".$id_order.";", $dbshop, __FILE__, __LINE__);
			while($row3=mysqli_fetch_array($results3)) $total+=$row3["price"];
			$average+=$total;
			echo '<tr>';
			$i++;
			echo '<td>'.$i.'</td>';
			echo '<td>'.$row["id_user"].'</td>';
			echo '<td>'.$row["username"].'</td>';
			echo '<td>'.$row["usermail"].'</td>';
//			echo '<td>'.$orders.'</td>';
			echo '<td>€ '.number_format($total, 2).'</td>';
			echo '<td>'.$row2["partner_id"].'</td>';
			echo '</tr>';
		}
	}
	echo '<tr><td style="font-weight:bold;" colspan="3">Durchschnitt Erstbestellung</td><td style="font-weight:bold;">€ '.number_format($average/$count, 2).'</td></tr>';
	echo '</table>';

	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>