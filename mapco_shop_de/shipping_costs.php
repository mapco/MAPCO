<?php
	include("config.php");
	include("templates/".TEMPLATE."/header.php");
	include("functions/mapco_gewerblich.php");
	include("templates/".TEMPLATE."/cms_leftcolumn.php");

	echo '<div id="mid_column">';
	
	//Gewerbskunde?
	$gewerblich=gewerblich($_SESSION["id_user"]);
	
	//PATH
	echo '<p><h1>'.t("Unsere preiswerten Versandkosten").'</h1></p>';
	echo '<p>';
	echo '<a href="'.PATHLANG.'online-shop/versandkosten/">'.t("Versandkosten").'</a>';
	echo ' >';
	echo '</p>';

	$query="SELECT * FROM shop_countries ORDER BY ordering;";
	$results=q($query, $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		if ($gewerblich)
		{
			$query2="SELECT * FROM shop_payment WHERE shop_id=".$_SESSION["id_shop"]." AND country_id=".$row["id_country"]." ORDER BY ordering;";
		}
		else
		{
			$query2="SELECT * FROM shop_payment WHERE shop_id=".$_SESSION["id_shop"]." AND NOT payment='Rechnung' AND country_id=".$row["id_country"]." ORDER BY ordering;";
		}
		$results2=q($query2, $dbshop, __FILE__, __LINE__);
		if(mysqli_num_rows($results2)>0)
		{
			echo '<p><span style="font-weight:bold; font-size:16px;">'.t("Versand nach").' '.t($row["country"], __FILE__, __LINE__).'</span></p>';
			while($row2=mysqli_fetch_array($results2))
			{
				$i=0;
				echo '<table class="hover">';
				echo '	<tr>';
				echo '		<th width="400px">'.t($row2["payment"], __FILE__, __LINE__).'</th>';
				if ($gewerblich)
				{
					echo '		<th>'.t("Wert").' '.t("zzgl. MwSt.", __FILE__, __LINE__).'</th>';
				}
				else
				{
					echo '		<th>'.t("Wert").' '.t("inkl. MwSt.", __FILE__, __LINE__).'</th>';
				}				
				echo '	</tr>';
				if ($gewerblich)
				{
					$query="SELECT * FROM shop_shipping WHERE payment_id=".$row2["id_payment"]." ORDER BY ordering;";
				}
				else
				{
					$query="SELECT * FROM shop_shipping WHERE NOT id_shipping=20 AND payment_id=".$row2["id_payment"]." ORDER BY ordering;";
				}
				$results3=q($query, $dbshop, __FILE__, __LINE__);
				while($row3=mysqli_fetch_array($results3))
				{				
					if ($i>0) $style ='border-top:1px solid lightgrey;';
					else $style='';
					echo '<tr>';
					echo '	<td style="padding-right:10px; '.$style.'">';
					echo '<b>'.t($row3["shipping"], __FILE__, __LINE__).'</b>';
					if ($row3["shipping_memo"]!="")
					{
						echo '<br />';
						echo $row3["shipping_memo"];
					}
					echo '</td>';
					if ($gewerblich)
					{
						echo '	<td style="'.$style.'">€ '.number_format($row3["price"], 2).'</td>';
					}
					else
					{
						echo '	<td>€ '.number_format(((100+UST)/100)*$row3["price"], 2).'</td>';
					}
					echo '</tr>';
					$i++;
				}
				echo '</table>';
			}
			echo '<br />';
		}
	}
	
/*
	if (gewerblich($_SESSION["id_user"]))
	{
		echo '	<a href="mapco_images.zip" class="cp_icon">';
		echo '		<img src="images/icons/128x128/image.png" alt="'.t("Bilderdownload").'" title="'.t("Bilderdownload").'" />';
		echo '		<br />'.t("Bilderdownload");
		echo '	</a>';
	}


	echo '	<a href="shop_user_leaflet.php" class="cp_icon">';
	echo '		<img src="images/icons/128x128/notes_edit.png" alt="Merkzettel" title="Merktzettel" />';
	echo '		<br />Merkzettel';
	echo '	</a>';
*/
	echo '</div>';
	
	
	
