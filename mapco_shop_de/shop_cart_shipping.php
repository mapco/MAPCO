<?php

    include("config.php");
	include("functions/cms_core.php");

    // hide menu
    $menu_hide = false;
    include("templates/".TEMPLATE."/header.php");
	
	//	keep post submit
	$post = $_POST;
		
	//	keep important session and post vars for the user
    $userData = array(
        'userId' => $_SESSION['id_user'],
		'shopId' => $_SESSION['id_shop'],
		'orderId' => '1819478',
		'paymentId' => $_SESSION['paymentId'],
		'shipCountryId' => $_SESSION['ship_country_id'],
		'billCountryId' => $_SESSION['bill_country_id']
    );
?>
	
<script type="text/javascript">

	/**
	 * show shop shipping methods by shopId and countryId and paymentId
	 */
	function ShopShippingMethods(userId, orderId, paymentId)
	{
		if (userId == "") { return; }
		
		var $post_data = new Object();
		$post_data['API'] = "shop";
		$post_data['APIRequest'] = "CheckoutShipping";
		$post_data['action'] = 'ShopShipping';
		$post_data['OrderID'] = orderId;
		$post_data['PaymentID'] = paymentId;
		$post_data['customer_id'] = userId;
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
			
			if ($xml.find('Jump').text() == 'Success') {
				$('#shopcard-payment-shipping').empty();
				document.location.href = '/online-shop/kasse/';
			} else {
				buttonsShopShippingsMethods($xml);
			}
		});
	}
	
	/**
	 *	shop shipping method select by shipping id
	 */
	function ShopShippingMethodSelect(shippingId)
	{
		$('#setorder-shipping-' + shippingId).bind("click", function() {
			var $post_data = new Object();
			$post_data['API'] = "shop";
			$post_data['APIRequest'] = "CheckoutShipping";
			$post_data['action'] = 'SetShopShippingMethod';
			$post_data['shippingId'] = shippingId;
			$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
				try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
				if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
				$('#shopcard-shipping-list').empty();
				document.location.href = '/online-shop/kasse/';
			});
		});
	}	
</script>

<?php
	//	shipping methods
	echo '
	<div id="form-add-shipping" class="row">
		<div id="shopcard-payment-shipping" class="shopcard-wrapper">
			<h1 id="shopcard_title" class="shopcard-heading">Zahlungsart</h1>
			
			<div class="widget-shopcard-assistent">
				<ul>
					<li><a href="/online-shop/adressen/"><em>1.</em> Adresse</a></li>
					<li><a href="/online-shop/zahlungsart/"><em>2.</em> Zahlungsart</a></li>
					<li class="active"><em>3.</em> Versandart</li>
					<li><a href="/online-shop/kasse/"><em>4.</em> Bestätigen</a></li>
				</ul>
				<div class="clear"></div>
			</div>
			
			<div class="shopcard-field-address">
			
				<div class="field-wrapper">
					<div id="shopcard-shipping-list"></div>
				</div>
				
				<hr>
				
				<div class="field-wrapper">
					<button id="add-new-address-save" onclick="" class="btn btn-add">zur Kasse</button>
				</div>
				
			</div>
				
		</div>
	</div>';

	//	loading....
	echo '<script src="' . PATH . 'javascript/shop/Shippings.php" type="text/javascript"></script>';
	echo '<script type="text/javascript">ShopShippingMethods(' . $userData['userId'] . ', ' . $userData['orderId'] . ', ' . $userData['paymentId'] . ');</script>';
	include("templates/".TEMPLATE."/footer.php");