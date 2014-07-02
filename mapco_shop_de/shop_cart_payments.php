<?php

    include("config.php");
	include("functions/cms_core.php");

    // hide menu
    $menu_hide = false;
    include("templates/".TEMPLATE."/header.php");
	
	//	keep important session vars for the user
    $userData = array(
        'userId' => $_SESSION['id_user'],
		'shopId' => $_SESSION['id_shop'],
		'orderId' => '1819478',
		'shipCountryId' => $_SESSION['ship_country_id'],
		'billCountryId' => $_SESSION['bill_country_id']
    );
?>
	
<script type="text/javascript">

	/**
	 * show shop payments methods by shopId and countryId
	 */
	function ShopPaymentMethods(userId, orderId)
	{
		if (userId == "") { return; }
		
		var $post_data = new Object();
		$post_data['API'] = "shop";
		$post_data['APIRequest'] = "CheckoutPayment";
		$post_data['action'] = 'ShopPayment';
		$post_data['OrderID'] = orderId;
		$post_data['customer_id'] = userId;
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
			
			buttonsShopPaymentsMethods($xml);
		});
	}
	
	/**
	 *	shop payment method select by payment id
	 */
	function ShopPaymentMethodSelect(paymentId)
	{
		$('#setorder-payment-' + paymentId).bind("click", function() {
			var $post_data = new Object();
			$post_data['API'] = "shop";
			$post_data['APIRequest'] = "CheckoutPayment";
			$post_data['action'] = 'SetShopPaymentMethod';
			$post_data['paymentId'] = paymentId;
			$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
				try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
				if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
				$('#shopcard-shipping-list').empty();
				document.location.href = '/online-shop/versandart/';
			});
		});
	}
</script>
	
<?php    
	//	payment types
	echo '
	<div id="form-add-payment" class="row">
		<div id="shopcard-payment-shipping" class="shopcard-wrapper">
			<h1 id="shopcard_title" class="shopcard-heading">Zahlungsart</h1>
			
			<div class="widget-shopcard-assistent">
				<ul>
					<li><a href="/online-shop/adressen/"><em>1.</em> Adresse</a></li>
					<li class="active"><em>2.</em> Zahlungsart</li>
					<li><a href="/online-shop/versandart/"><em>3.</em> Versandart</a></li>
					<li><em>4.</em> Bestätigen</li>
				</ul>
				<div class="clear"></div>
			</div>
			
			<div class="shopcard-field-address">
			
				<div class="field-wrapper">
					<div id="shopcard-payment-list"></div>
				</div>
			</div>
				
		</div>
	</div>';

	//	loading....
	echo '<script src="' . PATH . 'javascript/shop/Payments.php" type="text/javascript"></script>';
	echo '<script type="text/javascript">ShopPaymentMethods(' . $userData['userId'] . ', ' . $userData['orderId'] . ');</script>';
	include("templates/".TEMPLATE."/footer.php");