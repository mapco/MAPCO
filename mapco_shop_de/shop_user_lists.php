<?php
	include("config.php");
	$login_required=true;
	include("templates/".TEMPLATE."/header.php");
	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	include("functions/cms_url_encode.php");

	echo '<div id="mid_column">';
	
	//PATH
	echo '<p>';
	echo '<a href="'.PATHLANG.'online-shop/mein-konto/">'.t("Mein Konto").'</a>';
	echo ' > '.t("Meine Listen");
	echo '</p>';

	//echo '<div id="top_button" style="border-style: solid; border-width: 1px; border-bottom: none; border-color: #E6E6E6; cursor: pointer; float: left; margin-left: 2px; padding: 5px; background-color: #E6E6E6"><h1 id="h1top">Meine Top-Artikel</h1></div>';
	//echo '<div id="orders_button" style="border-style: solid; border-width: 1px; border-left: none; border-color: #E6E6E6; cursor: pointer; float: left; padding: 5px;"><h1  id="h1orders">Meine Nachbestellliste</h1></div><br />';
	echo '<select id="list_select"></select>';
	echo '<div id="lists">';
	echo '<div id="orders" style="display: none; margin-top: 23px; clear: both">';
	//echo '<h1>Meine Nachbestellungen</h1>';
	echo '</div>';

	//ANALYZE
	$topitems_amount=array();
	$results=q("SELECT * FROM shop_orders WHERE customer_id=".$_SESSION["id_user"].";", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$results2=q("SELECT * FROM shop_orders_items WHERE order_id=".$row["id_order"].";", $dbshop, __FILE__, __LINE__);
		while($row2=mysqli_fetch_array($results2))
		{
			$topitems_id[$row2["item_id"]]=$row2["item_id"];
			$topitems_amount[$row2["item_id"]]+=$row2["amount"];
			$results3=q("SELECT * FROM shop_items_".$_GET["lang"]." WHERE id_item=".$row2["item_id"].";", $dbshop, __FILE__, __LINE__);
			$row3=mysqli_fetch_array($results3);
			$topitems_title[$row2["item_id"]]=$row3["title"];
		}
	}
	if (sizeof($topitems_amount)>0)	array_multisort($topitems_amount, SORT_DESC, $topitems_id, $topitems_title);
	
	//VIEW
	echo '<div id="top" style="margin-top: 23px; clear: both">';
	//echo '<h1>Meine Top-Artikel</h1>';
	echo '<table class="hover">';
	echo '	<tr>';
	echo '		<th>'.t("Nr.").'</th>';
	echo '		<th>'.t("Artikel").'</th>';
	echo '		<th>'.t("Menge").'</th>';
	echo '	</tr>';
	for($i=0; $i<sizeof($topitems_amount); $i++)
	{
		echo '<tr>';
		echo '	<td>'.($i+1).'</td>';
		echo '	<td><a href="'.PATHLANG.'online-shop/autoteile/'.$topitems_id[$i].'/'.url_encode($topitems_title[$i]).'">'.$topitems_title[$i].'</a></td>';
		echo '	<td><input style="width:30px;" type="text" name="" value="'.$topitems_amount[$i].'" /></td>';
		echo '<tr>';
	}
	echo '</table>';
	echo '</div>';
	echo '</div>';
	
	echo '</div>';
	echo  '<p id="message" style="display:none"></p>';
	
	include("templates/".TEMPLATE."/cms_rightcolumn.php");
	include("templates/".TEMPLATE."/footer.php");
?>

