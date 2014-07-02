<?php

	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	
?>

<script type="text/javascript">

	function GetExpressCheckout()
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", {API: "paypal", Action: "PayPalGetExpressCheckout",
				token:$("#token").val()
			 },
			function (data)
			{
				wait_dialog_hide();

				var state = $(data).find("state").text();
				alert(state);
				if (state=="Success") {
					$("#name").val(decodeURIComponent($(data).find("name").text()));
					$("#street1").val(decodeURIComponent($(data).find("street1").text()));
					$("#street2").val(decodeURIComponent($(data).find("street2").text()));
					$("#city").val(decodeURIComponent($(data).find("city").text()));
					$("#zip").val(decodeURIComponent($(data).find("zip").text()));
					$("#countryname").val(decodeURIComponent($(data).find("countryname").text()));
					$("#countrycode").val(decodeURIComponent($(data).find("countrycode").text()));
					$("#payerid").val(decodeURIComponent($(data).find("payerID").text()));
					$("#payermail").val(decodeURIComponent($(data).find("payerMail").text()));
					$("#note").val(decodeURIComponent($(data).find("note").text()));
				}
				else
				{
					show_status2(data);
					return;
				}
			}
		);

	}

	function DoExpressCheckout()
	{
		wait_dialog_show();
		$.post("<?php echo PATH; ?>soa/", {API: "paypal", Action: "PayPalDOExpressCheckout",
				token:$("#token").val(),
				total_amount:$("#total").val(),
				name:$("#name").val(),
				street1:$("#street1").val(),
				street2:$("#street2").val(),
				city:$("#city").val(),
				zip:$("#zip").val(),
				countryname:$("#countryname").val(),
				countrycode:$("#countrycode").val(),
				payerID:$("#payerid").val(),
				payerMail:$("#payermail").val(),
				total_items:$("#total_items").val(),
				shipping:$("#shipping").val(),
				handling:$("#handling").val(),
				tax:$("#tax").val()

			 },
			function (data)
			{
				wait_dialog_hide();

				var state = $(data).find("state").text();
				alert(state);
				if (state=="Success") {
					show_status2(data);
					//show_status2(decodeURIComponent($(data).find("transactionID").text())+" "+$(data).find("paymentAmountRecieved").text());
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
echo 'TOKEN: <input type="text" size="20" name="token" id="token" value="'.$_SESSION["token"].'"><br />';
echo 'Vorname: <input type="text" size="20" name="name" id="name" /><br />';
echo 'Straße1: <input type="text" size="20" name="strasse1" id="street1" /><br />';
echo 'Straße2: <input type="text" size="20" name="strasse2" id="street2" /><br />';
echo 'Stadt: <input type="text" size="20" name="stadt" id="city" /><br />';
echo 'PLZ: <input type="text" size="20" name="plz" id="zip" /><br />';
echo 'Land <input type="text" size="20" name="land" id="countryname" /><br />';
echo 'LänderCode <input type="text" size="10" name="landid" id="countrycode" /><br />';
echo 'PAYERID <input type="text" size="10" name="payerid" id="payerid" /><br />';
echo 'PayerMAIL <input type="text" size="20" name="payermail" id="payermail" /><br />';
echo 'NOTE <input type="text" size="30" name="note" id="note" /><br /><br />';


echo 'Total: <input type="text" size="20" name="total" id="total" value="11.99" /><br />';
echo 'Summe Artikel: <input type="text" size="20" name="total_items" id="total_items" value="4.75" /><br />';
echo 'Versandkosten: <input type="text" size="20" name="shipping" id="shipping" value="4.96" /><br />';
echo 'Handlingskosten <input type="text" size="20" name="handling" id="handling" value="0" /><br />';
echo 'MwSt<input type="text" size="20" name="tax" id="tax" value="2.28"/><br />';

echo '<button onclick="DoExpressCheckout();">Bestellen</button>';

echo '<script>GetExpressCheckout();</script>';

include("templates/".TEMPLATE_BACKEND."/footer.php");

?>