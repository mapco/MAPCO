<?php

	include("../functions/mapco_gewerblich.php");
	include("../functions/cms_t.php");

	if (isset($_POST["id_payment_select"])) 
	{
		$payment_option["id_payment"]=$_POST["id_payment_select"];
		$payment_option["id_shipping"]=$_POST["id_shipping_select"];
		$results=q("SELECT * FROM shop_shipping WHERE payment_id=".$payment_option["id_payment"]." and id_shipping=".$payment_option["id_shipping"]." LIMIT 1;", $dbshop, __FILE__, __LINE__);
		if(!(mysql_num_rows($results)>0)) $payment_option["id_shipping"]="";
	}
	else
	{
		$payment_option["id_payment"]=$_SESSION["id_payment"];
		$payment_option["id_shipping"]=$_SESSION["id_shipping"];
		if(isset($_SESSION["shipping_net"])) $payment_option["shipping_net"]=$_SESSION["shipping_net"];
		else $payment_option["shipping_net"]=0;
		if(isset($_SESSION["shipping_costs"])) $payment_option["shipping_costs"]=$_SESSION["shipping_costs"];
		else $payment_option["shipping_costs"]=0;
		if(isset($_SESSION["shipping_details"])) $payment_option["shipping_details"]=$_SESSION["shipping_details"];
		else $payment_option["shipping_details"]="";
		if(isset($_SESSION["payment_memo"])) $payment_option["payment_memo"]=$_SESSION["payment_memo"];
		else $payment_option["payment_memo"]="";
		if(isset($_SESSION["shipping_memo"])) $payment_option["shipping_memo"]=$_SESSION["shipping_memo"];
		else $payment_option["shipping_memo"]="";
	}
	if (gewerblich($_SESSION["id_user"])) $results=q("SELECT * FROM shop_payment WHERE country_id=".$_SESSION["ship_country_id"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
	else $results=q("SELECT * FROM shop_payment WHERE NOT payment = 'Rechnung' AND country_id=".$_SESSION["ship_country_id"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
	echo '<table>';
	echo '<tr>';
	if (mysql_num_rows($results)>0)
	{
		echo '<td>'.t("Zahlungsart").':</td>';
		echo '<td><select id="id_payment_select" style="width:450px;" onchange="payment_select()">';
		while($row=mysql_fetch_array($results))
		{
			if ($payment_option["id_payment"]=="")
			{
				$payment_option["id_payment"]=$row["id_payment"];
			}
			if ($row["id_payment"]==$payment_option["id_payment"])
			{
				$selected=' selected="selected"';
				$payment_option["payment_memo"]=$row["payment_memo"];
				$payment_option["shipping_details"]=$row["payment"];
			}
			else $selected='';
			echo '<option'.$selected.' value="'.$row["id_payment"].'">';
			echo $row["payment"];
			echo '</option>';
		}
		echo '		</select></td>';
		echo '</tr>';
		
		//shipping costs
		echo '<tr>';
		echo '<td>'.t("Versandart").':</td>';
		echo '<td><select id="id_shipping_select" style="width:450px;" onchange="payment_select()">';
		if (gewerblich($_SESSION["id_user"]))
		{
			if($_SESSION["rcid"]!="" and $_SESSION["rcid"]!=9999 and $_SESSION["rcid"]>0 and $payment_option["id_payment"]==9)
			{
				$results=q("SELECT * FROM shop_shipping WHERE id_shipping IN (".$_SESSION["rc_shipping"].", 20) AND payment_id=".$payment_option["id_payment"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
			}
			else
			{
				$results=q("SELECT * FROM shop_shipping WHERE payment_id=".$payment_option["id_payment"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
			}
		}
		else
		{
			if($_SESSION["rcid"]!="" and $_SESSION["rcid"]!=9999 and $_SESSION["rcid"]>0 and $payment_option["id_payment"]==9)
			{
				$results=q("SELECT * FROM shop_shipping WHERE id_shipping=".$_SESSION["rc_shipping"]." AND payment_id=".$payment_option["id_payment"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
				$_SESSION["id_shipping"]=$_SESSION["rc_shipping"];
			}
			else
			{
				$results=q("SELECT * FROM shop_shipping WHERE NOT id_shipping=20 AND payment_id=".$payment_option["id_payment"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
			}
		}
		while($row=mysql_fetch_array($results))
		{
			if ($payment_option["id_shipping"]=="") $payment_option["id_shipping"]=$row["id_shipping"];
			if ($row["id_shipping"]==$payment_option["id_shipping"])
			{
				$shipping_id = $row["id_shipping"];
				$selected=' selected="selected"';
				$payment_option["shipping_net"]=$row["price"];
				if (gewerblich($_SESSION["id_user"])) $payment_option["shipping_costs"]=$row["price"];
				else $payment_option["shipping_costs"]=((100+UST)/100)*$row["price"];
				$payment_option["shipping_details"].=', '.$row["shipping"];
				$payment_option["shipping_memo"]=$row["shipping_memo"];
			}
			else $selected='';
			if ($row["id_shipping"]==27) echo '<option'.$selected.' value="'.$row["id_shipping"].'">'.$row["shipping"].'</option>';
			else
			{
				if (gewerblich($_SESSION["id_user"])) echo '<option'.$selected.' value="'.$row["id_shipping"].'">'.$row["shipping"].' (€ '.number_format($row["price"], 2).')</option>';
				else echo '<option'.$selected.' value="'.$row["id_shipping"].'">'.$row["shipping"].' (€ '.number_format(((100+UST)/100)*$row["price"], 2).')</option>';
			}
		}
		echo '		</select></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td colspan="2">';
		if ($payment_option["payment_memo"]!="") echo '<br /><i>'.$payment_option["payment_memo"].'</i>';
		if ($payment_option["shipping_memo"]!="") echo '<br /><i>'.$payment_option["shipping_memo"].'</i>';
		echo '<input type="hidden" id="payment_shipping_net" value="'.$payment_option["shipping_net"].'" />';
		echo '<input type="hidden" id="payment_shipping_costs" value="'.$payment_option["shipping_costs"].'" />';
		echo '<input type="hidden" id="payment_shipping_details" value="'.$payment_option["shipping_details"].'" />';
		echo '<input type="hidden" id="payment_payment_memo" value="'.$payment_option["payment_memo"].'" />';
		echo '<input type="hidden" id="payment_shipping_memo" value="'.$payment_option["shipping_memo"].'" />';
		echo '</td>';
		echo '</tr>';
		echo '</table>';
	}
	else 
	{
		echo '<div colspan="4" style="color:#ff0000"; align="center">';
		echo t("Zahlungsart und Versandkosten bitte vor Bestellung schriftlich oder telefonisch klären!");
		echo '</div>';
	}

?>