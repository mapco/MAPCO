<?php

	//GET CUSTOMER DATA
	$res_customer=q("SELECT * FROM crm_customers; ", $dbweb, __FILE__, __LINE__);
	while ($row_customer=mysql_fetch_array($res_customer))
	{
		if ($row_customer["company"]=="")
		{
			$crm_customer_name[$row_customer["id_crm_customer"]]=$row_customer["name"];
		}
		else
		{
			$crm_customer_name[$row_customer["id_crm_customer"]]=$row_customer["company"]." ".$row_customer["name"];
		}
	
		$crm_customer_street1[$row_customer["id_crm_customer"]]=$row_customer["street1"];
		$crm_customer_street2[$row_customer["id_crm_customer"]]=$row_customer["street2"];
		$crm_customer_zip[$row_customer["id_crm_customer"]]=$row_customer["zip"];
		$crm_customer_city[$row_customer["id_crm_customer"]]=$row_customer["city"];
		$crm_customer_country[$row_customer["id_crm_customer"]]=$row_customer["country"];
	}
	
	//GET LIST OWNER
	$notes=array();
	$communications=array();
	$reminder=array();
	$res_user=q("SELECT firstmod_user FROM crm_costumer_lists WHERE id_list = ".$_POST["id_list"].";", $dbweb, __FILE__, __LINE__);
	while ($row_user=mysql_fetch_array($res_user))
	{
		$list_owner=$row_user["firstmod_user"];
	}
	
	//GET NOTES
	$res_notes=q("SELECT customer_id FROM crm_customer_notes;", $dbweb, __FILE__, __LINE__);
	while ($row_notes=mysql_fetch_array($res_notes))
	{
		if (isset($notes[$row_notes["customer_id"]]))
		{
			$notes[$row_notes["customer_id"]]++;
		}
		else 
		{
			$notes[$row_notes["customer_id"]]=1;
		}
	}
	//GET COMMUNICATIONS
	$res_comm=q("SELECT * FROM crm_communications;", $dbweb, __FILE__, __LINE__);
	while ($row_comm=mysql_fetch_array($res_comm))
	{
		if (isset($communications[$row_comm["customer_id"]]))
		{
			$communications[$row_comm["customer_id"]]++;
		}
		else 
		{
			$communications[$row_comm["customer_id"]]=1;
		}
		if ($row_comm["reminder"]!=0)
		{
			if (isset($reminder[$row_comm["customer_id"]]))
			{
				if ($reminder[$row_comm["customer_id"]]<=$row_comm["reminder"]) $reminder[$row_comm["customer_id"]]=$row_comm["reminder"];
			}
			else
			{
				$reminder[$row_comm["customer_id"]]=$row_comm["reminder"];
			}
		}
	}

	$res=q("SELECT * FROM crm_costumer_lists_customers WHERE list_id = ".$_POST["id_list"].";", $dbweb, __FILE__, __LINE__);
	$counter=0;
	
	if (mysql_num_rows($res)>0)
	{
	
		echo '<form name="customer_list">';
		echo '<table>';
		echo '<tr>';
		echo '	<th><input type="checkbox" name="customer_select_all" id="customer_select_all" onclick="checkAll();"></th>';
		echo '	<th></th>';
		echo '	<th style="width:400px">Kunde</th>';
		echo '	<th style="width:120px">';
		echo '		<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/mail_edit.png" alt="Mail/Newsletter an Auswahl versenden" title="Mail/Newsletter an Auswahl versenden" onclick="create_mail(\'all\');" />';
		echo '		<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/add.png" alt="Kunde(n) zu einer Liste hinzufügen" title="Kunde(n) zu einer Liste hinzufügen" onclick="add_customer_to_costumer_list();" />';
		echo '	</th>';
		echo '</tr>';
		while($row=mysql_fetch_array($res))
		{
			$counter++;
			echo '<tr>';
			echo '	<td><input type="checkbox" name="customer_select[]" id="customer_select_'.$row["customer_id"].'" value="'.$row["customer_id"].'" /></td>';
			echo '	<td style="text-align:right">'.$counter.'</td>';
			echo '	<td>';
			if (isset($reminder[$row["customer_id"]]))
			{
				echo '	<span style="background-color:#f99; width:400px"><b>Wiedervorlage zur Kommunikation am: '.date("d.m.Y H:i", $reminder[$row["customer_id"]]).'</b></span><br />';
			}
			echo '	<b>'.$crm_customer_name[$row["customer_id"]].'</b><br />';
			//echo '	<small><b>Anschrift:</b> ';
			echo '	<small>';
			if (isset($crm_customer_street1[$row["customer_id"]]) && $crm_customer_street1[$row["customer_id"]]!="") echo $crm_customer_street1[$row["customer_id"]];
			if (isset($crm_customer_street2[$row["customer_id"]]) && $crm_customer_street2[$row["customer_id"]]!="") echo ', '.$crm_customer_street2[$row["customer_id"]];
			if (isset($crm_customer_zip[$row["customer_id"]]) && $crm_customer_zip[$row["customer_id"]]!="") echo ', '.$crm_customer_zip[$row["customer_id"]];
			if (isset($crm_customer_city[$row["customer_id"]]) && $crm_customer_city[$row["customer_id"]]!="") echo ' '.$crm_customer_city[$row["customer_id"]];
			if (isset($crm_customer_country[$row["customer_id"]]) && $crm_customer_country[$row["customer_id"]]!="") echo ', '.$crm_customer_country[$row["customer_id"]];
			echo '</small>';
			if (isset($notes[$row["customer_id"]]))
			{
				echo '<br /><span style="background-color:#cc0; width:400px; font-size:8pt">Es sind '.$notes[$row["customer_id"]].' Notizen vorhanden. <a href="javascript:show_notes('.$row["customer_id"].');">[+]</a></span>';
				echo '<div id="notebox'.$row["customer_id"].'" style="display:none; width:400px;"></div>';
			}
			if (isset($communications[$row["customer_id"]]))
			{
				echo '<br /><span style="background-color:#da5; width:400px; font-size:8pt">Es sind '.$communications[$row["customer_id"]].' Kontakte protokolliert. <a href="javascript:show_communication('.$row["customer_id"].');">[+]</a></span>';
				echo '<div id="commbox'.$row["customer_id"].'" style="display:none; width:400px;;"></div>';
			}

			echo '</td>';
			echo '<td>';
				if ($list_owner==$_SESSION["id_user"]) echo '<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/remove.png" alt="Kunde aus Liste löschen" title="Kunde aus Liste löschen" onclick="del_custormer_from_list('.$row["customer_id"].');" />';
				echo '<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/edit.png" alt="Kundendaten bearbeiten" title="Kundendaten bearbeiten" onclick="update_customer_data('.$row["customer_id"].');" />';
				echo '<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/page_edit.png" alt="Notiz hinzufügen" title="Notiz hinzufügen" onclick="add_customer_note('.$row["customer_id"].');" />';
				echo '<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/user_comment.png" alt="Kundenkommunikation protokollieren" title="Kundenkommunikation protokollieren" onclick="add_communication('.$row["customer_id"].');" />';
			echo '</td>';
			echo '</tr>';
		}
		echo '</table>';
		echo '</form>';
	}
	else
	{
		echo '<b>Keine Suchtreffer</b>';
	}
		
?>