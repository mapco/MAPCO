<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>
	<script>
//		var id_account=1;
		var mode="all";
		
	// DATEPICKERS ----------------------------------------------------------------------------------------------------------
					$.datepicker.regional['de'] = {clearText: 'löschen', clearStatus: 'aktuelles Datum löschen',
								closeText: 'schließen', closeStatus: 'ohne Änderungen schließen',
								prevText: '<zurück', prevStatus: 'letzten Monat zeigen',
								nextText: 'Vor>', nextStatus: 'nächsten Monat zeigen',
								currentText: 'heute', currentStatus: '',
								monthNames: ['Januar','Februar','März','April','Mai','Juni',
								'Juli','August','September','Oktober','November','Dezember'],
								monthNamesShort: ['Jan','Feb','Mär','Apr','Mai','Jun',
								'Jul','Aug','Sep','Okt','Nov','Dez'],
								monthStatus: 'anderen Monat anzeigen', yearStatus: 'anderes Jahr anzeigen',
								weekHeader: 'Wo', weekStatus: 'Woche des Monats',
								dayNames: ['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'],
								dayNamesShort: ['So','Mo','Di','Mi','Do','Fr','Sa'],
								dayNamesMin: ['So','Mo','Di','Mi','Do','Fr','Sa'],
								dayStatus: 'Setze DD als ersten Wochentag', dateStatus: 'Wähle D, M d',
								dateFormat: 'dd.mm.yy', firstDay: 1, 
								initStatus: 'Wähle ein Datum', isRTL: false};
					$.datepicker.setDefaults($.datepicker.regional['de']);

	//------------------------------------------------------------------------------------------------------------------------

		$(function()
		{
			$("#comp_from").datepicker({ "dateFormat":"dd.mm.yy", firstDay:1, showOtherMonths: true, selectOtherMonths: true });
		});
		$(function()
		{
			$("#comp_to").datepicker({ "dateFormat":"dd.mm.yy", firstDay:1, showOtherMonths: true, selectOtherMonths: true });
		});
		$(function()
		{
			$( "#CreateTimeFrom" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1, showOtherMonths: true, selectOtherMonths: true, onSelect: function(date) {view();} });
		});
		$(function()
		{
			$( "#CreateTimeTo" ).datepicker({ "dateFormat":"dd.mm.yy", firstDay:1, showOtherMonths: true, selectOtherMonths: true, onSelect: function(date) {view();} });
		});


		
		function show_opt(index)
		{
			if (index==0) mode="all";
			if (index==2) mode="list";
			$(".opt").slideUp(100);
			$("#option"+index).slideDown(100);
		}

		function show_listtypelist()
		{
			
			var listtype=$("#listtype").val();

			if (listtype=="PriceSuggestions" || listtype=="ArtGroup")
			{
				$(".listtype").slideUp(100);
				view();
			}
			else
			{
				$(".listtype").slideUp(100);
				
				$("#"+listtype).slideDown(100);
			}
		}
			
		function show_sub_group(groupID)	
		{
			$(".sub_items").slideUp(150);
			
			if ($(".sub_group"+groupID).is(":visible")) 
			{
				$(".sub_group"+groupID).slideUp(150);
			}
			else 
			{
			$(".sub_group").slideUp(150, "linear",function(){$(".sub_group"+groupID).slideDown(150)});
			}
		}
		function show_sub_items(itemsGroupID)
		{
			if ($(".sub_items"+itemsGroupID).is(":visible"))
			{
				$(".sub_items"+itemsGroupID).slideUp(150);
			}
			else
			{
				$(".sub_items").slideUp(150);
				$(".sub_items"+itemsGroupID).slideDown(150);
			}
		}
		