/*	
				echo '		<select name="id_payment" onchange="form.submit()">';
			if (gewerblich($_SESSION["id_user"])) $results=q("SELECT * FROM shop_payment WHERE country_id=".$_SESSION["ship_country_id"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
			else 
			$results=q("SELECT * FROM shop_payment WHERE NOT id_payment=5 AND country_id=".$_SESSION["ship_country_id"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
			while($row=mysqli_fetch_array($results))
			{
				if ($_SESSION["id_payment"]=="")
				{
					echo $_SESSION["id_payment"]=$row["id_payment"];
				}
				if ($row["id_payment"]==$_SESSION["id_payment"])
				{
					$selected=' selected="selected"';
					$payment_memo=$row["payment_memo"];
					$_SESSION["shipping_details"]=$row["payment"];
				}
				else $selected='';
				echo '<option'.$selected.' value="'.$row["id_payment"].'">';
				echo $row["payment"];
				echo '</option>';
			}
			echo '		</select>';
			
			//shipping costs
			echo ' &nbsp; Versandart:	<select name="id_shipping" onchange="form.submit()">';
			if (gewerblich($_SESSION["id_user"]))
			{
				$query="SELECT * FROM shop_shipping WHERE payment_id=".$_SESSION["id_payment"]." ORDER BY ordering;";
				$results=q($query, $dbshop, __FILE__, __LINE__);
			}
			else
			{
				$query="SELECT * FROM shop_shipping WHERE NOT id_shipping=20 AND payment_id=".$_SESSION["id_payment"]." ORDER BY ordering;";
				$results=q($query, $dbshop, __FILE__, __LINE__);
			}
			while($row=mysqli_fetch_array($results))
			{
				if ($_SESSION["id_shipping"]=="") $_SESSION["id_shipping"]=$row["id_shipping"];
				if ($row["id_shipping"]==$_SESSION["id_shipping"])
				{
					$shipping_id = $row["id_shipping"];
					$selected=' selected="selected"';
					$_SESSION["shipping_net"]=$row["price"];
					if (gewerblich($_SESSION["id_user"])) $_SESSION["shipping_costs"]=$row["price"];
					else $_SESSION["shipping_costs"]=((100+UST)/100)*$row["price"];
					$_SESSION["shipping_details"].=', '.$row["shipping"];
					$total+=$_SESSION["shipping_costs"];
					$shipping_memo=$row["shipping_memo"];
				}
				else $selected='';
				if ($row["id_shipping"]==27) echo '<option'.$selected.' value="'.$row["id_shipping"].'">'.$row["shipping"].'</option>';
				else
				{
					if (gewerblich($_SESSION["id_user"])) echo '<option'.$selected.' value="'.$row["id_shipping"].'">'.$row["shipping"].' (€ '.number_format($row["price"], 2).')</option>';
					else echo '<option'.$selected.' value="'.$row["id_shipping"].'">'.$row["shipping"].' (€ '.number_format(((100+UST)/100)*$row["price"], 2).')</option>';
				}
			}
			echo '		</select>';
			if ($payment_memo!="") echo '<br /><i>'.$payment_memo.'</i>';
			if ($shipping_memo!="") echo '<br /><i>'.$shipping_memo.'</i>';
			echo '	</td>';
			echo '	<td>';
			if ($shipping_id != 27)
				{
				echo '€ '.number_format($_SESSION["shipping_costs"], 2);
				}
			echo '</td>';
			echo '</tr>';

*/	
	
	

	include("templates/".TEMPLATE."/cms_rightcolumn.php");
	include("templates/".TEMPLATE."/footer.php");
?>