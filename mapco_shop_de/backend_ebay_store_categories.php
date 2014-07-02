<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	

?>
	<style>
		#account_check_status1_text, #account_check_status2_text, #account_check_status3_text
		{
			width:100%;
			margin:4px 0px 0px 0px;
			border:0;
			font-weight: bold;
			text-align:center;
			text-shadow: 1px 1px 0 #fff;
			float: left;
		}
	</style>

  	<script>
		var $id_account;
		var $ebay_categories=new Array();
		var $auctions=new Array();
		var $gart_name=new Array();
		var $errors=0;
		var $TotalNumberOfPages=0;

	function print_r(arr, level) {
	 
		var dumped_text = "";
		if (!level) level = 0;
	 
		//The padding given at the beginning of the line.
		var level_padding = "";
		var bracket_level_padding = "";
	 
		for (var j = 0; j < level + 1; j++) level_padding += "    ";
		for (var b = 0; b < level; b++) bracket_level_padding += "    ";
	 
		if (typeof(arr) == 'object') { //Array/Hashes/Objects 
			dumped_text += "Array\n";
			dumped_text += bracket_level_padding + "(\n";
			for (var item in arr) {
	 
				var value = arr[item];
	 
				if (typeof(value) == 'object') { //If it is an array,
					dumped_text += level_padding + "[" + item + "] => ";
					dumped_text += print_r(value, level + 2);
				} else {
					dumped_text += level_padding + "[" + item + "] => " + value + "\n";
				}
	 
			}
			dumped_text += bracket_level_padding + ")\n\n";
		} else { //Stings/Chars/Numbers etc.
			dumped_text = "===>" + arr + "<===(" + typeof(arr) + ")";
		}
	 
		return dumped_text;
	 
	}

		function account_check($id_account, $page, $i)
		{
//			alert($id_account+" / "+$page+" / "+$i);
			//end sync
			if($i>0 && $page>$TotalNumberOfPages)
			{
				$( "#account_check_status3" ).append("Synchronisation erfolgreich abgeschlossen.\n");
//				show_status("Die Synchronisation des Accounts "+$id_account+" mit eBay erfolgreich abgeschlossen.");
				return;
			}

			//default config
			if ($i==0)
			{
				$( "#account_check_status1" ).progressbar({ value: false });
				$( "#account_check_status1_text" ).text( "0%" );
				$( "#account_check_status3" ).html("");
				$(function()
				{
					$( "#account_check_status2" ).progressbar({ value: 0 });
					$( "#account_check_status2_text" ).text( "0%" );
				});
				$("#account_check_dialog").dialog
				({	buttons:
					[
						{ text: "OK", click: function() { $(this).dialog("close"); } }
					],
					closeText:"Fenster schließen",
					hide: { effect: 'drop', direction: "up" },
					modal:true,
					resizable:false,
					show: { effect: 'drop', direction: "up" },
					title:"Auktionen synchronisieren",
					width:600
				});
//				$TotalNumberOfPages=0;
				$errors=0;
				$OutputSelector="ItemArray.Item.ItemID,ItemArray.Item.SKU,HasMoreItems,PaginationResult.TotalNumberOfEntries,PaginationResult.TotalNumberOfPages,ReturnedItemCountActual";
			}
			else $OutputSelector="ItemArray.Item.ItemID,ItemArray.Item.SKU,HasMoreItems,ReturnedItemCountActual";

			//get auctions from ebay
			$( "#account_check_status3" ).append( "Rufe bis zu 200 Auktionen bei eBay ab..." );
			$( "#account_check_status1" ).progressbar({ value: false });
			$.post("<?php echo PATH ?>soa/", { API:"ebay", Action:"GetSellerList", id_account:$id_account, PageNumber:$page, OutputSelector:$OutputSelector, EntriesPerPage:200 },
				function(data)
				{
					try { $xml = $($.parseXML(data)); } catch (err) { $( "#account_check_status3" ).append("Fehler\n"+data); return; }
					$( "#account_check_status3" ).append("OK.\n");

					if($i==0)
					{
						$TotalNumberOfEntries=$xml.find("TotalNumberOfEntries").text();
						$TotalNumberOfPages=$xml.find("TotalNumberOfPages").text();
					}
					$HasMoreItems=$xml.find("HasMoreItems").text();
					$ReturnedItemCountActual=parseInt($xml.find("ReturnedItemCountActual").text());
					$(function()
					{
						var $value=Math.floor(($i+$ReturnedItemCountActual)*100/$TotalNumberOfEntries);
						$( "#account_check_status1" ).progressbar({ value:$value });
						$( "#account_check_status1_text" ).text( $value + "% ("+($i+$ReturnedItemCountActual)+" / "+$TotalNumberOfEntries+")" );
					});
					$j=0;
					$auctions=new Array();
					$xml.find("Item").each(
						function()
						{
							$ItemID=$(this).find("ItemID").text();
							$SKU=$(this).find("SKU").text();
							$auctions[$j]=new Array();
							$auctions[$j]["ItemID"]=$ItemID;
							$auctions[$j]["SKU"]=$SKU;
							$i++;
							$j++;
						}
					);
					$( "#account_check_status3" ).append("Überprüfe Auktionen...\n");
					account_check2(0, $page, $i, $id_account);
					return;
/*
					if ( $HasMoreItems=="true" ) AuctionsGet1($id_account, $page+1, $i);
					else
					{
						AuctionsGet2($id_account, 1, 0);
						return;
					}
*/
				}
			);
		}


		function account_check2($j, $page, $i, $id_account)
		{
			if( $j == $auctions.length )
			{
				$( "#account_check_status3" ).append("Überprüfung abgeschlossen.\n");
				account_check($id_account, $page+1, $i);
				return;
			}

			$value=Math.floor(($j+1)*100/$ReturnedItemCountActual);
			$( "#account_check_status2" ).progressbar({ value:$value });
			$( "#account_check_status2_text" ).text( $value + "% ("+($j+1)+" / "+$ReturnedItemCountActual+")" );
			
			//check for missing SKU
			if($auctions[$j]["SKU"]=="")
			{
				$( "#account_check_status3" ).append("Auktion "+$auctions[$j]["ItemID"]+" hat keine SKU.\n");
				$( "#account_check_status3" ).append("Beende Auktion "+$auctions[$j]["ItemID"]+"...");
				$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"EndItem", id_account:$id_account, ItemID:$auctions[$j]["ItemID"] },
					function($data)
					{
						try { $xml = $($.parseXML($data)); } catch (err) { show_status2(err.message+'<br />'+$data); return; }
						$Ack=$xml.find("Ack").text();
						if( $Ack!="Success" )
						{
							show_status2($data);
							$errors++;
							$( "#account_check_status3" ).append("Fehler. Synchronisierung abgebrochen.");
							return;
						}
						else
						{
							$( "#account_check_status3" ).append("OK.\n");
							account_check2($j+1, $page, $i, $id_account);
							return;
						}
					}
				);
				return;
			}

			//check for new auction
			$.post("<?php echo PATH ?>soa/", { API:"ebay", Action:"AuctionGet", ItemID:$auctions[$j]["ItemID"] },
				function(data)
				{
					try { $auction = $($.parseXML(data)); } catch (err) { show_status(err.message+'<br />'+data); return; }
					$Ack=$auction.find("Ack").text();
					if( $Ack!="Success" )
					{
						$errors++;
						$( "#account_check_status3" ).append("Auktion "+$auctions[$j]["ItemID"]+" nicht gefunden.\n");
						account_check_add_auction($j, $page, $i, $id_account);
						return;
					}
					account_check2($j+1, $page, $i, $id_account);
				}
			);
		}


		function account_check_add_auction($j, $page, $i, $id_account)
		{
			//alert(print_r($auctions[$j]));
			//return;
			$( "#account_check_status3" ).append("Auktion "+$ItemID+" wird angelegt...");
			$.post("<?php echo PATH ?>soa/", { API:"ebay", Action:"AuctionAdd", id_account:$id_account, SKU:$auctions[$j]["SKU"], ItemID:$auctions[$j]["ItemID"] },
				function($data)
				{
//					show_status2($data);
					try { $data = $($.parseXML($data)); } catch (err) { show_status(err.message+'<br />'+$data); return; }
					$Ack=$data.find("Ack").text();
					if( $Ack!="Success" )
					{
						$( "#account_check_status3" ).append("Fehler. Synchronisierung abgebrochen.");
						return;
					}
					else
					{
						$( "#account_check_status3" ).append("OK.\n");
						account_check2($j, $page, $i, $id_account);
						return;
					}
				}
			);
		}

		
		function account_select($id)
		{
			$id_account=$id;
			ebay_get_categories();
		}
		
		function ebay_get_categories()
		{
			$("#view_categories").html('<img src="<?php echo PATH ?>images/icons/loaderb64.gif" style="margin:0px 10px 0px 30px" alt="Bitte warten!" /><br />Lese Shop-Kategorien bei eBay aus...');
			$.post("<?php echo PATH ?>soa/", { API:"ebay", Action:"GetStore", id_account:$id_account },
				function(data)
				{
					$("#category_edit_CategoryID").html('');
					$("#category_edit_CategoryID2").html('');
					$("#category_edit_CategoryID").append('<option value="0">(leer)</option>');
					$("#category_edit_CategoryID2").append('<option value="0">(leer)</option>');
					$ebay_categories=new Array();
					$ebay_categories[0]="(leer)";
					$xml = $($.parseXML(data));
					$xml.find("CustomCategory").each(function()
					{
						$CategoryID=$(this).find("CategoryID:first").text();
						$CategoryLevel=$(this).find("Order:first").text();
						$CategoryName=$(this).find("Name:first").text();
						$ebay_categories[$CategoryID]=$CategoryName;
						$("#category_edit_CategoryID").append('<option value="'+$CategoryID+'">'+$CategoryName+'</option>');
						$("#category_edit_CategoryID2").append('<option value="'+$CategoryID+'">'+$CategoryName+'</option>');
						$(this).find("ChildCategory").each(function()
						{
							$CategoryID=$(this).find("CategoryID").text();
							$CategoryLevel=$(this).find("Order").text();
							$CategoryName=$(this).find("Name").text();
							$ebay_categories[$CategoryID]=$CategoryName;
							$CategoryName='&nbsp;&nbsp;&nbsp;&nbsp;'+$CategoryName;
							$("#category_edit_CategoryID").append('<option value="'+$CategoryID+'">'+$CategoryName+'</option>');
							$("#category_edit_CategoryID2").append('<option value="'+$CategoryID+'">'+$CategoryName+'</option>');
						});
					});
					view();
				}
			);
		}

		function category_features($CategoryID)
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"GetCategoryFeatures", id_account:$id_account, CategoryID:$CategoryID },
				function($data)
				{
					show_status2($data);
					wait_dialog_hide();
				}
			);
		}

		function category_edit($GART, $Category, $CategoryID2)
		{
			$("#category_edit_GART").val($GART);
			$("#category_edit_CategoryID").val($Category);
			$("#category_edit_CategoryID2").val($CategoryID2);
			$("#category_edit_dialog").dialog
			({	buttons:
				[
					{ text: "Speichern", click: function() { category_edit_save(); } },
					{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
				],
				closeText:"Fenster schließen",
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Kategorien ändern",
				width:500
			});
		}

		function category_edit_save()
		{
			var $GART=$("#category_edit_GART").val();
			var $StoreCategory = $("#category_edit_CategoryID").val();
			var $StoreCategory2 = $("#category_edit_CategoryID2").val();
			$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"StoreCategoriesEdit", id_account:$id_account, GART:$GART, StoreCategory:$StoreCategory, StoreCategory2:$StoreCategory2 },
				function(data)
				{
					try
					{
						$xml = $($.parseXML(data));
						var $Ack = $xml.find("Ack").text();
					}
					catch (err)
					{
						show_status(err.message+'<br />'+data);
						return;
					}
					if ( $Ack != "Success") show_status2(data);
					else
					{
						show_status("eBay-Shopkategorien erfolgreich gespeichert.");
						$("#category_edit_dialog").dialog("close");
//						$("#category_edit_dialog").remove();
						view_categories();
					}
				}
			);
		}

		function view()
		{
			view_accounts();
			if ( $id_account>0 ) view_categories();
		}

		function view_accounts()
		{
//			$("#view_accounts").html('<img src="<?php echo PATH ?>images/icons/loaderb64.gif" style="margin:0px 0px 0px 30px" alt="Bitte warten!" />');
			$.post("<?php echo PATH ?>soa/", { API:"ebay", Action:"AccountsGet" },
				function(data)
				{
					$listhtml  = '<ul class="orderlist ui-sortable" style="width:250px;">';
					$listhtml += '	<li class="header">';
					$listhtml += '		<div style="width:238px;">Accounts</div>';
					$listhtml += '	</li>';
					$xml = $($.parseXML(data));
					$xml.find("Account").each(
						function()
						{
							$Id=$(this).find("Id").text();
							$Title=$(this).find("Title").text();
							$Description=$(this).find("Description").text();
							$listhtml += '<li>';
							$listhtml += '	<div style="width:238px; text-align:left;">';
							if ($Id==$id_account) $style=' style="font-weight:bold;"'; else $style='';
							$listhtml += '		<a'+$style+' href="javascript:account_select('+$Id+');">'+$Title+'</a>';
							$listhtml += '		<br /><i>'+$Description+'</i>';
							$listhtml += '	</div>';
							$listhtml += '	<div style="width:238px;"><img src="<?php echo PATH; ?>images/icons/24x24/search.png" alt="Auktionen abgleichen" title="Auktionen abgleichen" onclick="account_check('+$Id+', 1, 0);" /></div>';
							$listhtml += '</li>';
						}
					);
					$listhtml += '</ul>';
					$("#view_accounts").html($listhtml);
				}
			);
		}


		function view_categories()
		{
			$("#view_categories").html('<img src="<?php echo PATH ?>images/icons/loaderb64.gif" style="margin:0px 10px 0px 30px" alt="Bitte warten!" /><br />Lese Generische Artikeldaten aus...');
			$.post("<?php echo PATH ?>soa/", { API:"ebay", Action:"StoreCategoriesGet", id_account:$id_account },
				function(data)
				{
					$categories = $($.parseXML(data));

					$.post("<?php echo PATH ?>soa/", { API:"shop", Action:"GartGet" },
						function(data)
						{
							try
							{
								$xml = $($.parseXML(data));
							}
							catch (err)
							{
								show_status2(err.message);
								return;
							}
							$listhtml  = '<ul class="orderlist ui-sortable" style="width:950px;">';
							$listhtml += '	<li class="header">';
							$listhtml += '		<div style="width:50px;">GenArtNr</div>';
							$listhtml += '		<div style="width:300px;">Bezeichnung</div>';
							$listhtml += '		<div style="width:250px;">1. Kategorie</div>';
							$listhtml += '		<div style="width:250px;">2. Kategorie</div>';
							$listhtml += '	</li>';
							$gart_name=new Array();
							$xml.find("GART").each(
								function()
								{
									$GART=parseInt($(this).text());
									$gart_name[$GART]=$(this).attr("Name");
									$CategoryID=0;
									$CategoryID2=0;
									$categories.find("StoreCategory").each(
										function()
										{
											$GART2=parseInt($(this).find("GART").text());
											if ( $GART==$GART2 )
											{
												$CategoryID=$(this).find("StoreCategory").text();
												$CategoryID2=$(this).find("StoreCategory2").text();
												$("#category_edit_CategoryID").val($CategoryID);
												$("#category_edit_CategoryID2").val($CategoryID2);
											}
										}
									);
									$listhtml += '<li>';
									$listhtml += '	<div style="width:50px;">'+$GART+'</div>';
									$listhtml += '	<div style="width:300px;">';
									$listhtml += '		<a href="javascript:category_edit('+$GART+', '+$CategoryID+', '+$CategoryID2+');">'+$gart_name[$GART]+'</a>';
									$listhtml += '	</div>';
									$listhtml += '	<div style="width:250px;">';
									$listhtml += $ebay_categories[$CategoryID];
									if( $CategoryID!=0 )
									{
//										$listhtml += ' <img alt="Rufe Kategoriedetails ab" src="<?php echo PATH; ?>images/icons/24x24/info.png" title="Rufe Kategoriedetails ab" onclick="category_features('+$CategoryID+');" />';
									}
									$listhtml += '</div>';
									$listhtml += '	<div style="width:250px;">';
									$listhtml += $ebay_categories[$CategoryID2];
									if( $CategoryID2!=0 )
									{
//										$listhtml += ' <img alt="Rufe Kategoriedetails ab" src="<?php echo PATH; ?>images/icons/24x24/info.png" title="Rufe Kategoriedetails ab" onclick="category_features('+$CategoryID2+');" />';
									}
									$listhtml += '</div>';
									$listhtml += '</li>';
								}
							);
							$listhtml += '</ul>';
							$("#view_categories").html($listhtml);
						}
					);
				}
			);
		}
	</script>
