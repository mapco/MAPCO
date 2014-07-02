<?php
	include("config.php");
	$login_required=true;
	$title="Bestellbestätigung";	


	$order_id = $_SESSION["checkout_order_id"];

	include("templates/".TEMPLATE."/header.php");
	
	//left column
	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	echo '<div id="mid_right_column">';
	
	
	
	//unset session-varables set by shop_cart.php
	unset( $_SESSION["checkout_order_id"] );
	unset( $_SESSION["checkout"] );
	
	/*
	//Google Code for Sales Conversion Page
	if (file_exists("templates/".TEMPLATE."/google_cart_end.php"))
	{
		include("templates/".TEMPLATE."/google_cart_end.php");
	}
	*/
	
	
	//LEERE WARENKORB
		//NUR SHOP_CART_ITEMS die dieser Order zugeordnet sind
	$postfields = array();
	$postfields['API'] 			= 'shop';
	$postfields['APIRequest'] 	= 'CartClear';
	$postfields['order_id'] 	= $order_id;
	
	$response = soa2($postfields, __FILE__, __LINE__, "obj");	
	
	if ( (string)$response->Ack[0] != "Success")
	{
		show_error(11360, 7, __FILE__, __LINE__, (string)$response->text[0], false);	
	}
	
	// MAIL ZUM KUNDEN NEU
	$postfields=array();
	$postfields['API']			= 'shop';
	$postfields['APIRequest']	= 'MailOrderConfirmation';
	$postfields['order_id']		= $order_id;

	$response = soa2($postfields, __FILE__, __LINE__, "obj");	


	if ( (string)$response->Ack[0] != "Success")
	{
		show_error(11361, 7, __FILE__, __LINE__, (string)$response->text[0], false);
	}


	//CHECK, if mail to shop has to be send
		/*
			- FRANCISE SHOPS bekommen MAIL immer sofort
			- MAPCO EIGENE SHOPS: MAIL JE NACH ZAHLUNG UND ZAHLUNGSSTATUS	
				RECHNUNG: 			sofort
				VORKASSE: 			sofort
				NACHNAHME: 			sofort
				PayPal: 			nur bei Zahlstatus COMPLETED
				Kreditkarte:		nur bei Zahlstatus COMPLETED
				Sofortüberweisung:	nur bei Zahlstatus COMPLETED
				Barzhalung:			sofort
		*/
		
	$mailOrderSeller = false;
	//GET PAYMENT_TYPE_ID
	$res = q("SELECT * FROM shop_orders WHERE id_order = ".$order_id, $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows( $res ) == 0 )
	{
		show_error(9811, 7, __FILE__, __LINE__, (string)$response->text[0], false);
	}
	$row = mysqli_fetch_assoc( $res );
	$payment_type_id = $row["payments_type_id"];
	$payment_status = $row["Payments_TransactionState"];

	switch ( $payment_type_id )	
	{
		case 1: $mailOrderSeller = true; break;
		
		case 2: $mailOrderSeller = true; break;
		
		case 3: $mailOrderSeller = true; break;
		
		case 4: if ( $payment_status == "Completed" )
				{
					$mailOrderSeller = true;
				}
				break;

		case 5: if ( $payment_status == "Completed" )
				{
					$mailOrderSeller = true;
				}
				break;

		case 6: if ( $payment_status == "Completed" )
				{
					$mailOrderSeller = true;
				}
				break;

		case 7: $mailOrderSeller = true; break;
			
	}

	// MAIL ZUM SHOP
	if ( $mailOrderSeller )
	{					
		$postfields=array();
		$postfields['API']			= 'shop';
		$postfields['APIRequest']	= 'MailOrderSeller2';
		$postfields['order_id']		= $order_id;
	
		$response = soa2($postfields, __FILE__, __LINE__, "obj");	
		
		if ( (string)$response->Ack[0] != "Success")
		{
			show_error(11362, 7, __FILE__, __LINE__, (string)$response->text[0], false);
		}
	
	}
?>

<script type="text/javascript">

	$( document ).ready(function()
	{
		shop_cart_end_main();
	});

	
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
	if($_SESSION["lang"]=="de" and $_SESSION["id_shop"]==2 and $_SESSION["order_id"]>0 && $_SESSION["id_user"] != 28625 )
	{

		$fieldlist=array();
		//BASISFELDER FÜR API-AUFRUF
		$fieldlist["API"]="shop";
		$fieldlist["APIRequest"]="OrderDetailGet";

		$fieldlist["OrderID"]=$_SESSION["order_id"];
		
		$response = soa2($fieldlist, __FILE__, __LINE__, "obj");
		
		if ($response->Ack[0]!="Success")
		{
			show_error(9756, 7, __FILE__, __LINE__, (string)$response->text[0], false);
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
