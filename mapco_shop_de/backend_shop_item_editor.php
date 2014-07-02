<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>
	<script type="text/javascript">
		function auction_submit($Action, $id_auction)
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:$Action, id_auction:$id_auction },
				function($data)
				{
					wait_dialog_hide();
					try

					{
						$xml = $($.parseXML($data));
						$ack = $xml.find("Ack");
						if ( $ack.text()!="Success" )
						{
							show_status2($data);
							return;
						}
					}
					catch (err)
					{
						show_status2(err.message);
						return;
					}
					if( $Action=="AddItem" ) show_status("Auktion erfolgreich erstellt.");
					else if( $Action=="ReviseItem" ) show_status("Auktion erfolgreich aktualisiert.");
					else if( $Action=="EndItem" ) show_status("Auktion erfolgreich beendet.");
					view_auctions();
				}
			);
		}
		
		function get_item($id_account, $ItemID)
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"GetItem", id_account:$id_account, ItemID:$ItemID },
				function($data)
				{
					show_status2($data);
					wait_dialog_hide();
				}
			);
		}
		
		function get_ebay_details($id_account, $ItemID)
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"GetEbayDetails", id_account:$id_account },
				function($data)
				{
					show_status2($data);
					wait_dialog_hide();
				}
			);
		}
		
		function get_last_response($id_auction)
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"AuctionGet", id_auction:$id_auction },
				function($data)
				{
					wait_dialog_hide();
					try
					{
						$xml = $($.parseXML($data));
						$ack = $xml.find("Ack");
						if ( $ack.text()!="Success" )
						{
							show_status2($data);
							return;
						}
					}
					catch (err)
					{
						show_status2(err.message);
						return;
					}
					show_status2($xml.find("responseXml").text());
				}
			);
		}
		
		function item_create_auctions($id_item, $id_accountsite)
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"ItemCreateAuctions", id_item:$id_item, id_accountsite:$id_accountsite }, function($data)
			{
				wait_dialog_hide();
				try
				{
					$xml = $($.parseXML($data));
					$ack = $xml.find("Ack");
					if ( $ack.text()!="Success" )
					{
						show_status2($data);
						return;
					}
				}
				catch (err)
				{
					show_status2(err.message);
					return;
				}
				view_auctions();
				show_status("Auktionen neu erstellt und in die Warteschlange gestellt. Die Auktionen werden in den nächsten 30min auf eBay hochgeladen.");
			});
		}

		function view_auctions()
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ItemAuctionsView", id_item:<?php echo $_GET["id_item"]; ?> },
				function(data)
				{
					$("#view").html(data);
					wait_dialog_hide();
				}
			);
		}
	</script>

<?php
	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_shop_index.php">Online-Shop</a>';
	echo ' > <a href="backend_shop_items.php">Artikel</a>';
	echo ' > Artikel-Editor';
	echo '</p>';
	
	//tabs
	echo '<div class="tab">Allgemein</div>';
	echo '<div class="tab">Bilder</div>';
	echo '<a class="tab" href="javascript:view_auctions();">Auktionen</a>';
	
	//view
	echo '<div id="view"></div>';
	echo '<script> view_auctions(); </script>';


	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>