/*		function orders_update(id_account)
		{
			var from = parseInt($.datepicker.formatDate('@', $("#CreateTimeFrom").datepicker('getDate'))/1000);
			var to = parseInt($.datepicker.formatDate('@', $("#CreateTimeTo").datepicker('getDate'))/1000)+24*3600-1;
			days=Math.ceil((to-from)/(24*3600));
			$("#orders_update_dialog").dialog
			({	buttons:
				[
					{ text: "Schließen", click: function() { $(this).dialog("close"); view(); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Bestellungen aktualisierung",
				width:400
			});
			orders_update2(from, 0, days, id_account);
		}
		
*/
		function orders_update3(id_account)
		{
			alert(id_account);
		}


		//function orders_update2(time, i, days, id_account)
		function orders_update2(id_account, i)
		{
			var from = parseInt($.datepicker.formatDate('@', $("#CreateTimeFrom").datepicker('getDate'))/1000);
			var to = parseInt($.datepicker.formatDate('@', $("#CreateTimeTo").datepicker('getDate'))/1000)+24*3600-1;
			var days=Math.ceil((to-from)/(24*3600));
//			alert(to+"\n"+from+"\n"+days+"\n"+i);
			if (i>=days)
			{
				view();
				$("#orders_update_dialog").dialog("close");
				return;
			}
/*
//			var from=time+(i*24*3600);
//			var to=time+(days*24*3600);
*/
			$("#orders_update_dialog").html("Tag "+(i+1)+" von "+days);
			wait_dialog_show();
			//$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"GetOrders", id_account:id_account, from:from, to:to },
			$.post("<?php echo PATH; ?>soa/", { API:"jobs", Action:"Get_Ebay_Orders", id_account:id_account, from:from, to:to },
				function(data)
				{
					//show_status2(data);
					//return;
					try
					{
						$xml = $($.parseXML(data));
						$ack = $xml.find("Ack");
						if ( $ack.text()!="Success" )
						{
							wait_dialog_hide();
							show_status2(data);
						}
					}
					catch (ex)
					{
						wait_dialog_hide();
						show_status2(data+"\n"+ex);
					}

//					orders_update2(time, i+1, days, id_account);
					orders_update2(id_account, i+1);
				}
			);
		}

		function view()
		{
			var from = $("#CreateTimeFrom").val();
			if ( typeof from == "undefined" ) from=0;
			var to = $("#CreateTimeTo").val();
			if ( typeof to == "undefined" ) to=0;
			
			if (to!=0 && from!=0) {show_statistics();}
			
//			wait_dialog_show();
			$.post("<?php echo PATH ?>soa/", { API:"ebay", Action:"AccountStatisticsView", from:from, to:to },
			function(data)
				{
					$("#view").html(data);
//					wait_dialog_hide();
				}
			);
		}
		
		function show_statistics()
		{
			
			var from = $("#CreateTimeFrom").val();
			if ( typeof from == "undefined" ) from=0;
			var to = $("#CreateTimeTo").val();
			if ( typeof to == "undefined" ) to=0;
			var comp_from = $("#comp_from").val();
			if ( typeof comp_from == "undefined" ) comp_from=0;
			var comp_to = $("#comp_to").val();
			if ( typeof comp_to == "undefined" ) comp_to=0;
			
			var listtype="";
			var ArtList="";
			var Notnull=0;
			if ($("#notnull").is(":checked")) { Notnull=1;}
			
			if (mode=="list") {listtype=$("#listtype").val();}
			
			if (listtype=="ArtList") {ArtList=$("#ArtList").val();}

			if (listtype=="ArtGroup"){
				wait_dialog_show();
			
				$.post("<?php echo PATH ?>soa/", { API:"ebay", Action:"AccountStatisticsView3", from:from, to:to, mode:mode, comp_from:comp_from, comp_to:comp_to},
					function(data)
					{
						$("#statistics").html(data);
						wait_dialog_hide();
					}
				);
			}
			else
			{	
				wait_dialog_show();

				$.post("<?php echo PATH ?>soa/", { API:"ebay", Action:"AccountStatisticsView2", from:from, to:to, mode:mode, comp_from:comp_from, comp_to:comp_to, Notnull:Notnull, listtype:listtype, ArtList:ArtList},
					function(data)
					{
						$("#statistics").html(data);
						wait_dialog_hide();
					}
				);
			}
			
		}
	

	</script>	
