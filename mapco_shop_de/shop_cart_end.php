<?php
	include("config.php");
	$login_required=true;
	$title="Bestellbestätigung";	

	include("templates/".TEMPLATE."/header.php");
//	include("functions/shop_get_price.php");
/*	include("functions/shop_get_prices.php");
//	include("functions/shop_get_net_price.php");
	include("functions/shop_mail_order.php");
	include("functions/shop_mail_order2.php");
	include("functions/shop_itemstatus.php");	
	include("functions/cms_send_html_mail.php");
	include("functions/cms_t.php");
	include("functions/mapco_gewerblich.php");
	include("functions/mapco_frachtpauschale.php");
	include("functions/shop_checkOnlinePayment.php");*/
	
	//left column
	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	
	//unset session-varables set by shop_cart.php
	unset($_SESSION["id_payment"]);
	unset($_SESSION["payment_memo"]);
	unset($_SESSION["shipping_details"]);
	unset($_SESSION["shipping_net"]);
	unset($_SESSION["shipping_costs"]);
	unset($_SESSION["shipping_memo"]);
	unset($_SESSION["usermail"]);
	unset($_SESSION["userphone"]);
	unset($_SESSION["userfax"]);
	unset($_SESSION["usermobile"]);
	unset($_SESSION["bill_adr_id"]);
	unset($_SESSION["bill_company"]);
	unset($_SESSION["bill_gender"]);
	unset($_SESSION["bill_title"]);
	unset($_SESSION["bill_firstname"]);
	unset($_SESSION["bill_lastname"]);
	unset($_SESSION["bill_street"]);
	unset($_SESSION["bill_number"]);
	unset($_SESSION["bill_additional"]);
	unset($_SESSION["bill_zip"]);
	unset($_SESSION["bill_city"]);
	unset($_SESSION["bill_country_id"]);
	unset($_SESSION["bill_country"]);
	unset($_SESSION["bill_standard"]);
	unset($_SESSION["ship_country_id"]);
	unset($_SESSION["ship_country"]);
	unset($_SESSION["bill_PayPalNote"]);
	unset($_SESSION["ship_gender"]);
	unset($_SESSION["ship_company"]);
	unset($_SESSION["ship_title"]);
	unset($_SESSION["ship_firstname"]);
	unset($_SESSION["ship_lastname"]);
	unset($_SESSION["ship_zip"]);
	unset($_SESSION["ship_city"]);
	unset($_SESSION["ship_street"]);
	unset($_SESSION["ship_number"]);
	unset($_SESSION["ship_additional"]);
	unset($_SESSION["ship_adr_id"]);
	unset($_SESSION["id_shipping"]);
	unset($_SESSION["user_deposit"]);
	unset($_SESSION["bulb_set"]);//Herbstaktion 2013


	//Google Code for Sales Conversion Page
	if (file_exists("templates/".TEMPLATE."/google_cart_end.php"))
	{
		include("templates/".TEMPLATE."/google_cart_end.php");
	}

?>

<script type="text/javascript">
	
	function shop_cart_end_main()
	{
		cart_clear2();
		var text = $('<p style="font-size: 24px; font-weight: bold"><?php echo t("Vielen Dank für Ihre Bestellung"); ?>!</p>');
		$('#main_div').append(text);
		text = $('<p style="font-size: 15px"><?php echo t("Ihr Auftrag ist bei uns eingegangen und wird schnellstmöglich bearbeitet"); ?>.</p>');
		$('#main_div').append(text);
		text = $('<p style="font-size: 18px"><?php echo t("Hier können Sie sich Ihre Bestellung noch einmal ansehen");?>:</p>');
		$('#main_div').append(text);
		var button = $('<a href="<?php echo PATH.$_GET["lang"]; ?>/online-shop/mein-konto/" style="cursor: pointer; font-size: 12px"><?php echo t("Mein Konto");?></a>>');
		$('#main_div').append(button);
		var button = $('<a href="<?php echo PATH.$_GET["lang"]; ?>/online-shop/mein-konto/bestellungen/" style="cursor: pointer; font-size: 12px"><?php echo t("Bestellungen");?></a>>');
		$('#main_div').append(button);
		var button = $('<a href="<?php echo PATH;?>online-shop/bestellung/<?php echo $_SESSION["order_id"];?>/" style="cursor: pointer; font-size: 18px"><?php echo t("Bestellung Nr.: ").$_SESSION["order_id"];?></a>');
		$('#main_div').append(button);
	}
	
</script>
		
<?php
	echo '<br /><div id="main_div"></div>';
	//echo print_r($_SESSION);
	include("templates/".TEMPLATE."/footer.php");
?>	
<script type="text/javascript">shop_cart_end_main();</script>