<?php

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_ebay_index.php">eBay</a>';
	echo ' > Shop-Kategorien';
	echo '</p>';

	//VIEW
	echo '<div id="view_accounts" style="margin:10px; float:left;"></div>';
	echo '<div id="view_categories" style="margin:10px; float:left;"></div>';
	echo '<script> view(); </script>';
	
	//account_check_dialog
	echo '<div id="account_check_dialog" style="display:none;">';
	echo '	eBay-Auktionen<br /><div id="account_check_status1"><span id="account_check_status1_text">0%</span></div>';
	echo '	<br /><br />Überprüfe Auktionen<br /><div id="account_check_status2"><span id="account_check_status2_text">0%</span></div>';
	echo '	<br /><br />Logfile<br /><textarea style="width:500px; height:100px;" id="account_check_status3"></textarea>';
	echo '</div>';
	
	//category_edit_dialog
	echo '<div id="category_edit_dialog" style="display:none;">';
	echo '	<table>';
	echo '		<tr><td>Kategorie 1</td><td><select id="category_edit_CategoryID"></select></td></tr>';
	echo '		<tr><td>Kategorie 2</td><td><select id="category_edit_CategoryID2"></select></td></tr>';
	echo '	</table>';
	echo '	<input id="category_edit_GART" type="hidden" value="" />';
	echo '</div>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>