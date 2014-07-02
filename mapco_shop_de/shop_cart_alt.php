<?php
	include("config.php");
	$login_required=true;
	$title="Warenkorb";
	
	$_SESSION["language"]=$_GET["lang"];

	include("templates/".TEMPLATE."/header.php");
//	include("functions/shop_get_price.php");
	include("functions/shop_get_prices.php");
	include("functions/shop_mail_order.php");
	include("functions/shop_itemstatus.php");	
	include("functions/cms_send_html_mail.php");
	include("functions/cms_t.php");
	include("functions/mapco_gewerblich.php");
	include("functions/mapco_frachtpauschale.php");
?>
	<script type="text/javascript">
		window.onbeforeunload = function()
		{
			var ordernr = document.getElementById("ordernr").value;
			var comment = document.getElementById("comment").value;
			var usermail = document.getElementById("usermail").value;
			var userphone = document.getElementById("userphone").value;
			var userfax = document.getElementById("userfax").value;
			var usermobile = document.getElementById("usermobile").value;
			ajax("<?php echo PATH; ?>modules/shop_cart_actions.php?action=additional_save&ordernr="+ordernr+"&comment="+comment+"&usermail="+usermail+"&userphone="+userphone+"&userfax="+userfax+"&usermobile="+usermobile, false);
		}
		
		function submit_cart(shipping_details)
		{
			if ( shipping_details!="" )
			{
				if(confirm("<?php echo t("Bitte bestätigen Sie die gewählte Zahlungs- und Versandart").':\n'; ?>"+shipping_details))
				{
					document.cart.submit();
				} 
				else
				{
					payment_edit(); 
				}
			}
			else
			{
				document.cart.submit();
			}
		}		

		function view_cart()
		{
			$.post("<?php echo PATH; ?>modules/shop_cart_actions.php", { action:"view", lang:"<?php echo $_GET["lang"]; ?>", id_user:<?php echo $_SESSION["id_user"]; ?> },
				function(data)
				{
					$("#view").html(data);
					waypoints_reload();
				}
			);
		}
		
		function country_selection_ok()
		{
			var ship_country_id=$("#country_selection_country").val();
			$.post("<?php echo PATH; ?>modules/shop_cart_actions.php", { action:"country_selection", ship_country_id:ship_country_id }, function(data) { if (data!="") show_status(data);  $("#country_selection_window").dialog("close"); view_cart(); } );
		}
		
		function shipping_country_selection()
		{
			$("#country_selection_window").dialog
			({	buttons:
				[
					{ text: "OK", click: function() { country_selection_ok(); } }
				],
				closeOnEscape:false,
				closeText:"Fenster schließen",
				open: function(event, ui) { $(".ui-dialog-titlebar-close", ui.dialog).hide(); },
				hide: { effect: 'drop', direction: "up" },
				modal:true,
				resizable:false,
				show: { effect: 'drop', direction: "up" },
				title:"Land auswählen",
				width:400,
				zIndex: 3999
			});
		}

		function payment_edit()
		{
			$.post("<?php echo PATH; ?>modules/shop_cart_actions.php", { action:"payment_edit" },
				   	function(data)
					{
						$("#payment_window").html(data);
						$("#payment_window").dialog

						({	buttons:
							[
								{ text: "Speichern", click: function() { payment_save(); } },
								{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
							],
							closeText:"Fenster schließen",
						 	height:300,
							hide: { effect: 'drop', direction: "up" },
							modal:true,
							resizable:false,
							show: { effect: 'drop', direction: "up" },
							title:"Zahlungsoptionen",
							width:600
						});
					}
			);
		}

		function payment_save()
		{
			var id_payment=$("#id_payment_select").val();
			var id_shipping=$("#id_shipping_select").val();
			var shipping_net=$("#payment_shipping_net").val();
			var shipping_costs=$("#payment_shipping_costs").val();
			var shipping_details=$("#payment_shipping_details").val();
			var payment_memo=$("#payment_payment_memo").val();
			var shipping_memo=$("#payment_shipping_memo").val();
			$.post("<?php echo PATH; ?>modules/shop_cart_actions.php", { action:"payment_save", id_payment:id_payment, id_shipping:id_shipping, shipping_net:shipping_net, shipping_costs:shipping_costs, shipping_details:shipping_details, payment_memo:payment_memo, shipping_memo:shipping_memo },
				   	function(data)
					{
						if (data!="") alert(data);
						$("#payment_window").dialog("close");
						view_cart();
					}
			);
		}

		function payment_select()
		{
			var id_payment_select=$("#id_payment_select").val();
			var id_shipping_select=$("#id_shipping_select").val();
			$.post("<?php echo PATH; ?>modules/shop_cart_actions.php", { action:"payment_edit", id_payment_select:id_payment_select, id_shipping_select:id_shipping_select }, function(data) { $("#payment_window").html(data); } );
		}
		
		function availability_edit()
		{
			$.post("<?php echo PATH; ?>modules/shop_cart_actions.php", { action:"availability_edit" },
				   	function(data)
					{
						$("#availability_window").html(data);
						$("#availability_window").dialog
						({	buttons:
							[
								{ text: "Speichern", click: function() { availability_save(); } },
								{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
							],
							closeText:"Fenster schließen",
						 	height:300,
							hide: { effect: 'drop', direction: "up" },
							modal:true,
							resizable:false,
							show: { effect: 'drop', direction: "up" },
							title:"Erreichbarkeit",
							width:400
						});
					}
			);
		}


		function availability_save()
		{
			var usermail=$("#availability_usermail").val();
			var userphone=$("#availability_userphone").val();
			var userfax=$("#availability_userfax").val();
			var usermobile=$("#availability_usermobile").val();
			$.post("<?php echo PATH; ?>modules/shop_cart_actions.php", { action:"availability_save", usermail:usermail, userphone:userphone, userfax:userfax, usermobile:usermobile },
				   	function(data)
					{
						if (data!="") alert(data);
						$("#availability_window").dialog("close");
						view_cart();
					}
			);
		}

		function additional_edit()
		{
			$.post("<?php echo PATH; ?>modules/shop_cart_actions.php", { action:"additional_edit" },
				   	function(data)
					{
						$("#additional_window").html(data);
						$("#additional_window").dialog
						({	buttons:
							[
								{ text: "Speichern", click: function() { additional_save(); } },
								{ text: "Abbrechen", click: function() { $(this).dialog("close"); } }
							],
							closeText:"Fenster schließen",
						 	height:300,
							hide: { effect: 'drop', direction: "up" },
							modal:true,
							resizable:false,
							show: { effect: 'drop', direction: "up" },
							title:"Zusätzliche Informationen",
							width:400
						});
					}
			);
		}

		function additional_save()
		{
			var ordernr=$("#additional_ordernr").val();
			var comment=$("#additional_comment").val();
			$.post("<?php echo PATH; ?>modules/shop_cart_actions.php", { action:"additional_save", ordernr:ordernr, comment:comment },
				   	function(data)
					{
						if (data!="") alert(data);
						$("#additional_window").dialog("close");
						view_cart();
					}
			);
		}

		function bill_delete()
		{
			if (confirm("Wollen Sie die Rechnungsanschrift wirklich löschen?"))
			{
				var bill_delete=$("#bill_adr_id").val();
				$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"AddressBillEdit", action:"bill_edit", bill_delete:bill_delete }, function(data) { $("#bill_window").html(data); } );
			}
		}

		function bill_edit()
		{
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"AddressBillEdit", action:"bill_edit" },
				   	function(data)
					{
						$("#bill_window").html(data);
						$("#bill_window").dialog
						({	buttons:
							[
								{ text: "Speichern", click: function() { bill_save(); } },
								{ text: "Abbrechen", click: function() { $(this).dialog("close"); view_cart(); } }
							],
							closeText:"Fenster schließen",
							hide: { effect: 'drop', direction: "up" },
							modal:true,
							resizable:false,
							show: { effect: 'drop', direction: "up" },
							title:"Rechnungsanschrift",
							width:700
						});
					}
			);
		}

		function bill_save()
		{
			var bill_adr_id=$("#bill_adr_id").val();
			var bill_company=$("#bill_company").val();
			var bill_gender=$("#bill_gender").val();
			var bill_title=$("#bill_title").val();
			var bill_firstname=$("#bill_firstname").val();
			var bill_lastname=$("#bill_lastname").val();
			var bill_street=$("#bill_street").val();
			var bill_number=$("#bill_number").val();
			var bill_additional=$("#bill_additional").val();
			var bill_zip=$("#bill_zip").val();
			var bill_city=$("#bill_city").val();
			var bill_country_id=$("#bill_country_id").val();
			if ($("#bill_standard:checked").val()===undefined) { var bill_standard=0; }
			else { var bill_standard=1;	}			

			if ( bill_company=="" && bill_firstname=="" && bill_lastname=="" )
			{
				$("#bill_company").css("border", "1px solid red");
				$("#bill_firstname").css("border", "1px solid red");
				$("#bill_lastname").css("border", "1px solid red");
				alert("Bitte Firma oder Vor- und Nachname ausfüllen!");
				return;
			}
			else 
			{
				$("#bill_company").css("border", "1px solid green");
				$("#bill_firstname").css("border", "1px solid green");
				$("#bill_lastname").css("border", "1px solid green");
			}
			if ( bill_company=="" && bill_firstname=="" )	{ $("#bill_firstname").css("border", "1px solid red"); return; }
			else $("#bill_firstname").css("border", "1px solid green");
			if ( bill_company=="" && bill_lastname=="" ) { $("#bill_lastname").css("border", "1px solid red"); return; }
			else $("#bill_lastname").css("border", "1px solid green");
			if ( bill_street=="" ) { $("#bill_street").css("border", "1px solid red"); return; }
			else $("#bill_street").css("border", "1px solid green");
			if ( bill_zip=="" ) { $("#bill_zip").css("border", "1px solid red"); return; }
			else $("#bill_zip").css("border", "1px solid green");
			if ( bill_city=="" ) { $("#bill_city").css("border", "1px solid red"); return; }
			else $("#bill_city").css("border", "1px solid green");

			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"AddressBillSave", bill_adr_id:bill_adr_id, bill_company:bill_company, bill_gender:bill_gender, bill_title:bill_title, bill_firstname:bill_firstname, bill_lastname:bill_lastname, bill_street:bill_street, bill_number:bill_number, bill_additional:bill_additional, bill_zip:bill_zip, bill_city:bill_city, bill_country_id:bill_country_id, bill_standard:bill_standard },
				   	function(data)
					{
						if (data!="") alert(data);
						$("#bill_window").dialog("close");
						view_cart();
					}
			);
		}
		
		function bill_select()
		{
			var bill_select=$("#bill_adr_id").val();
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"AddressBillEdit", action:"bill_edit", bill_select:bill_select }, function(data) { $("#bill_window").html(data); } );
		}
		

		function ship_delete()
		{
			if (confirm("Wollen Sie die Lieferanschrift wirklich löschen?"))
			{
				var ship_delete=$("#ship_adr_id").val();
				$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"AddressShippingEdit", action:"ship_edit", ship_delete:ship_delete }, function(data) { $("#ship_window").html(data); } );
			}
		}

		function ship_edit()
		{
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"AddressShippingEdit", action:"ship_edit" },
				   	function(data)
					{
						$("#ship_window").html(data);
						$("#ship_window").dialog
						({	buttons:
							[
								{ text: "Speichern", click: function() { ship_save(); } },
								{ text: "Abbrechen", click: function() { $(this).dialog("close"); view_cart(); } }
							],
							closeText:"Fenster schließen",
							hide: { effect: 'drop', direction: "up" },
							modal:true,
							resizable:false,
							show: { effect: 'drop', direction: "up" },
							title:"Lieferanschrift",
							width:700
						});
					}
			);
		}
		
		function ship_save()
		{
			var ship_adr_id=$("#ship_adr_id").val();
			var ship_company=$("#ship_company").val();
			var ship_gender=$("#ship_gender").val();
			var ship_title=$("#ship_title").val();
			var ship_firstname=$("#ship_firstname").val();
			var ship_lastname=$("#ship_lastname").val();
			var ship_street=$("#ship_street").val();
			var ship_number=$("#ship_number").val();
			var ship_additional=$("#ship_additional").val();
			var ship_zip=$("#ship_zip").val();
			var ship_city=$("#ship_city").val();
			var ship_country_id=$("#ship_country_id").val();
			if ($("#ship_standard:checked").val()===undefined) { var ship_standard=0; }
			else { var ship_standard=1;	}			

			if ( ship_company=="" && ship_firstname=="" && ship_lastname=="" )
			{
				$("#ship_company").css("border", "1px solid red");
				$("#ship_firstname").css("border", "1px solid red");
				$("#ship_lastname").css("border", "1px solid red");
				alert("Bitte Firma oder Vor- und Nachname ausfüllen!");
				return;
			}
			else 
			{
				$("#ship_company").css("border", "1px solid green");
				$("#ship_firstname").css("border", "1px solid green");
				$("#ship_lastname").css("border", "1px solid green");
			}
			if ( ship_company=="" && ship_firstname=="" )	{ $("#ship_firstname").css("border", "1px solid red"); return; }
			else $("#ship_firstname").css("border", "1px solid green");
			if ( ship_company=="" && ship_lastname=="" ) { $("#ship_lastname").css("border", "1px solid red"); return; }
			else $("#ship_lastname").css("border", "1px solid green");
			if ( ship_street=="" ) { $("#ship_street").css("border", "1px solid red"); return; }
			else $("#ship_street").css("border", "1px solid green");
			if ( ship_zip=="" ) { $("#ship_zip").css("border", "1px solid red"); return; }
			else $("#ship_zip").css("border", "1px solid green");
			if ( ship_city=="" ) { $("#ship_city").css("border", "1px solid red"); return; }
			else $("#ship_city").css("border", "1px solid green");

			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"AddressShippingSave", ship_adr_id:ship_adr_id, ship_company:ship_company, ship_gender:ship_gender, ship_title:ship_title, ship_firstname:ship_firstname, ship_lastname:ship_lastname, ship_street:ship_street, ship_number:ship_number, ship_additional:ship_additional, ship_zip:ship_zip, ship_city:ship_city, ship_country_id:ship_country_id, ship_standard:ship_standard },
				   	function(data)
					{
						if (data!="") alert(data);
						$("#ship_window").dialog("close");
						view_cart();
					}
			);
		}
		
		function ship_select()
		{
			var ship_select=$("#ship_adr_id").val();
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"AddressShippingEdit", action:"ship_edit", ship_select:ship_select }, function(data) { $("#ship_window").html(data); } );
		}
		
		function SetPayPalCheckout(id_user)
		{
				var firstname="<?php if (isset($_SESSION["bill_firstname"])) echo $_SESSION["bill_firstname"]; ?>";
				var lastname="<?php if (isset($_SESSION["bill_lastname"])) echo $_SESSION["bill_lastname"]; ?>";
				var zip="<?php if (isset($_SESSION["bill_zip"]) )echo $_SESSION["bill_zip"]; ?>";
				var city="<?php if (isset($_SESSION["bill_city"])) echo $_SESSION["bill_city"]; ?>";
				var street1="<?php if (isset($_SESSION["bill_street"])) echo $_SESSION["bill_street"]; ?>";
				var streetnr="<?php if (isset($_SESSION["bill_number"])) echo $_SESSION["bill_number"]; ?>";
				var street2="<?php if (isset($_SESSION["bill_additional"])) echo $_SESSION["bill_additional"]; ?>";
				var countryname="<?php if (isset($_SESSION["bill_country"])) echo $_SESSION["bill_country"]; ?>";
				var phone="<?php if (isset($_SESSION["userphone"])) echo $_SESSION["userphone"]; ?>";
				var language="<?php echo $_GET["lang"]; ?>";

			$.post("<?php echo PATH; ?>soa/", { API:"paypal", Action:"PayPalSetExpressCheckout", 
				id_user:id_user, firstname:firstname, lastname:lastname, zip:zip, city:city, street1:street1, streetnr:streetnr, street2:street2, countryname:countryname, phone:phone, language:language }, 
			
				function(data) {

					var $xml=$($.parseXML(data));
					var state = $xml.find("state").text();
					if (state=="Success") {
						var paypal_href=$xml.find("paypal_href").text();
						//show_status2(paypal_href);
					
						//Manuelle Weiterleitung zu PayPal
						$("#PayPalLink").html("<a href='"+paypal_href+"' style='cursor:pointer'>Klicken Sie hier, wenn Sie nicht innerhalb weniger Sekunden automatisch weitergeleitet werden.</a>");
						//$("#shop_cart_error").html("Sollten Sie nicht automatisch innerhlab weniger Sekunden zu PayPal weitergeleitet werden, nutzen Sie bitte den folgenden <a href='"+paypal_href+"' style='cursor:pointer'><b> Weiterleitungslink</b></a>");
					//	$("#shop_cart_error").show();
					//	$(".success").hide();
						//automatische WEITERLEITUNG ZU PAYPAL
					//	show_status2(paypal_href);

						window.location = paypal_href;
						
					}
					 else
					{
						$("#shop_cart_error").html("Die Weiterleitung zu PayPal war nicht erfolgreich. Bitte versuchen Sie erneut die Bestellung zu abzuschließen, oder wenden Sie sich an unseren Kundenservice.");
						$("#shop_cart_error").show();
						$.post("<?php echo PATH; ?>soa/", { API:"paypal", Action:"PayPalErrorMail", id_user:id_user, firstname:firstname, lastname:lastname, zip:zip, city:city, street1:street1, streetnr:streetnr, street2:street2, countryname:countryname, phone:phone, language:language, data:data}, function(data2) {});
					}
					
			} );
			
		}
		
		function GetPayPalCheckout() {
			$.post("<?php echo PATH; ?>soa/", { API:"paypal", Action:"PayPalGetExpressCheckout"}, 
				function (data)
				{
					var $xml=$($.parseXML(data));
					var state = $xml.find("state").text();
					//var state = "Success";

					if (state=="Success") {
						DoPayPalCheckout();
					}
					else
					{
						$("#shop_cart_error").html("Bei der Übermittlung der Daten von PayPal ist ein Fehler aufgetreten. Ihre PayPal-Zahlung ist nicht erfolgt. Bitte versuchen Sie erneut die Bestellung zu abzuschließen, oder wenden Sie sich an unseren Kundenservice.");
						$("#shop_cart_error").show();
					}
				}
			);			
		
		}
			
		function DoPayPalCheckout()
		{
			$.post("<?php echo PATH; ?>soa/", { API:"paypal", Action:"PayPalDoExpressCheckout" }, 
			
				function (data)
				{
					var $xml=$($.parseXML(data));
					var state = $xml.find("state").text();
				//var state = "Success";

					if (state=="Success") {
						//MANUELLER BESTELLABSCHLUSS
						$("#shop_cart_error").html("Sollte die Bestellung nicht automatisch innerhlab weniger Sekunden abgeschlossen sein, nutzen Sie bitte den folgenden <a href='<?php echo PATH.'shop_cart.php?PayPalAction=paymentdone'; ?>' style='cursor:pointer'><b> Weiterleitungslink</b></a>");
						$("#shop_cart_error").show();
						//AUTOMATISCHER BESTELLABSCHLUSS
						window.location = "<?php echo PATH.'shop_cart.php?PayPalAction=paymentdone'; ?>";
					}
					else
					{
						$("#shop_cart_error").html("Bei der Übermittlung der Daten von PayPal ist ein Fehler aufgetreten. Ihre PayPal-Zahlung ist nicht erfolgt. Bitte versuchen Sie erneut die Bestellung zu abzuschließen, oder wenden Sie sich an unseren Kundenservice.");
						$("#shop_cart_error").show();
					}
				}
			);	
		}


	</script>

