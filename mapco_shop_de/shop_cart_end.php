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
	echo '<div id="mid_right_column">';
	
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
		var button = $('<a href="<?php echo PATHLANG; ?>online-shop/mein-konto/" style="cursor: pointer; font-size: 12px"><?php echo t("Mein Konto");?></a>>');
		$('#main_div').append(button);
		var button = $('<a href="<?php echo PATHLANG; ?>online-shop/mein-konto/bestellungen/" style="cursor: pointer; font-size: 12px"><?php echo t("Bestellungen");?></a>>');
		$('#main_div').append(button);
		var button = $('<a href="<?php echo PATHLANG; ?>online-shop/bestellung/<?php echo $_SESSION["order_id"];?>/" style="cursor: pointer; font-size: 18px"><?php echo t("Bestellung Nr.: ").$_SESSION["order_id"];?></a>');
		$('#main_div').append(button);
	}
	
</script>
		
<?php
	echo '<div id="main_div" style="width:100%; text-align:center; float:left;"></div>';
	
	//TRUSTEDSHOPS
	if($_SESSION["lang"]=="de" and $_SESSION["id_shop"]==2 and $_SESSION["order_id"]>0)
	{
		$OK=true;
		$fieldlist=array();
		//BASISFELDER FÜR API-AUFRUF
		$fieldlist["API"]="shop";
		$fieldlist["APIRequest"]="OrderDetailGet";
		$fieldlist["OrderID"]=$_SESSION["order_id"];
		
		$responseXML = post(PATH."soa2/", $fieldlist);
		
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXML);
		}
		catch(Exception $e)
		{
			show_error(9756, 7, __FILE__, __LINE__, $responseXML);
			$OK=false;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);

		
		if ($response->Ack[0]!="Success" || !$OK)
		{
			show_error(9756, 7, __FILE__, __LINE__, $responseXML, false);
		}
		else
		{
			$results2=q("SELECT * FROM shop_payment_types WHERE id_paymenttype=".(int)$response->Order[0]->payments_type_id[0]." LIMIT 1;", $dbshop, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
	
			echo '<div id="trustedshops" style="width:100%; margin-top:30px; float:left; text-align:center;">';
			
			echo '   <p style="font-size: 16px;">';
			echo     '<b>'.t("Bitte bewerten Sie uns").'!</b><br />'.t("Sie finden dazu einen Link in Ihrer Bestellbestätigung").'.';
			echo '   </p>';
			
			echo '   <table style="width:500px; border:1px solid lightgrey; margin:auto; padding:5px;">'; 
			echo '   <tr>'; 
			echo '   <td width="90">'; 
			echo '   <form name="formSiegel" method="post" action="https://www.trustedshops.com/shop/certificate.php" target="_blank">'; 
			echo '   <input type="image" border="0" src="'.PATH.'images/trustedshops/TrustedShops-rgb-Siegel_90Hpx.png" title="Trusted Shops Gütesiegel - Bitte hier Gültigkeit prüfen!">'; 
			echo '   <input name="shop_id" type="hidden" value="'.(string)$response->Order[0]->TrustedShops_id[0].'">'; 
			echo '   </form>'; 
			echo '   </td>'; 
			echo '   <td align="justify">'; 
			echo '   <form id="formTShops" name="formTShops" method="post" action="https://www.trustedshops.com/shop/protection.php" target="_blank">'; 
			echo '   <input name="shop_id" type="hidden" value="'.(string)$response->Order[0]->TrustedShops_id[0].'">'; 
			echo '   <input name="email" type="hidden" value="'.(string)$response->Order[0]->usermail[0].'">'; 
			echo '   <input name="amount" type="hidden" value="'.str_replace(",", ".",(string)$response->Order[0]->orderTotalGross[0]).'">'; 
			echo '   <input name="curr" type="hidden" value="'.(string)$response->Order[0]->Currency_Code[0].'">'; 
			echo '   <input name="paymentType" type="hidden" value="'.$row2["trusted_paymentType"].'">'; 
			echo '   <input name="kdnr" type="hidden" value="'.(int)$response->Order[0]->customer_id[0].'">'; 
			echo '   <input name="ordernr" type="hidden" value="'.(int)$response->Order[0]->id_order[0].'">';
			echo '   Als zus&auml;tzlichen Service bieten wir Ihnen den Trusted Shops K&auml;uferschutz an. Wir &uuml;bernehmen alle Kosten dieser Garantie, Sie m&uuml;ssen sich lediglich anmelden.'; 
			echo '   <br /><br />'; 
			echo '   <input type="submit" id="btnProtect" name="btnProtect" value="Anmeldung zum Trusted Shops Käuferschutz">';
			echo '   </form>'; 
			echo '   </td>'; 
			echo '   </tr>'; 
			echo '   </table>'; 
	
			echo '</div>';
		}
	}
	
	echo '</div>';
	
	include("templates/".TEMPLATE."/footer.php");
?>	
<script type="text/javascript">shop_cart_end_main();</script>