<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>

	<script type="text/javascript">
		var $partnerprogram=0;

		function partner_add()
		{
			var $html = '';
			$html += '<table>';
			$html += '	<tr>';
			$html += '		<td>Titel</td>';
			$html += '		<td><input id="partner_add_title" style="width:200px;" type="text" /></td>';
			$html += '	</tr>';
			$html += '	<tr>';
			$html += '		<td>Beschreibung</td>';
			$html += '		<td><textarea id="partner_add_description" style="width:250px; height:75px;"></textarea></td>';
			$html += '	</tr>';
			$html += '</table>';
			if( $("#partner_add_dialog").length==0 ) $("body").append('<div id="partner_add_dialog" style="display:none"></div>');
			$("#partner_add_dialog").html($html);

			$("#partner_add_dialog").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() { partner_add_save(); } },
					{ text: "Abbrechen", click: function() {$(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				modal:true,
				resizable:false,
				title:"Partnerprogramm-Messpunkt hinzufügen",
				width:400
			});		
		}

		function partner_add_save()
		{
			var $title=$("#partner_add_title").val();
			var $description=$("#partner_add_description").val();

			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"PartnerAdd", id_partnerprogram:$partnerprogram, title:$title, description:$description }, function($data)
			{
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" ) { show_status2($data); return; }
				
				view();
				var $id_partner=$xml.find("id_partner").text();
				show_status("Partnerprogramm-Messpunkt erfolgreich angelegt. Die Partner-ID lautet "+$id_partner+".");
				$("#partner_add_dialog").dialog("close");
				wait_dialog_hide();
			});
		}


		function partner_edit($id_partner)
		{
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"PartnerGet", id_partner:$id_partner }, function($data)
			{
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" ) { show_status2($data); return; }
				
				var $title = $xml.find("title").text();
				var $description = $xml.find("description").text();
				var $html = '';
				$html += '<table>';
				$html += '	<tr>';
				$html += '		<td>Titel</td>';
				$html += '		<td><input id="partner_edit_title" style="width:200px;" type="text" value="'+$title+'" /></td>';
				$html += '	</tr>';
				$html += '	<tr>';
				$html += '		<td>Beschreibung</td>';
				$html += '		<td><textarea id="partner_edit_description" style="width:250px; height:75px;">'+$description+'</textarea></td>';
				$html += '	</tr>';
				$html += '</table>';
				if( $("#partner_edit_dialog").length==0 ) $("body").append('<div id="partner_edit_dialog" style="display:none"></div>');
				$("#partner_edit_dialog").html($html);
	
				$("#partner_edit_dialog").dialog
				({	buttons:
					[
						{ text: "Speichern", click: function() { partner_edit_save($id_partner); } },
						{ text: "Abbrechen", click: function() {$(this).dialog("close"); } }
					],
					closeText:"Fenster schließen",
					modal:true,
					resizable:false,
					title:"Partnerprogramm-Messpunkt bearbeiten",
					width:400
				});		
			});		
		}


		function partner_edit_save($id_partner)
		{
			var $title=$("#partner_edit_title").val();
			var $description=$("#partner_edit_description").val();

			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"PartnerUpdate", id_partner:$id_partner, title:$title, description:$description }, function($data)
			{
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" ) { show_status2($data); return; }
				
				view();
				show_status("Partnerprogramm-Messpunkt erfolgreich aktualisiert.");
				$("#partner_edit_dialog").dialog("close");
				wait_dialog_hide();
			});
		}


		function partner_remove($id_partner)
		{
			var $html = '';
			$html += 'Wollen Sie den Partnerprogramm-Messpunkt wirklich löschen?';
			if( $("#partner_remove_dialog").length==0 ) $("body").append('<div id="partner_remove_dialog" style="display:none"></div>');
			$("#partner_remove_dialog").html($html);

			$("#partner_remove_dialog").dialog
			({	buttons:
				[
					{ text: "Ja", click: function() { partner_remove_save($id_partner); } },
					{ text: "Nein", click: function() {$(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				modal:true,
				resizable:false,
				title:"Partnerprogramm-Messpunkt löschen",
				width:400
			});
		}


		function partner_remove_save($id_partner)
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"PartnerRemove", id_partner:$id_partner }, function($data)
			{
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" ) { show_status2($data); $("#partner_remove_dialog").dialog("close"); wait_dialog_hide(); return; }
				
				view();
				show_status("Partnerprogramm-Messpunkt erfolgreich gelöscht.");
				$("#partner_remove_dialog").dialog("close");
				wait_dialog_hide();
			});
		}


		function partnerprogram_add()
		{
			var $html = '';
			$html += '<table>';
			$html += '	<tr>';
			$html += '		<td>Titel</td>';
			$html += '		<td><input id="partnerprogram_add_title" style="width:200px;" type="text" /></td>';
			$html += '	</tr>';
			$html += '	<tr>';
			$html += '		<td>Beschreibung</td>';
			$html += '		<td><textarea id="partnerprogram_add_description" style="width:250px; height:75px;"></textarea></td>';
			$html += '	</tr>';
			$html += '</table>';
			if( $("#partnerprogram_add_dialog").length==0 ) $("body").append('<div id="partnerprogram_add_dialog" style="display:none"></div>');
			$("#partnerprogram_add_dialog").html($html);

			$("#partnerprogram_add_dialog").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() { partnerprogram_add_save(); } },
					{ text: "Abbrechen", click: function() {$(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				modal:true,
				resizable:false,
				title:"Partnerprogramm hinzufügen",
				width:400
			});		
		}


		function partnerprogram_add_save()
		{
			var $title=$("#partnerprogram_add_title").val();
			var $description=$("#partnerprogram_add_description").val();

			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"PartnerprogramAdd", title:$title, description:$description }, function($data)
			{
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" ) { show_status2($data); return; }
				
				$partnerprogram=$xml.find("id_partnerprogram").text();
				show_status("Partnerprogramm erfolgreich angelegt.");
				$("#partnerprogram_add_dialog").dialog("close");
				view();
				wait_dialog_hide();
			});
		}


		function partnerprogram_select($id_partnerprogram)
		{
			$partnerprogram=$id_partnerprogram;
			view();
		}


		function partnerprogram_edit($id_partnerprogram)
		{
			alert("In Arbeit...");	
		}


		function partnerprogram_remove($id_partnerprogram)
		{
			alert("In Arbeit...");	
		}


		function view()
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"PartnerprogramsGet" }, function($data)
			{
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" ) { show_status2($data); return; }

				var $html = '';
				$html += '<table class="hover" style="float:left;">';
				$html += '	<tr>';
				$html += '		<th style="width:20px;">Nr.</td>';
				$html += '		<th style="width:100px;">Partnerprogramm</td>';
				$html += '		<th style="width:60px;">';
				$html += '			<img onclick="partnerprogram_add();" src="<?php echo PATH; ?>images/icons/24x24/add.png" style="cursor:pointer;" title="Partnerprogramm hinzufügen" />';
				$html += '		</th>';
				$html += '	</tr>';
				var $nr=0;
				$xml.find("Partnerprogram").each(function()
				{
					$nr++;
					var $id_partnerprogram=$(this).find("id_partnerprogram").text();
					var $title=$(this).find("title").text();
					var $description=$(this).find("description").text();
					$html += '<tr>';
					$html += '	<td>'+$nr+'</td>';
					$html += '	<td>';
					if( $partnerprogram==$id_partnerprogram ) $style=' style="font-weight:bold;"'; else $style='';
					$html += '		<a'+$style+' href="javascript:partnerprogram_select('+$id_partnerprogram+');" id="partnerprogram'+$id_partnerprogram+'">';
					$html += '			'+$title;
					$html += '		</a>';
					$html += '		<br /><i>'+$description.substr(0, 50)+'</i>';
					$html += '	</td>';
					$html += '	<td>';
					$html += '		<img onclick="partnerprogram_edit('+$id_partnerprogram+');" src="<?php echo PATH; ?>images/icons/24x24/edit.png" style="cursor:pointer;" title="Partnerprogramm bearbeiten" />';
					$html += '		<img onclick="partnerprogram_remove('+$id_partnerprogram+');" src="<?php echo PATH; ?>images/icons/24x24/remove.png" style="cursor:pointer;" title="Partnerprogramm löschen" />';
					$html += '	</td>';
					$html += '</tr>';
				});
				$html += '</table>';
				if( $("#view").length==0 ) $("body").append('<div id="view"></div>');
				$("#view").html($html);
				
				if( $partnerprogram>0 ) view_partners();
			});
		}


		function view_partners()
		{
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"PartnersGet", id_partnerprogram:$partnerprogram }, function($data)
			{
				wait_dialog_hide();
				try { $xml = $($.parseXML($data)); } catch ($err) { show_status2($err.message); return; }
				$ack = $xml.find("Ack");
				if ( $ack.text()!="Success" ) { show_status2($data); return; }
				
				var $html = '';
				$html += '<table class="hover" style="float:left;">';
				$html += '	<tr>';
				$html += '		<th style="width:20px;">Nr.</td>';
				$html += '		<th style="width:40px;">ID</td>';
				$html += '		<th style="width:100px;">Partnerprogramm</td>';
				$html += '		<th style="width:40px;">Besuche</td>';
				$html += '		<th style="width:40px;">Registrierungen</td>';
				$html += '		<th style="width:40px;">Teilnehmer</td>';
				$html += '		<th style="width:40px;">Bestellungen</td>';
				$html += '		<th style="width:80px;">Umsatz</td>';
				$html += '		<th style="width:60px;">';
				$html += '			<img onclick="partner_add();" src="<?php echo PATH; ?>images/icons/24x24/add.png" style="cursor:pointer;" title="Partnerprogramm hinzufügen" />';
				$html += '		</th>';
				$html += '	</tr>';
				var $nr=0;
				var $total_visits=0;
				var $total_registrations=0;
				var $total_participants=0;
				var $total_orders=0;
				var $total_revenue=0;
				var $conversion=0.00;
				$xml.find("Partner").each(function()
				{
					$nr++;
					var $id_partner=$(this).find("id_partner").text();
					var $title=$(this).find("title").text();
					var $description=$(this).find("description").text();
					var $visits=Number($(this).find("visit_counter").text());
					$total_visits += $visits;
					var $orders=Number($(this).find("Orders").text());
					$total_orders += $orders;
					var $revenue=Number($(this).find("Revenue").text());
					$total_revenue += $revenue;
					$html += '<tr>';
					$html += '	<td>'+$nr+'</td>';
					$html += '	<td>'+$id_partner+'</td>';
					$html += '	<td>'+$title+'<br /><i>'+$description.substr(0, 50)+'</i></td>';
					//visits
					$html += '	<td>'+$visits+'</td>';
					//registrations
					var $registrations=Number($(this).find("Registrations").text());
					$total_registrations += $registrations;
					$conversion=0.00;
					if( $visits>0 ) $conversion=Math.round($registrations/$visits*10000)/100;
					$html += '	<td>'+$registrations+' ('+$conversion+')%</td>';
					//participants
					var $participants=Number($(this).find("Participants").text());
					$total_participants += $participants;
					$conversion=0.00;
					if( $visits>0 ) $conversion=Math.round($participants/$visits*10000)/100;
					$html += '	<td>'+$participants+' ('+$conversion+')%</td>';
					//orders
					var $conversion=0.00;
					if( $participants>0 ) $conversion=Math.round($orders/$participants*10000)/100;
					$html += '	<td>'+$orders+' ('+$conversion+')%</td>';
					//revenue
					$html += '	<td>'+$revenue+' €</td>';
					//options
					$html += '	<td>';
					$html += '		<img onclick="partner_edit('+$id_partner+');" src="<?php echo PATH; ?>images/icons/24x24/edit.png" style="cursor:pointer;" title="Partnerprogramm bearbeiten" />';
					$html += '		<img onclick="partner_remove('+$id_partner+');" src="<?php echo PATH; ?>images/icons/24x24/remove.png" style="cursor:pointer;" title="Partnerprogramm löschen" />';
					$html += '	</td>';
					$html += '</tr>';
				});
				$html += '<tr>';
				$html += '	<td colspan="3"><b>Gesamt</b></td>';
				$html += '	<td><b>'+$total_visits+'</b></td>';
				$html += '	<td><b>'+$total_registrations+'</b></td>';
				$html += '	<td><b>'+$total_participants+'</b></td>';
				$html += '	<td><b>'+$total_orders+'</b></td>';
				$html += '	<td><b>'+$total_revenue+' €</b></td>';
				$html += '	<td></td>';
				$html += '</tr>';
				$html += '</table>';
				if( $("#view_partners").length==0 ) $("body").append('<div id="view_partners"></div>');
				$("#view_partners").html($html);
			});
		}
		
		$( document ).ready( function() { view(); } )
	</script>