<script>

	list_view();

	var option = $('<option value="top"><?php echo t("Meine Top-Artikel"); ?></option>');
	$('#list_select').append(option);
	option = $('<option value="orders"><?php echo t("Meine Nachbestellliste");?></option>');
	$('#list_select').append(option);
	
	var value_old = $('#list_select').val();
	$('#list_select').change(function()
	{
		$('#' + this.value).show();
		$('#' + value_old).hide();
		value_old = $('#list_select').val();
	});
	/*$('#top_button').click(function(){
		$('#top').css('display', '');
		$('#orders').css('display', 'none');
		//$('#orders').css('border-bottom-style', 'solid');
		$('#orders_button').css('border-bottom-style', 'solid');
		$('#orders_button').css('border-bottom-width', '1px');
		$('#orders_button').css('border-bottom-color', '#E6E6E6');
		$('#top_button').css('border-bottom', 'none');
		$('#top_button').css('background-color', '#E6E6E6');
		$('#orders_button').css('background-color', '#FFFFFF');
	});
		
	$('#orders_button').click(function(){
		list_build();
		$('#orders').css('display', '');
		$('#top').css('display', 'none');
		$('#top_button').css('border-bottom-style', 'solid');
		$('#top_button').css('border-bottom-width', '1px');
		$('#top_button').css('border-bottom-color', '#E6E6E6');
		$('#orders_button').css('border-bottom', 'none');
		$('#orders_button').css('background-color', '#E6E6E6');
		$('#top_button').css('background-color', '#FFFFFF');
	});*/
	
	function cart_add2(item_id, amount)
	{
		//alert("item_id: " + item_id + "  amount: " + amount);
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"CartAdd", id_item:item_id, amount:amount }, function(data) { cart_update(); alert(data); list_view();} );
	}
	
	function list_view()
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa2/", { API:"shop", APIRequest:"ReorderListGet" },
			function(data)
			{
//				show_status2(data);
				try
				{
					var ids = 		new Array();
					var item_ids = 	new Array();
					$xml = $($.parseXML(data));
					$ack = $xml.find("Ack").text();
					if ( $ack=="Success" )
					{
						html = '';
						html += '<table class="hover">';
						html += '	<tr>';
						html += '		<th>Artikel</th>';
						html += '		<th style="width: 50px">Menge</th>';
						html += '		<th style="width: 50px">verfügbar</th>';
						html += '		<th style="width: 80px">In Warenkorb</th>';
						html += '		<th style="width: 50px">Löschen</th>';
						html += '	</tr>';
						
						$xml.find("item").each(
							function(){
								ids.push($(this).find("id").text());
								item_ids.push($(this).find("item_id").text());
								html += '	<tr>';
								html += '		<td><a href="<?php echo PATHLANG;?>online-shop/autoteile/' + $(this).find("item_id").text() + '/' + $(this).find("url_title").text() + '">' + $(this).find("title").text() + '</a></td>';
								html += '		<td style="text-align: right">' + $(this).find("amount").text() + '</td>';
								if($(this).find("stock").text()!="0")
								{
									html += '	<td style="padding-left: 19px"><img src="<?php echo PATH;?>/images/icons/24x24/accept.png" style="margin-top: 4px"></td>';	
									html += '	<td style="padding-left: 5px"><input type="text" id="' + $(this).find("item_id").text() + 'amount" value="1" style="text-align: right; vertical-align: 5px; width: 30px"><img src="<?php echo PATH;?>/images/icons/24x24/shopping_cart_up.png" id="' + $(this).find("item_id").text() + 'cartbutton" style="cursor: pointer; margin-left: 10px; vertical-align: -3px"></td>';
								}
								else
								{
									html += '		<td></td>';
									html += '		<td></td>';
								}
								html += '		<td style="padding-left: 17px"><img src="<?php echo PATH;?>/images/icons/24x24/remove.png" style="cursor: pointer; margin-top: 4px" id="' + $(this).find("item_id").text() + 'deletebutton"></td>';
								html += '	</tr>';
							});
					
						html += '</table>';
						$('#orders').html(html);
						
						for(var a in item_ids)
						{
							(function(b){
								$("#" + b + "cartbutton").click(function(){
									cart_add2(b, $("#" + b + "amount").val());
								});	
							})(item_ids[a]);
						}
						
						for(var n in item_ids)
						{
						 	(function(k) {
						  		$("#" + k + "deletebutton").click(function() {
							  		reorder_list_item_remove( k, $xml.find( 'list_id' ).text() );
								});
						  	})(item_ids[n]);
						}
						
						wait_dialog_hide();
						return;
					}
				}
				catch (err)
				{
					show_status2(err.message+'<br />'+data);
					wait_dialog_hide();
					return;
				}
				wait_dialog_hide();
			}
		);
	}
	
	function reorder_list_item_remove( id, list_id )
	{
		delete_dialog("<?php echo t("Wollen Sie diesen Artikel wirklich löschen?"); ?>");
		function d_close()
		{			
			$post_data = 				new Object();
			$post_data['API'] = 		'shop';
			$post_data['APIRequest'] = 	'ListItemsRemove';
			$post_data['id_list'] = 	list_id;
			$post_data['ids'] = 		id;
			
			soa2( $post_data, 'reorder_list_item_remove_callback' );
		}
		function delete_dialog(message)
		{
			$("#message").html(message);
			$("#message").dialog
			({	buttons:
				[
					{ text: "<?php echo t("Ok"); ?>", click: function() {d_close(); $(this).dialog("close");} },
					{ text: "<?php echo t("Abbrechen"); ?>", click: function() {$(this).dialog("close");} }
				],
				closeText:"<?php echo t("Fenster schließen"); ?>",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"<?php echo t("Achtung!"); ?>",
				width:300
			});
		}
	}
	
	function reorder_list_item_remove_callback( $xml )
	{
		list_view();
	}
	
</script>