<?php
	
	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_ebay_index.php">eBay</a>';
	echo ' > Statistiken';
	echo '</p>';

	echo '<h1>eBay-Statistiken</h1>';
	echo '<div style="float:left">';
		echo '<div style="float:left; width:470px;">';
			echo '<div id="CreateTimeFrom" style="float:left;"></div>';
			echo '<div id="CreateTimeTo" style="float:left;"></div>';
			echo '<br style="clear:both;" />';

			echo '<div id="view"></div>';
			echo '<script>view();</script>';
			
			echo '<ul class="orderlist" style="width:450px">';
			echo '	<li class="header" style="width:450px">Statistiken anzeigen</li>';
			echo '	<li style="width:450px; cursor:pointer;">';
			echo '		<div style="float:left; width:450px; cursor:pointer; text-align:left;" onclick="show_opt(0); view();">alle Verkäufe im Zeitraum anzeigen</div>';
			echo '	<li style="width:450px;">';
			echo '		<div style="float:left; width:450px; cursor:pointer; text-align:left;" onclick="show_opt(2);">Verkäufe nach Listen anzeigen</div>';
			echo '		<div class="opt" id="option2" style="float:left; display:none; width:450px">';
			echo '		<select name="listtype" id="listtype" size="1" style="width:300px; float:left; margin:5px;" onchange="show_listtypelist();">';
			echo '			<option value="">Bitte einen Listentyp wählen</option>';
			echo '			<option value="ArtList">Artikelliste</option>';
			echo '			<option value="PriceSuggestions">Preisrecherche</option>';
			echo '			<option value="ArtGroup">Artikelgruppen</option>';
			echo '		</select>';
			echo '		<select class="listtype" name="ArtList" id="ArtList" size="1" style="width:300px; float:left; margin:5px; display:none" onchange="show_statistics();">';
			echo '			<option value="">Bitte eine Liste wählen</option>';
					$res=q("SELECT * FROM shop_lists WHERE private = '0' OR ( private='1' AND firstmod_user='".$_SESSION["id_user"]."' ) ORDER BY private, title;", $dbshop, __FILE__, __LINE__);
					//$res=q("select * from shop_lists;",$dbshop, __FILE__, __LINE__);
					while ($row=mysqli_fetch_array($res)) {echo '<option value="'.$row["id_list"].'">'.$row["title"].'</option>';}
			echo '		</select>';
/*			
			echo '		<select class="listtype" name="ArtList" id="ArtGroup" size="1" style="width:300px; float:left; margin:5px; display:none" onchange="show_statistics();">';
			echo '			<option value="">Bitte eine Artikelgruppe wählen</option>';
			$results=q("SELECT * FROM shop_categories where category_id > 0 order by category_id;", $dbshop, __FILE__, __LINE__);
			$act_group="";
			while( $row=mysqli_fetch_array($results) ) {
				if ($row["category_id"]==1) 
					{
					$field[$row["id_category"]]=$row["constant"];
					$field2[$row["id_category"]]=$row["id_category"];
					}
				else
				{
					if ($act_group!=$row["category_id"])
					{
						echo '<option value='.$field2[$row["category_id"]].' >'.str_replace("_", " ", $field[$row["category_id"]]).'</option>';
					}
					else {
						echo '<option value='.$row["id_category"].' >&nbsp;&nbsp;&nbsp;'.str_replace("_", " ", $row["constant"]).'</option>';
					}
					$act_group=$row["category_id"];
				}
			}
			echo '		</select>';
*/			
			echo '</div></li>';
			
			echo '</ul>';
			
			echo '<ul class="orderlist" style="width:450px">';
			echo '	<li class="header" style="width:450px">Vergleichszeitraum anzeigen</li>';
			echo '  <li style="width:450px;">';
			echo '	<div style="width:150px; float:left;"><b>von </b><input type="text" name="von" id="comp_from" style="width:75px; cursor:pointer;" /></div>';
			echo '	<div style="width:150px; float:left;"><b>bis </b><input type="text" name="bis" id="comp_to" style="width:75px; cursor:pointer;" /></div>';
			echo '<input type="button" value="Anzeigen" onclick="view();" style="float:right;" /></li>';
			echo '</ul>';
			
			echo '<ul class="orderlist" style="width:450px">';
			echo '	<li class="header" style="width:450px">Weitere Optionen</li>';
			echo '  <li style="width:450px;">';
			echo '	Keine Zeilen mit Anzahl <b>0</b> anzeigen <input type="checkbox" name="notnull" id="notnull" style="cursor:pointer" /></li>';
			echo '</ul>';

			
		echo '</div>';
	
		echo '<div style="float:left">';
			echo '<div id="statistics" style="float:left"></div>';
		echo '</div>';
	
	echo '</div>';
	
	//ORDERS UPDATE DIALOG
	echo '<div id="orders_update_dialog" style="display:none;"></div>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>