<?php
	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_shop_index.php">Online-Shop</a>';
	echo ' > Partnerprogramme';
	echo '</p>';

	echo '<h1>Partnerprogramme</h1>';
	echo '<div id="view"></div>';
	echo '<div id="view_partners"></div>';
	exit;




	echo '<table>';
	echo '	<tr>';
	echo '		<th>ID</th>';
	echo '		<th>Programm</th>';
	echo '		<th>Besuche</th>';
	echo '		<th>Benutzer</th>';
	echo '		<th>Bestellungen</th>';
	echo '		<th>Umsatz</th>';
	echo '	</tr>';
	$results=q("SELECT * FROM shop_partners ORDER BY firstmod;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		//count through campaign registered users
		$results2=q("SELECT * FROM cms_users WHERE partner_id=".$row["id_partner"].";", $dbweb, __FILE__, __LINE__);
		$users=mysqli_num_rows($results2);
		//count through campaign done orders
		$results2=q("SELECT * FROM shop_orders WHERE partner_id=".$row["id_partner"].";", $dbshop, __FILE__, __LINE__);
		$orders=mysqli_num_rows($results2);
		//generated revenue
		$total=0;
		if ($orders>0)
		{
			while($row2=mysqli_fetch_array($results2))
			{
				$results3=q("SELECT * FROM shop_orders_items WHERE order_id=".$row2["id_order"].";", $dbshop, __FILE__, __LINE__);
				while($row3=mysqli_fetch_array($results3)) $total+=($row3["amount"]*$row3["price"]);
			}
		}
		echo '<tr>';
		echo '	<td>'.$row["id_partner"].'</td>';
		echo '	<td><a href="?id_partner='.$row["id_partner"].'">'.$row["title"].'</a><br /><i>'.$row["description"].'</i></td>';
		echo '	<td>'.$row["visit_counter"].'</td>';
		echo '	<td>'.$users.'</td>';
		echo '	<td>'.$orders.'</td>';
		echo '	<td>€ '.number_format($total, 2).'</td>';
		echo '</tr>';
	}
	echo '</table>';
	echo '</div>';
	
	
	//show details for partner program
	if ($_GET["id_partner"]>0)
	{
		echo '<div style="margin:5px; float:left;">';
		$results=q("SELECT * FROM shop_partners WHERE id_partner=".$_GET["id_partner"].";", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		echo '<h1>Partnerprogramm: '.$row["title"].'</h2>';
		
		//count through campaign registered users
		$results=q("SELECT * FROM cms_users WHERE partner_id=".$_GET["id_partner"]." ORDER BY firstmod;", $dbweb, __FILE__, __LINE__);
		if (mysqli_num_rows($results)>0)
		{
			echo '<table>';
			echo '<tr><th colspan="2">Registrierte Benutzer</th></tr>';
			while($row=mysqli_fetch_array($results))
			{
				echo '<tr>';
				echo '	<td>'.$row["username"].'</td>';
				echo '	<td><a href="backend_cms_user_editor.php?id_user='.$row["id_user"].'">Anzeigen</a></td>';
				echo '</tr>';
			}
			echo '</table>';
		}
		//count through campaign done orders
		$results=q("SELECT * FROM shop_orders WHERE partner_id=".$_GET["id_partner"].";", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($results)>0)
		{
			echo '<table>';
			echo '<tr><th colspan="2">Generierte Bestellungen</th></tr>';
			while($row=mysqli_fetch_array($results))
			{
				echo '<tr>';
				if ($row["bill_company"]!="") echo '<td>'.$row["bill_company"].'</td>';
				else echo '<td>'.$row["bill_firstname"].' '.$row["bill_lastname"].'</td>';
				echo '	<td><a href="backend_shop_order.php?id_order='.$row["id_order"].'">Anzeigen</a></td>';
				echo '</tr>';
			}
			echo '</table>';
		}
		echo '</div>';
	}

	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>