<?php
	//FELD FÜR FEHLERMELDUNGEN
	echo '<div class="warning" id="shop_cart_error" style="display:none"></div>';

	//PAYPAL ABBRUCH DURCH KUNDEN
	if (isset($_GET["PayPalAction"]) && $_GET["PayPalAction"]=="abort") {
		echo '<div class="success">'.t("Die Zahlung der Bestellung per PayPal wurde abgebrochen. Bitte wählen Sie eine andere Zahlungsart.").'</div>';
		echo '<script type="text/javascript">payment_select();</script>';
	}


	$_SESSION["language"]=$_GET["lang"];
	//Gewerbskunde?
	$gewerblich=gewerblich($_SESSION["id_user"]);

	//Frachtpauschale?
	if($gewerblich)
	{
		$frachtpauschale=frachtpauschale($_SESSION["id_user"]);
	}

	//ZAHLUNGSMETHODE AUF PAYPAL PRÜFEN
	$i=0;
	$res=q("SELECT id_payment FROM shop_payment WHERE payment = 'PayPal';",$dbshop, __FILE__, __LINE__);
	while ($row=mysql_fetch_array($res)) {$paypal_IDs[$i]=$row["id_payment"]; $i++;}
	if (isset($_SESSION["id_payment"])) {
		if (in_array($_SESSION["id_payment"], $paypal_IDs)) $paypalpayment=true; else $paypalpayment=false;
	}
	else $paypalpayment=false;

	//CHECK, OB PAYPAL-ZAHLUNG DURCHGEFÜHRT WURDE
	$paypalpaymentOK=false;
	if (isset($_GET["PayPalAction"]) && $_GET["PayPalAction"]=="payment") {
		//TOKEN VERHGLEICHEN
		if ($_SESSION["paypaltoken"]==$_GET["token"]) {
			$paypalpaymentOK=true;
		}
	}
	//CHECK, OB PAYPAL TRANSACTION KOMPLETT
	if (isset($_GET["PayPalAction"]) && $_GET["PayPalAction"]=="paymentdone") {$paypalCheckout=true; $_POST["form_agbs"]="checked";} else {$paypalCheckout=false;}

	
	if (isset($_POST["form_button"])) unset($_SESSION["PayPalCheckout"]);
	
	//ZWEITER (+DRITTER) PAYPAL-DURCHLAUF ->GetPayPalCheckout (+DoPayPalCheckout)
	if ($paypalpayment && $paypalpaymentOK && !$paypalCheckout) {
		echo '<div class="success">'.t("Ihre Bestellung wird übertragen").'....!</div>';
		echo '<script type="text/javascript">GetPayPalCheckout();</script>'; 
		exit;
	}
	
	//Bestellung abschicken
	if (isset($_POST["form_button"]) || $paypalCheckout and !isset($_POST["cartupdate"]) and !isset($_POST["cartclear"]))
	{

		$results=q("SELECT * FROM shop_carts WHERE user_id='".$_SESSION["id_user"]."';", $dbshop, __FILE__, __LINE__);
		if (mysql_num_rows($results)==0) echo '<div class="failure">'.t("Der Warenkorb darf nicht leer sein").'!</div>';
		elseif ($_SESSION["usermail"]=="") echo '<div class="failure">'.t("Sie müssen eine E-Mail-Adresse angeben").'!</div>';
		elseif ($_SESSION["userphone"]=="" and $_SESSION["usermobile"]=="") echo '<div class="failure">'.t("Sie müssen eine Telefonnummer angeben").'!</div>';
		elseif ($_SESSION["bill_firstname"]=="") echo '<div class="failure">'.t("Sie müssen einen Vornamen angeben").'!</div>';
		elseif ($_SESSION["bill_lastname"]=="") echo '<div class="failure">'.t("Sie müssen einen Nachnamen angeben").'!</div>';
		elseif ($_SESSION["bill_zip"]=="") echo '<div class="failure">'.t("Sie müssen eine Postleitzahl angeben").'!</div>';
		elseif ($_SESSION["bill_city"]=="") echo '<div class="failure">'.t("Sie müssen eine Stadt angeben").'!</div>';
		elseif ($_SESSION["bill_street"]=="") echo '<div class="failure">'.t("Sie müssen eine Straße angeben").'!</div>';
		elseif ($_SESSION["bill_number"]=="") echo '<div class="failure">'.t("Sie müssen eine Hausnummer angeben").'!</div>';
		elseif ($_SESSION["bill_country"]=="") echo '<div class="failure">'.t("Sie müssen ein Land angeben").'!</div>';
		elseif (!isset($_POST["form_agbs"])) echo '<div class="failure">'.t("Sie müssen den AGBs zustimmen").'!</div>';


		else
		{

			//ERSTER PAYPAL-DURCHLAUF ->SetPayPalCheckout
			if (isset($_SESSION["id_payment"]) && in_array($_SESSION["id_payment"], $paypal_IDs) && !$paypalpaymentOK && !$paypalCheckout)
			{
				echo '<div class="success">';
				echo t("Sie werden auf die PayPal-Seite weitergeleitet").'...';
				echo '<br /><br />';
				echo '<span id="PayPalLink">';
				echo '<a href="javascript:SetPayPalCheckout(\''.$_SESSION["id_user"].'\');">';
				echo 'Klicken Sie hier, wenn Sie nicht innerhalb weniger Sekunden automatisch weitergeleitet werden.</a>';
				echo '</span>';
				echo '</div>';
				echo '<script type="text/javascript">SetPayPalCheckout(\''.$_SESSION["id_user"].'\');</script>';
				include("templates/".TEMPLATE."/footer.php");
				exit;
			}

			if (isset($_POST["form_button"]) || $paypalCheckout) {

			if (!isset($_SESSION["PayPalTransactionID"])) $_SESSION["PayPalTransactionID"]="";
			if (!isset($_SESSION["PayPalPaymentStatus"])) $_SESSION["PayPalPaymentStatus"]="";
			if (!isset($_SESSION["bill_PayPalNote"])) $_SESSION["bill_PayPalNote"]="";

			if ($paypalCheckout) $PayPalTransactionStateDate=time(); else $PayPalTransactionStateDate=0;
			
			//Bestellung abspeichern
			$results3=q("SELECT username, password FROM cms_users WHERE id_user=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
			$row3=mysql_fetch_array($results3);
			if (!isset($_SESSION["pid"]) or !($_SESSION["pid"]>0)) $pid=0; else $pid=$_SESSION["pid"];
			if($pid==0 and $_SESSION["rcid"]!="" and $_SESSION["rcid"]!=9999 and $_SESSION["rcid"]>0)
			{
				$results4=q("SELECT * FROM cms_locations where RC_NR='".$_SESSION["rcid"]."' LIMIT 1;", $dbweb, __FILE__, __LINE__);
				$row4=mysql_fetch_array($results4);
				$pid=$row4["PID"];
			}
			if(!isset($_SESSION["bill_gender"])) $_SESSION["bill_gender"]="0";
			if(!isset($_SESSION["ship_gender"])) $_SESSION["ship_gender"]="0";
			if ($_SESSION["bill_gender"]==0) $bill_gender='Herr';
			else $bill_gender='Frau';
			if ($_SESSION["ship_gender"]==0) $ship_gender='Herr';
			else $ship_gender='Frau';
			if(!isset($_SESSION["ordernr"])) $_SESSION["ordernr"]="";
			if(!isset($_SESSION["comment"])) $_SESSION["comment"]="";
			
			if(!isset($_SESSION["ship_company"])) $_SESSION["ship_company"]="";
			if(!isset($_SESSION["ship_title"])) $_SESSION["ship_title"]="";
			if(!isset($_SESSION["ship_firstname"])) $_SESSION["ship_firstname"]="";
			if(!isset($_SESSION["ship_lastname"])) $_SESSION["ship_lastname"]="";
			if(!isset($_SESSION["ship_zip"])) $_SESSION["ship_zip"]="";
			if(!isset($_SESSION["ship_city"])) $_SESSION["ship_city"]="";
			if(!isset($_SESSION["ship_street"])) $_SESSION["ship_street"]="";
			if(!isset($_SESSION["ship_number"])) $_SESSION["ship_number"]="";
			if(!isset($_SESSION["ship_additional"])) $_SESSION["ship_additional"]="";

			if($frachtpauschale and stripos($_SESSION["shipping_details"], '(Frachtpauschale)')===FALSE)
			{
				$_SESSION["shipping_details"]=$_SESSION["shipping_details"].' (Frachtpauschale)';
			}
			
			if (!isset($_SESSION["ship_adr_id"]) or $_SESSION["ship_adr_id"]=='') $_SESSION["ship_adr_id"]=0;
			q("INSERT INTO shop_orders (status_id, customer_id, ordernr, comment, usermail, userphone, userfax, usermobile, bill_company, bill_gender, bill_title, bill_firstname, bill_lastname, bill_zip, bill_city, bill_street, bill_number, bill_additional, bill_country, ship_company, ship_gender, ship_title, ship_firstname, ship_lastname, ship_zip, ship_city, ship_street, ship_number, ship_additional, ship_country, shipping_costs, shipping_details, PayPal_TransactionID, PayPal_TransactionState, PayPalTransactionStateDate, PayPal_PendingReason, PayPal_BuyerNote, partner_id, bill_adr_id, ship_adr_id, firstmod, firstmod_user, lastmod, lastmod_user, username, password, shipping_net) VALUES(1, '".$_SESSION["id_user"]."', '".mysql_real_escape_string($_SESSION["ordernr"], $dbshop)."', '".mysql_real_escape_string($_SESSION["comment"], $dbshop)."', '".mysql_real_escape_string($_SESSION["usermail"], $dbshop)."', '".mysql_real_escape_string($_SESSION["userphone"], $dbshop)."', '".mysql_real_escape_string($_SESSION["userfax"], $dbshop)."', '".mysql_real_escape_string($_SESSION["usermobile"], $dbshop)."', '".mysql_real_escape_string($_SESSION["bill_company"], $dbshop)."', '".mysql_real_escape_string($bill_gender, $dbshop)."', '".mysql_real_escape_string($_SESSION["bill_title"], $dbshop)."', '".mysql_real_escape_string($_SESSION["bill_firstname"], $dbshop)."', '".mysql_real_escape_string($_SESSION["bill_lastname"], $dbshop)."', '".mysql_real_escape_string($_SESSION["bill_zip"], $dbshop)."', '".mysql_real_escape_string($_SESSION["bill_city"], $dbshop)."', '".mysql_real_escape_string($_SESSION["bill_street"], $dbshop)."', '".mysql_real_escape_string($_SESSION["bill_number"], $dbshop)."', '".mysql_real_escape_string($_SESSION["bill_additional"], $dbshop)."', '".mysql_real_escape_string($_SESSION["bill_country"], $dbshop)."', '".mysql_real_escape_string($_SESSION["ship_company"], $dbshop)."', '".mysql_real_escape_string($ship_gender, $dbshop)."', '".mysql_real_escape_string($_SESSION["ship_title"], $dbshop)."', '".mysql_real_escape_string($_SESSION["ship_firstname"], $dbshop)."', '".mysql_real_escape_string($_SESSION["ship_lastname"], $dbshop)."', '".mysql_real_escape_string($_SESSION["ship_zip"], $dbshop)."', '".mysql_real_escape_string($_SESSION["ship_city"], $dbshop)."', '".mysql_real_escape_string($_SESSION["ship_street"], $dbshop)."', '".mysql_real_escape_string($_SESSION["ship_number"], $dbshop)."', '".mysql_real_escape_string($_SESSION["ship_additional"], $dbshop)."', '".mysql_real_escape_string($_SESSION["ship_country"], $dbshop)."', '".$_SESSION["shipping_costs"]."', '".mysql_real_escape_string($_SESSION["shipping_details"], $dbshop)."', '".mysql_real_escape_string($_SESSION["PayPalTransactionID"], $dbshop)."', '".mysql_real_escape_string($_SESSION["PayPalPaymentStatus"], $dbshop)."', ".$PayPalTransactionStateDate.", '".mysql_real_escape_string($_SESSION["PayPalPendingReason"], $dbshop)."', '".mysql_real_escape_string($_SESSION["bill_PayPalNote"], $dbshop)."', ".$pid.", ".$_SESSION["bill_adr_id"].", ".$_SESSION["ship_adr_id"].", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].", '".mysql_real_escape_string($row3["username"], $dbshop)."', '".mysql_real_escape_string($row3["password"], $dbshop)."', '".$_SESSION["shipping_net"]."');", $dbshop, __FILE__, __LINE__);
			$order_id=mysql_insert_id($dbshop);
			//Bestellte Artikel und Preise abspeichern
			$results=q("SELECT * FROM shop_carts WHERE user_id='".$_SESSION["id_user"]."';", $dbshop, __FILE__, __LINE__);
			while($row=mysql_fetch_array($results))
			{
				$price = get_prices($row["item_id"], $row["amount"]);

				if ($gewerblich)
				{
					if ($_SESSION["rcid"]==16 and time()>mktime(0,0,0,8,1,2012) and time()<mktime(0,0,0,10,1,2012))
					{
						if($_SESSION["id_shipping"]==8 or $_SESSION["id_shipping"]==50) $special=10;
						else $special=5;
						$price["net"]=$price["net"]*((100-$special)/100);
						$price["total"]=$price["net"];
					}
				}

				q("INSERT INTO shop_orders_items (order_id, item_id, amount, price, netto) VALUES('".$order_id."', '".$row["item_id"]."', ".$row["amount"].", '".$price["total"]."', '".$price["net"]."');", $dbshop, __FILE__, __LINE__);
			}
			q("UPDATE cms_users SET payment_id=".$_SESSION["id_payment"].", shipping_id=".$_SESSION["id_shipping"]." WHERE id_user=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);

			//Bestellung nach Borkheide schicken
			if (!$paypalpayment || ($_SESSION["PayPalPaymentStatus"]=="Completed" && $paypalpayment) ) {
				mail_order($order_id);
			}
			
			//Warenkorb leeren
			echo '<script type="text/javascript"> cart_clear2(); </script>';
			//Bestellinformationen leeren
			unset($_SESSION["comment"]);
			unset($_SESSION["ordernr"]);

			unset($_SESSION["PayPalPendingReason"]);
			unset($_SESSION["PAYMENTREQUEST_0_ITEMAMT"]);
			unset($_SESSION["PAYMENTREQUEST_0_SHIPPINGAMT"]);
			unset($_SESSION["PAYMENTREQUEST_0_TAXAMT"]);
			unset($_SESSION["PAYMENTREQUEST_0_AMT"]);
			unset($_SESSION["paypaltoken"]);
			unset($_SESSION["PayPalCheckout"]);
			unset($_SESSION["PayPalTransactionID"]);
			unset($_SESSION["PayPalPaymentStatus"]);
			unset($_SESSION["bill_PayPalPayerID"]);
			//unset($_SESSION["bill_firstname"]);
			//unset($_SESSION["bill_lastname"]);
			//unset($_SESSION["bill_number"]);
			//unset($_SESSION["bill_street"]);
			//unset($_SESSION["bill_country"]);
			//unset($_SESSION["bill_zip"]);
			//unset($_SESSION["bill_city"]);
			//unset($_SESSION["userphone"]);

			unset($_POST["form_agbs"]);
			echo '<div class="success">'.t("Bestellung erfolgreich versendet").'.</div>';
			} // ELSEIF isset($_POST["form_button"])
		}
	}
	
	//Warenkorb aktualisieren
	if ( isset($_POST["cartupdate"]) or (isset($_POST["cartupdate_x"]) and isset($_POST["cartupdate_y"])) )
	{
		for($i=0; $i<sizeof($_POST["item_id"]); $i++)
		{
			if ($_POST["amount"][$i]>0)
			{
				q("UPDATE shop_carts SET amount=".$_POST["amount"][$i].", lastmod=".time()." WHERE item_id=".$_POST["item_id"][$i]." AND user_id='".$_SESSION["id_user"]."';", $dbshop, __FILE__, __LINE__);
			}
			else
			{
				q("DELETE FROM shop_carts WHERE item_id=".$_POST["item_id"][$i]." AND user_id='".$_SESSION["id_user"]."';", $dbshop, __FILE__, __LINE__);
			}
		}
		echo '<script type="text/javascript"> cart_update(); </script>';
		echo '<div class="success">'.t("Warenkorb erfolgreich aktualisiert").'!</div>';
	}
		
	//Gutschein verwerten
	$coupon_value=0;
	$total=0;
	if ( isset($_POST["coupon_code"]) and $_POST["coupon_code"]=="12345" )
	{
		$results=q("SELECT * FROM shop_carts AS a, shop_items AS b, shop_items_".$_GET["lang"]." AS c WHERE a.user_id='".$_SESSION["id_user"]."' AND item_id=b.id_item AND b.id_item=c.id_item;", $dbshop, __FILE__, __LINE__);
		while($row=mysql_fetch_array($results))
		{
//			$total+=$row["amount"]*get_net_price($row["id_item"]);
		}
		$total*=((100+UST)/100);
		if ($total>50)
		{
			if ($gewerblich) $coupon_value=8.10;
			else $coupon_value=10;
			echo '<div class="success">Ihr Gutschein wurde erfolgreich erfasst.</div>';
		}
		else
		{
			echo '<div class="failure">Der nötige Bestellwert von 50 Euro ist für diesen Gutschein noch nicht erreicht.</div>';
		}
	}
	elseif( isset($_POST["coupon_code"]) and $_POST["coupon_code"]!="")
	{
		echo '<div class="failure">Ihr Gutscheincode ist leider nicht gültig. Bitte überprüfen Sie Ihre Eingabe!</div>';
	}
	
	
	//Artikel aus dem Warenkorb entfernen
	if ( isset($_POST["item_id"]) )
	{
		for($i=0; $i<sizeof($_POST["item_id"]); $i++)
		{
			if (isset($_POST["removeitem".$_POST["item_id"][$i]."_x"]))
			{
				q("DELETE FROM shop_carts WHERE item_id=".$_POST["item_id"][$i]." AND user_id='".$_SESSION["id_user"]."';", $dbshop, __FILE__, __LINE__);
				echo '<script type="text/javascript"> cart_update(); </script>';
				echo '<div class="success">'.t("Artikel erfolgreich aus dem Warenkorb entfernt").'!</div>';
			}
		}
	}


	//Warenkorb leeren
	if (isset($_POST["cartclear"]))
	{
		q("DELETE FROM shop_carts WHERE user_id='".$_SESSION["id_user"]."';", $dbshop, __FILE__, __LINE__);
		echo '<script type="text/javascript">cart_update(); </script>';
		echo '<div class="success">'.t("Warenkorb erfolgreich geleert").'</div>';
	}


	//REGIONAL-CENTER 
	if($_SESSION["rcid"]!="" and $_SESSION["rcid"]!=9999 and $_SESSION["rcid"]>0)
	{
		$results2=q("SELECT * FROM cms_locations where RC_NR='".$_SESSION["rcid"]."' LIMIT 1;", $dbweb, __FILE__, __LINE__);
		$row2=mysql_fetch_array($results2);
		$_SESSION["rc_shipping"]=$row2["SHIPPING_ID"];
	}
		
	//read user data
	if(!isset($_SESSION["bill_adr_id"]))
	{
		$results=q("SELECT * FROM cms_users WHERE id_user=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
		$row=mysql_fetch_array($results);
		$kundennr=$row["username"];
		
		if($row["payment_id"]>0 and $row["shipping_id"]>0)
		{
			//Standard Zahlungsart
			$results2=q("SELECT * FROM shop_payment WHERE id_payment=".$row["payment_id"].";", $dbshop, __FILE__, __LINE__);
			$row2=mysql_fetch_array($results2);
			$_SESSION["id_payment"]=$row2["id_payment"];
			$_SESSION["payment_memo"]=$row2["payment_memo"];
			$_SESSION["shipping_details"]=$row2["payment"];
			
			//Standard Versandart
			$results3=q("SELECT * FROM shop_shipping WHERE id_shipping=".$row["shipping_id"].";", $dbshop, __FILE__, __LINE__);
			$row3=mysql_fetch_array($results3);
			$_SESSION["id_shipping"]=$row3["id_shipping"];
			$_SESSION["shipping_net"]=$row3["price"];
			if (gewerblich($_SESSION["id_user"])) $_SESSION["shipping_costs"]=$row3["price"];
			else $_SESSION["shipping_costs"]=((100+UST)/100)*$row3["price"];
			$_SESSION["shipping_details"].=', '.$row3["shipping"];
			$_SESSION["shipping_memo"]=$row3["shipping_memo"];
		}
		
		if ( !isset($_POST["usermail"]) or $_POST["usermail"]=="") $_POST["usermail"]=$row["usermail"];
		
		$results=q("SELECT * FROM shop_orders WHERE customer_id=".$_SESSION["id_user"]." ORDER BY firstmod DESC LIMIT 1;", $dbshop, __FILE__, __LINE__);
		if (mysql_num_rows($results)>0)
		{
			$row=mysql_fetch_array($results);
			if (!isset($_SESSION["usermail"]) or $_SESSION["usermail"]=="") $_SESSION["usermail"]=$row["usermail"];
			if (!isset($_SESSION["userphone"]) or $_SESSION["userphone"]=="") $_SESSION["userphone"]=$row["userphone"];
			if (!isset($_SESSION["userfax"]) or $_SESSION["userfax"]=="") $_SESSION["userfax"]=$row["userfax"];
			if (!isset($_SESSION["usermobile"]) or $_SESSION["usermobile"]=="") $_SESSION["usermobile"]=$row["usermobile"];
		}

		$results2=q("SELECT * FROM shop_bill_adr WHERE user_id=".$_SESSION["id_user"]." and active=1 and standard=1 LIMIT 1;", $dbshop, __FILE__, __LINE__);
		if (mysql_num_rows($results2)>0)
		{
			$row2=mysql_fetch_array($results2);
			$_SESSION["bill_adr_id"]=$row2["adr_id"];
			$_SESSION["bill_company"]=$row2["company"];
			$_SESSION["bill_gender"]=$row2["gender"];
			$_SESSION["bill_title"]=$row2["title"];
			$_SESSION["bill_firstname"]=$row2["firstname"];
			$_SESSION["bill_lastname"]=$row2["lastname"];
			$_SESSION["bill_street"]=$row2["street"];
			$_SESSION["bill_number"]=$row2["number"];
			$_SESSION["bill_additional"]=$row2["additional"];
			$_SESSION["bill_zip"]=$row2["zip"];
			$_SESSION["bill_city"]=$row2["city"];
			$_SESSION["bill_country_id"]=$row2["country_id"];
			$_SESSION["bill_country"]=$row2["country"];
			$_SESSION["bill_standard"]=$row2["standard"];
		}
		else
		{
			if (mysql_num_rows($results)>0)
			{
				$results3=q("SELECT * FROM shop_countries WHERE country='".$row["bill_country"]."';", $dbshop, __FILE__, __LINE__);
				if (mysql_num_rows($results3)>0)
				{
					$row3=mysql_fetch_array($results3);
					$_SESSION["bill_company"]=$row["bill_company"];
					if ($row["bill_gender"]=="Frau") $_SESSION["bill_gender"]=1; else $_SESSION["bill_gender"]=0;
					$_SESSION["bill_title"]=$row["bill_title"];
					$_SESSION["bill_firstname"]=$row["bill_firstname"];
					$_SESSION["bill_lastname"]=$row["bill_lastname"];
					$_SESSION["bill_street"]=$row["bill_street"];
					$_SESSION["bill_number"]=$row["bill_number"];
					$_SESSION["bill_additional"]=$row["bill_additional"];
					$_SESSION["bill_zip"]=$row["bill_zip"];
					$_SESSION["bill_city"]=$row["bill_city"];
					$_SESSION["bill_country"]=$row["bill_country"];
					$_SESSION["bill_country_id"]=$row3["id_country"];	
					q("INSERT INTO shop_bill_adr (user_id, company, gender, title, firstname, lastname, street, number, additional, zip, city, country, country_id, standard) VALUES(".$_SESSION["id_user"].", '".mysql_real_escape_string($row["bill_company"], $dbshop)."', '".$_SESSION["bill_gender"]."', '".mysql_real_escape_string($row["bill_title"], $dbshop)."', '".mysql_real_escape_string($row["bill_firstname"], $dbshop)."', '".mysql_real_escape_string($row["bill_lastname"], $dbshop)."', '".mysql_real_escape_string($row["bill_street"], $dbshop)."', '".mysql_real_escape_string($row["bill_number"], $dbshop)."', '".mysql_real_escape_string($row["bill_additional"], $dbshop)."', '".mysql_real_escape_string($row["bill_zip"], $dbshop)."', '".mysql_real_escape_string($row["bill_city"], $dbshop)."', '".$row["bill_country"]."', ".$row3["id_country"].", 1);", $dbshop, __FILE__, __LINE__);	
		
					$_SESSION["bill_adr_id"]=mysql_insert_id();
				}
				else
				{
					$_SESSION["bill_adr_id"]="";
					$_SESSION["bill_company"]="";
					$_SESSION["bill_gender"]=0;
					$_SESSION["bill_title"]="";
					$_SESSION["bill_firstname"]="";
					$_SESSION["bill_lastname"]="";
					$_SESSION["bill_street"]="";
					$_SESSION["bill_number"]="";
					$_SESSION["bill_additional"]="";
					$_SESSION["bill_zip"]="";
					$_SESSION["bill_city"]="";
					$_SESSION["bill_country"]="";
					$_SESSION["bill_country_id"]="";
				}

				$_SESSION["ship_company"]="";
				$_SESSION["ship_gender"]=0;
				$_SESSION["ship_title"]="";
				$_SESSION["ship_firstname"]="";
				$_SESSION["ship_lastname"]="";
				$_SESSION["ship_street"]="";
				$_SESSION["ship_number"]="";
				$_SESSION["ship_additional"]="";
				$_SESSION["ship_zip"]="";
				$_SESSION["ship_city"]="";
				$_SESSION["ship_country"]="";
				$_SESSION["ship_country_id"]="";
			}
		}
	}

	
	//VIEW
	$shop_cart=TRUE;
	echo '<div id="left_mid_right_column">';
/*
	if ($gewerblich)
	{
		if($_SESSION["bill_country"]=="Deutschland")
		{
			echo '<div class="warning">&nbsp;&nbsp;&nbsp; ACHTUNG! &nbsp; Ab dem 01.04.2012 erhöhen sich unsere Inlandsversandkosten für DPD und Nachtversand um jeweils 50Cent!</div>';
		}
	}
*/
	echo '<div id="view"></div>';
	echo '<script type="text/javascript"> view_cart(); </script>';
	echo '</div>';
		
	//COUNTRY WINDOW
	echo '<div id="country_selection_window" style="display:none;">';
	echo '<table>';
	echo '	<tr>';
	echo '		<td colspan="2">';
	echo 			t("Bitte wählen Sie das Land aus, in welches die Ware verschickt werden soll.");
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Land</td>';
	echo '		<td>';
	echo '			<select id="country_selection_country">';
	$results=q("SELECT * FROM shop_countries ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
	while($row=mysql_fetch_array($results))
	{
		echo '<option value="'.$row["id_country"].'">'.t($row["country"], __FILE__, __LINE__).'</option>';
	}
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</div>';
	
	//ADDITIONAL WINDOW
	echo '<div id="additional_window" style="display:none;">';
	echo '</div>';
	
	//AVAILABILITY WINDOW
	echo '<div id="availability_window" style="display:none;">';
	echo '</div>';
	
	//BILL WINDOW
	echo '<div id="bill_window" style="display:none;">';
	echo '</div>';
	
	//SHIP WINDOW
	echo '<div id="ship_window" style="display:none;">';
	echo '</div>';

	//PAYMENT WINDOW
	echo '<div id="payment_window" style="display:none;">';
	echo '</div>';

	// PayPal Redirect Window
	echo '<div id="paypal_redirect_window" class="warning" style="display:none;">';
	echo '</div>';

	include("templates/".TEMPLATE."/footer.php");
?>