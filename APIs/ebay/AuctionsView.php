<?php

	//VIEW
		echo '<ul id="ebay_accounts" class="orderlist" style="width:250px;">';
		echo '	<li class="header" id="ebay_accounts_header" style="width:238px;">';
		echo '		<img src="'.PATH.'images/icons/24x24/add.png" alt="Neuen eBay-Account anlegen" title="Neuen eBay-Account anlegen" onclick="account_add();" />';
		echo '		Accounts';
		echo '	</li>';
		$results=q("SELECT * FROM ebay_accounts ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
		while($row=mysqli_fetch_array($results))
		{
			echo '<li id="'.$row["id_account"].'" style="width:238px;">';
			if ( $_POST["id_account"]==$row["id_account"] ) $style=' style="font-weight:bold;"'; else $style='';
			if ($_SESSION["userrole_id"]==1)
			{
				echo '	<img src="../images/icons/24x24/page_edit.png" alt="Einstellungen" title="Einstellungen" onclick="account_settings('.$row["id_account"].');" />';
				echo '	<img src="'.PATH.'images/icons/24x24/info.png" alt="Account-Informationen" title="Account-Informationen" onclick="account_info('.$row["id_account"].');" />';
				echo '	<img src="'.PATH.'images/icons/24x24/remove.png" alt="Account löschen" title="Account löschen" onclick="account_remove('.$row["id_account"].');" />';
				echo '	<img src="'.PATH.'images/icons/24x24/edit.png" alt="Account editieren" title="Account editieren" onclick="account_edit('.$row["id_account"].', \''.$row["title"].'\', \''.$row["description"].'\', \''.$row["language_id"].'\', \''.$row["SiteID"].'\', \''.$row["active"].'\', \''.$row["production"].'\', \''.$row["devID"].'\', \''.$row["devID_sandbox"].'\', \''.$row["appID"].'\', \''.$row["appID_sandbox"].'\', \''.$row["certID"].'\', \''.$row["certID_sandbox"].'\', \''.$row["token"].'\', \''.$row["token_sandbox"].'\', \''.$row["DispatchTimeMax"].'\', \''.$row["PaymentMethods"].'\', \''.$row["PayPalEmailAddress"].'\', \''.$row["PostalCode"].'\', \''.$row["pricelist"].'\', \''.$row["id_imageformat"].'\');" />';
			}
			echo '	<a'.$style.' href="javascript:account_select('.$row["id_account"].');">'.$row["title"].'</a>';
			echo '	<br /><i>'.$row["description"].'</i>';
			echo '</li>';
		}
		echo '</ul>';

		if ($_POST["id_account"]>0)
		{
			echo '<div style="float:left;">';

			//SELECTION
			$artgr=array();
			$artgr_title=array();
			$results=q("SELECT * FROM `cms_menuitems` WHERE menu_id=5 AND menuitem_id>0 ORDER BY title;", $dbweb, __FILE__, __LINE__);
			echo '<table style="width:600px;">';
			echo '	<tr><th colspan="2">Suchfunktion</th></tr>';
			echo '	<tr>';
			echo '		<td>Artikelgruppe</td>';
			echo '		<td>';
			echo '			<select id="id_menuitem" onchange="view();">';
			while($row=mysqli_fetch_array($results))
			{
				if ( $_POST["id_menuitem"]=="" ) $_POST["id_menuitem"]=$row["id_menuitem"];
				if ($_POST["id_menuitem"]==$row["id_menuitem"]) $selected=' selected="selected"'; else $selected='';
				echo '<option'.$selected.' value="'.$row["id_menuitem"].'">'.$row["title"].'</option>';
			}
			echo '			</select>';
			echo '		</td>';
			echo '	</tr>';
			echo '	<tr>';
			echo '		<td>Lieferstatus</td>';
			echo '		<td>';
			echo '			<select id="deliverystatus" onchange="view()">';
			if ($_POST["deliverystatus"]==4) $selected=' selected="selected"'; else $selected='';
			echo '				<option'.$selected.' value="4">alle anzeigen</option>';
			if ($_POST["deliverystatus"]==1) $selected=' selected="selected"'; else $selected='';
			echo '				<option'.$selected.' value="1">nur sofort lieferbare anzeigen</option>';
			if ($_POST["deliverystatus"]==2) $selected=' selected="selected"'; else $selected='';
			echo '				<option'.$selected.' value="2">nur z.Z. nicht lieferbare anzeigen</option>';
			if ($_POST["deliverystatus"]==0) $selected=' selected="selected"'; else $selected='';
			echo '				<option'.$selected.' value="0">nur nicht lieferbare anzeigen</option>';
			echo '			</select>';
			echo '		</td>';
			echo '	</tr>';
			echo '	<tr>';
			echo '		<td>Abbildungen</td>';
			echo '		<td>';
			echo '			<select id="fotostatus" onchange="view()">';
			if ($_POST["fotostatus"]==0) $selected=' selected="selected"'; else $selected='';
			echo '				<option'.$selected.' value="0">Alle anzeigen</option>';
			if ($_POST["fotostatus"]==1) $selected=' selected="selected"'; else $selected='';
			echo '				<option'.$selected.' value="1">nur mit Fotos anzeigen</option>';
			if ($_POST["fotostatus"]==2) $selected=' selected="selected"'; else $selected='';
			echo '				<option'.$selected.' value="2">nur ohne Fotos anzeigen</option>';
			echo '			</select>';
			echo '		</td>';
			echo '	</tr>';
			echo '	<tr>';
			echo '		<td>Suchtext</td>';
			echo '		<td>';
			echo '			<input id="needle" type="text" value="'.$_POST["needle"].'" />';
			echo '			<input type="button" value="Suchen" onclick="view();" />';
			echo '		</td>';
			echo '	</tr>';
			echo '	<tr>';
			echo '		<td colspan="2">';
			echo '			<input type="button" onclick="items_submit_options();" value="Zu eBay übertragen" />';
			echo '		</td>';
			echo '	</tr>';
			echo '</table>';
			if( $_POST["id_menuitem"]>0 )
			{
				$items=array();
				$i=0;
				$results=q("SELECT * FROM shop_items WHERE menuitem_id=".$_POST["id_menuitem"]." ORDER BY MPN;", $dbshop, __FILE__, __LINE__);
				while( $row=mysqli_fetch_array($results) )
				{
					$items[$i]=$row;
					$results2=q("SELECT * FROM shop_items_de WHERE id_item=".$items[$i]["id_item"].";", $dbshop, __FILE__, __LINE__);
					$row2=mysqli_fetch_array($results2);
					$items[$i]["title"]=$row2["title"];
					$results2=q("SELECT * FROM lager WHERE ArtNr='".$items[$i]["MPN"]."';", $dbshop, __FILE__, __LINE__);
					$row2=mysqli_fetch_array($results2);
					$items[$i]["delivery"]=$row2["Bestand"];
					$results2=q("SELECT * FROM cms_articles_images WHERE article_id=".$items[$i]["article_id"]." AND imageformat_id=8;", $dbweb, __FILE__, __LINE__);
					$items[$i]["imgcount"]=mysqli_num_rows($results2);
					$i++;
				}
				//show results
				if ( sizeof($items)==0 ) echo '<p>Keine Treffer.</p>';
				else
				{
					echo '<form name="itemform" method="post" action="backend_shop_items_export.php">';
					echo '<table class="hover" style="width:600px;">';
					echo '	<tr>';
					echo '		<th><input id="selectall" type="checkbox" onclick="checkAll();" /></th>';
					echo '		<th>Nr.</th>';
					echo '		<th>Titel</th>';
					echo '		<th>Lieferstatus</th>';
					echo '		<th>Bilder</th>';
					echo '		<th>Auktionen</th>';
					echo '	</tr>';
					$nr=0;
					for($i=0; $i<sizeof($items); $i++)
					{
						if ($_POST["needle"]!="" and strpos($items[$i]["title"], $_POST["needle"]) === false) {}
						elseif ($_POST["deliverystatus"]!=4 and $_POST["deliverystatus"]!=$items[$i]["delivery"]) {}
						elseif ($_POST["fotostatus"]==1 and $items[$i]["imgcount"]==0) {}
						elseif ($_POST["fotostatus"]==2 and $items[$i]["imgcount"]>0) {}
						else
						{
							echo '<tr>';
							//checkbox
							echo '	<td><input type="checkbox" name="item_id[]" value="'.$items[$i]["id_item"].'" /></td>';
							//counter
							$nr++;
							echo '	<td>'.$nr.'</td>';
							//title
							echo '	<td>'.$items[$i]["title"].'</td>';
							//delivery status
							$results=q("SELECT * FROM lager WHERE ArtNr='".$items[$i]["MPN"]."';", $dbshop, __FILE__, __LINE__);
							$row=mysqli_fetch_array($results);
							if ( $row["Bestand"]==1 ) $deliverystatus='<span style="color:#008000;">sofort lieferbar</span>';
							elseif ( $row["Bestand"]==2 ) $deliverystatus='<span style="color:#000080;">Liefertermin auf Anfrage</span>';
							else $deliverystatus='<span style="color:#b30000;">z.Z. nicht lieferbar</span>';
							echo '<td>'.$deliverystatus.'</td>';
							//images
							echo '<td>'.$items[$i]["imgcount"].'</td>';
							//ebay products
							echo '<td><a href="javascript:ebay_auctions('.$_POST["id_account"].', '.$items[$i]["id_item"].');">Auktionen anzeigen</a></td>';
							echo '</tr>';
						}
					}
					echo '</table>';
					echo '</form>';
				}
			} //end if( $_POST["id_menuitem"]>0 )
		} //end if ($_POST["id_account"]>0)
		echo '</div>';

?>