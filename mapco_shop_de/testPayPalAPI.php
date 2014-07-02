<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	
//	session_start();
	
?>
<script type="text/javascript">

	function SetExpressCheckout()
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", {API: "paypal", Action: "PayPalSetExpressCheckout",
				total_amount:$("#total").val(),
				firstname:$("#firstname").val(),
				lastname:$("#lastname").val(),
				street1:$("#street1").val(),
				street2:$("#street2").val(),
				city:$("#city").val(),
				zip:$("#zip").val(),
				countryname:$("#countryname").val(),
				countrycode:$("#countrycode").val(),
				returnurl:$("#returnurl").val(),
				cancelurl:$("#cancelurl").val(),
			 },
			function (data)
			{
				wait_dialog_hide();
				var state = $(data).find("state").text();
				if (state=="Success") {
					var token=$(data).find("token").text();
					var paypal_href=$(data).find("paypal_href").text();
					window.location = paypal_href;
				}
				else
				{
					show_status2(data);
					return;
				}
			}
		);
	}
		

</script>

<?php
echo 'Summe: <input type="text" size="20" name="summe" id="total" value="11.99"><br />';
echo 'Vorname: <input type="text" size="20" name="vorname" id="firstname" value="Karl"><br />';
echo 'Nachname: <input type="text" size="20" name="nachname" id="lastname" value="Mayer"><br />';
echo 'Straße1: <input type="text" size="20" name="strasse1" id="street1" value="Hauptstr.1"><br />';
echo 'Straße2: <input type="text" size="20" name="strasse2" id="street2" value=""><br />';
echo 'Stadt: <input type="text" size="20" name="stadt" id="city" value="Entenhausen"><br />';
echo 'PLZ: <input type="text" size="20" name="plz" id="zip" value="12345"><br />';
echo 'Land <input type="text" size="20" name="land" id="countryname" value="Deutschland"><br />';
echo 'LänderCode <input type="text" size="10" name="landid" id="countrycode" value="DE"><br />';
echo 'RETURN_URL <input type="text" size="40" name="returnurl_" id="returnurl" value="http://localhost/MAPCO/mapco_shop_de/testPayPalAPI2.php"><br />';
echo 'CANCEL_URL <input type="text" size="40" name="cancelurl_" id="cancelurl" value="http://www.mapco.de"><br />';
echo '<button onclick="SetExpressCheckout();">Senden</button>';

include("templates/".TEMPLATE_BACKEND."/footer.php");

?>