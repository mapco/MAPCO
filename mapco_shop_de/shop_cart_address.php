<?php

    include("config.php");

    // hide menu
    $menu_hide = false;
    include("templates/".TEMPLATE."/header.php");
	
	if (isset($_SESSION['id_user']) && !empty($_SESSION['id_user'])) {
		$userId = $_SESSION['id_user'];
	} else {
		$userId = 0;	
	}

    //	keep important session vars for the user 
    $userData = array(
		'ship_country_id' => $_SESSION['ship_country_id'],
        'userId' => $userId,
		'shopId' => $_SESSION['id_shop'],
		'orderId' => '1819478',
		'checkoutGuestOrder' => $_SESSION['ckeckout_guest']
    );
?>

<script type="text/javascript">

	/**
	 *	set shop order billing address
	 */
	function SetShopOrderBillingAddress(adrId, shopId, orderId, userId)
	{
		$('#setorder-billing-' + adrId).click(function(e) {
			var $post_data = new Object();
			$post_data['API'] = "shop";
			$post_data['APIRequest'] = "OrderAddressUpdate_test";

			$post_data['adrId'] = adrId;
			$post_data['addresstype'] = 'bill';
			$post_data['OrderID'] = orderId;
			$post_data['customer_id'] = userId;
			$post_data['shop_id'] = shopId;
			$post_data['country_id'] = 1;
			$post_data['country'] = 'Deutschland';
			$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
				try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
				if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
				document.location.href = '/online-shop/adressen/';
			});
		});
	}

	/**
	 *	set shop order shipping address
	 */
	function SetShopOrderShppingAddress(adrId, shopId, orderId, userId)
	{
		$('#setorder-shipping-' + adrId).click(function(e) {
			var $post_data = new Object();
			$post_data['API'] = "shop";
			$post_data['APIRequest'] = "OrderAddressUpdate_test";

			$post_data['adrId'] = adrId;
			$post_data['addresstype'] = 'ship';
			$post_data['OrderID'] = orderId;
			$post_data['customer_id'] = userId;
			$post_data['shop_id'] = shopId;
			$post_data['country_id'] = 1;
			$post_data['country'] = 'Deutschland';
			$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
				try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
				if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
				document.location.href = '/online-shop/adressen/';
			});
		});
	}

	/**
	 *	update address billing standard status
	 *
	 */
	function SetAddressBillingStandard(adrId, userId)
	{
		if ($("#form-field-billing-standard:checked").val() === undefined) {
			var bill_standard = 0;
		} else {
			var bill_standard = 1;
		}
		var $post_data = new Object();
		$post_data['API'] = "shop";
		$post_data['APIRequest'] = "AddressBill";
		$post_data['action'] = 'set-standard-billing';
		$post_data['user_id'] = userId;
		$post_data['adrId'] = adrId;
		$post_data['bill_standard'] = bill_standard;
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
			document.location.href = '/online-shop/adressen/';
		});
	}

	/**
	 *	update address shipping standard status
	 *
	 */
	function SetAddressShippingStandard(adrId, userId)
	{
		if ($("#form-field-shipping-standard").val() === undefined) {
			var bill_standard_ship_adr = 0;
		} else {
			var bill_standard_ship_adr = 1;
		}
		var $post_data = new Object();
		$post_data['API'] = "shop";
		$post_data['APIRequest'] = "AddressBill";
		$post_data['action'] = 'set-standard-shipping';
		$post_data['user_id'] = userId;
		$post_data['adrId'] = adrId;
		$post_data['bill_standard_ship_adr'] = bill_standard_ship_adr;
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
			document.location.href = '/online-shop/adressen/';
		});
	}

	/**
	 * show address bill list by user id
	 */
	function AddressBillList(userId, shopId, orderId)
	{
		if (userId == "") { return; }

		var $post_data = new Object();
		$post_data['API'] = "shop";
		$post_data['APIRequest'] = "AddressBill";
		$post_data['action'] = 'list';
		$post_data['user_id'] = userId;
		$post_data['orderId'] = orderId;
		$post_data['shopId'] = shopId;
		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}

			if ($xml.find('countBillingAddress').text() == 'noBillingData' && $xml.find('countShippingAddress').text() == 'noShippingData' && $xml.find('countOtherAddress').text() == 'noOtherData') {

				$("#list-address").hide();
				$("#form-add-address").show();
			} else {
				
				if ($xml.find('countBillingAddress').text() == 'noBillingData' && $xml.find('countShippingAddress').text() != 'noShippingData') {
					widgetFieldAddressBoth($xml);
				} else {
					widgetFieldAddressBilling($xml);
					widgetFieldAddressShipping($xml);
				}
				
				widgetFieldAddress($xml, shopId, orderId);

				if ($xml.find('countShippingAddress').text() == 'noShippingData') {
					$('.address-shipping').append('<div class="alert alert-warning">Wählen Sie eine Adresse aus Ihrer Liste aus oder tragen Sie eine Neue ein.</div>');
				}

				if ($xml.find('countBillingAddress').text() == 'noBillingData' && $xml.find('countShippingAddress').text() == 'noShippingData' && $xml.find('countOtherAddress').text() != 'noOtherData') {
					$("#shopcard-address-list").show();
				}

				$("#edit-standard").click(function() {
					$("#shopcard-address-list").toggle('fade');
				});
				$("#edit-shipping").click(function() {
					$("#shopcard-address-list").toggle('fade');
				});
			}
		});

		$("#add-new-address").click(function() {
			$("#form-add-address").show();
			$("#list-address").hide();
		});

		$("#add-new-address-cancel").click(function() {
			$("#form-add-address").hide();
			$("#list-address").show();
		});

		$(".show-more").click(function() {
			$("#shopcard-address-list").toggle('fade');
		});
	}

	/**
	 * save new address bill user id
	 */
	function AddressBillAdd(userId, shopId, orderId)
	{
		var bill_user_id			=	userId;
		var bill_shop_id			=	shopId;
		var bill_company			= 	$("#form-field-bill-company").val();
		var bill_gender				= 	$("#form-field-bill-gender").val();
		var bill_title				= 	$("#form-field-bill-title").val();
		var bill_firstname			=	$("#form-field-bill-firstname").val();
		var bill_lastname			= 	$("#form-field-bill-lastname").val();
		var bill_street				=	$("#form-field-bill-street").val();
		var bill_number				=	$("#form-field-bill-number").val();
		var bill_additional			=	$("#form-field-bill-additional").val();
		var bill_zip				=	$("#form-field-bill-zip").val();
		var bill_city				=	$("#form-field-bill-city").val();
		var bill_country			=	$("#form-field-bill-country").val();
		var bill_country_id			=	$("#form-field-bill-country_id").val();
		var bill_standard_ship_adr	=	$("#form-field-bill-standard-ship").val();

		if ($("#form-field-bill-standard:checked").val() === undefined) {
			var bill_standard = 0;
		} else {
			var bill_standard = 1;
		}

		if ($("#form-field-bill-standard-ship:checked").val() === undefined) {
			var bill_standard_ship_adr = 0;
		} else {
			var bill_standard_ship_adr = 1;
		}

		if (bill_company == "" && bill_firstname == "" && bill_lastname == "") {

			$("#form-field-bill-company").css("border", "1px solid red");
			$("#form-field-bill-firstname").css("border", "1px solid red");
			$("#form-field-bill-lastname").css("border", "1px solid red");
			alert("<?php echo t("Bitte Firma oder Vor- und Nachname ausfüllen!"); ?>");
			return;
		} else {

			$("#form-field-bill-company").css("border", "1px solid green");
			$("#form-field-bill-firstname").css("border", "1px solid green");
			$("#orm-field-bill-lastname").css("border", "1px solid green");
		}

		if (bill_company == "" && bill_firstname == "" ) {
			$("#bill_firstname").css("border", "1px solid red");
			return;
		} else {
			$("#bill_firstname").css("border", "1px solid green");
		}

		if (bill_company == "" && bill_lastname == "" ) {
			$("#bill_lastname").css("border", "1px solid red");
			return;
		} else {
			$("#bill_lastname").css("border", "1px solid green");
		}

		if (bill_street == "") {
			$("#bill_street").css("border", "1px solid red");
			return;
		} else {
			$("#bill_street").css("border", "1px solid green");
		}

		if (bill_number == "") {
			$("#bill_number").css("border", "1px solid red");
			return;
		} else {
			$("#bill_number").css("border", "1px solid green");
		}

		if (bill_zip == "") {
			$("#bill_zip").css("border", "1px solid red");
			return;
		} else {
			$("#bill_zip").css("border", "1px solid green");
		}

		if (bill_city == "") {
			$("#bill_city").css("border", "1px solid red");
			return;
		} else {
			$("#bill_city").css("border", "1px solid green");
		}

		var $post_data = new Object();
		$post_data['API'] = "shop";
		$post_data['APIRequest'] = "OrderAddressUpdate_test";

		$post_data['addresstype'] = 'both';
		$post_data['OrderID'] = orderId;
		$post_data['customer_id'] = bill_user_id;
		$post_data['shop_id'] = bill_shop_id;
		$post_data['company'] = bill_company;
		$post_data['gender'] = bill_gender;
		$post_data['title'] = bill_title;
		$post_data['firstname'] = bill_firstname;
		$post_data['lastname'] = bill_lastname;
		$post_data['street'] = bill_street;
		$post_data['number'] = bill_number;
		$post_data['additional'] = bill_additional;
		$post_data['zip'] = bill_zip;
		$post_data['city'] = bill_city;
		$post_data['country'] = bill_country;
		$post_data['country_id'] = bill_country_id;
		$post_data['standard'] = bill_standard;
		$post_data['standard_ship_adr'] = bill_standard_ship_adr;
		$post_data['active'] =	1;
		$post_data['active_ship_adr'] =	1;

		$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
			try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
			if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
			document.location.href = '/online-shop/zahlungsart/';
		});
	}

	/**
	 * remove address bill adr by adrId and userId
	 */
	function AddressBillRemove(adrId, userId)
	{
		if (confirm("<?php echo t("Wollen Sie die Adresse wirklich löschen?"); ?>"))
		{
			var $post_data = new Object();
			$post_data['API'] = "shop";
			$post_data['APIRequest'] = "AddressBill";
			$post_data['action'] = 'remove';
			$post_data['adrId'] = adrId;
			$.post('<?php echo PATH;?>soa2/', $post_data, function($data) {
				try {$xml = $($.parseXML($data));} catch($err) {show_status2($err.message); return;}
				if ($xml.find('Ack').text() != 'Success') {show_status2($data); return;}
					$('#address-bill-' + adrId).hide();
			});
		}
	}

</script>

<?php

	//	address list
	echo '
	<div id="list-address" class="row">
			<div id="shopcard-adress" class="shopcard-wrapper">
			<h1 id="shopcard_title" class="shopcard-heading">' . t('Adresse') . '</h1>
			<div>';
			echo getShopcardAssistent($userData);
			echo '
				<div id="shopcard-address-list-current" class="shopcard-field-address first-element">
					<div class="field-wrapper-block">
						<div class="address-billing left"></div>
						<div class="address-shipping right"></div>

						<div class="clear"></div>
					</div>
				</div>

				<div class="show-more">
					<span>' . t('weitere Adressen von mir anzeigen') . '</span>
					<i class="fa fa-angle-double-down"></i>
				</div>

				<div id="shopcard-address-list" class="shopcard-field-address" style="display:none">
					<div class="add-container">
						<button id="add-new-address" class="btn btn-add btn-right">' . t('Neue Ardresse anlegen') . '</button>
						<div class="clear"></div>
					</div>
				</div>
			</div>
		</div>
	</div>';

	// if order a guest order
	if ($userData['checkoutGuestOrder'] == 1)
	{
		echo '
		<div id="form-add-address" class="row" style="display:none">
			<div id="shopcard-adress" class="shopcard-wrapper">
				<h1 id="shopcard_title" class="shopcard-heading">' . t('Neue Ardresse anlegen') . '</h1>
				<div class="shopcard-field-address">

					<div class="field-wrapper-block">
						<div class="field-wrapper left">
							<label for="form-field-bill-firstname">
								' . t('Vorname') . '<span class="required">*</span>
								<small class="text-warning">' . t('Geben sie hier ihren Vornamen an') . '</small>
							</label>
							<div class="input-group">
									<input id="form-field-bill-firstname" class="form-control" type="text">
							</div>
						</div>

						<div class="field-wrapper right">
							<label for="form-field-bill-lastname">
								' . t('Nachname') . '<span class="required">*</span>
								<small class="text-warning">' . t('Geben sie hier ihren Nachnamen an') . '</small>
							</label>
							<div class="input-group">
									<input id="form-field-bill-lastname" class="form-control" type="text">
							</div>
						</div>

						<div class="clear"></div>
					</div>

					<hr>

					<div class="field-wrapper-block">

						<div class="field-wrapper left">
							<label for="form-field-bill-street">
								' . t('Strasse') . '<span class="required">*</span>
								<small class="text-warning">' . t('Geben sie hier ihre Strasse an') . '</small>
							</label>
							<div class="input-group">
									<input id="form-field-bill-street" class="form-control" type="text">
							</div>
						</div>

						<div class="field-wrapper right">
							<label for="form-field-bill-number">
								' . t('Hausnummer') . '<span class="required">*</span>
								<small class="text-warning">' . t('Geben sie hier ihre Hausnummer an') . '</small>
							</label>
							<div class="input-group">
									<input id="form-field-bill-number" class="form-control input-mask-number" type="text">
							</div>
						</div>

						<div class="clear"></div>
					</div>

					<hr>

					<div class="field-wrapper-block">

						<div class="field-wrapper left">
							<label for="form-field-bill-zip">
								' . t('Postleizahl') . '<span class="required">*</span>
								<small class="text-warning">' . t('Geben sie hier ihre Postleitzahl an') . '</small>
							</label>
							<div class="input-group">
									<input id="form-field-bill-zip" class="form-control input-mask-zip" type="text">
							</div>
						</div>

						<div class="field-wrapper right">
							<label for="form-field-bill-city">
								' . t('Ort') . '<span class="required">*</span>
								<small class="text-warning">' . t('Geben sie hier den Ort an') . '</small>
							</label>
							<div class="input-group">
									<input id="form-field-bill-city" class="form-control" type="text">
							</div>
						</div>

						<div class="clear"></div>
					</div>

					<hr>

					<div class="field-wrapper">
						<label for="form-field-bill-country">
							' . t('Land') . '
							<small class="text-warning"' . t('Geben sie hier ihr Land an') . '></small>
						</label>
						<div class="input-group">';
					echo getSelectShopCountries($userData);
					echo '
						</div>
					</div>

					<hr>

					<div class="field-wrapper-block">

						<div class="field-wrapper left"></div>

						<div class="field-wrapper right">
							<button id="add-new-address-save" onclick="AddressBillAdd(' . $userData['userId'] . ', ' . $userData['shopId'] . ', ' . $userData['orderId'] . ');" class="btn btn-add">' . t('Speichern') . '</button>
							<button id="add-new-address-cancel" class="btn btn-add">' . t('Abbrechen') . '</button>
						</div>

						<div class="clear"></div>
					</div>
				</div>
			</div>
		</div>';
	}

	if ($userData['guestOrder'] == false)
	{
		//	add and edit form for address
		echo '
			<div id="form-add-address" class="row" style="display:none">
				<div id="shopcard-adress" class="shopcard-wrapper">
					<h1 id="shopcard_title" class="shopcard-heading">' . t('Neue Ardresse anlegen') . '</h1>
					<div class="shopcard-field-address">

						<div class="field-wrapper">
							<label for="form-field-bill-company">
								' . t('Firma') . '<span class="required">*</span>
								<small class="text-warning">' . t('Geben sie hier ihre Firmenbezeichnung an') . '</small>
							</label>
							<div class="input-group">
									<input id="form-field-bill-company" class="form-control" type="text">
							</div>
						</div>

						<hr>

						<div class="field-wrapper-block">
							<div class="field-wrapper left">
								<label for="form-field-bill-gender">
									' . t('Anrede') . '
								</label>
								<div class="input-group">
									<select id="form-field-bill-gender" class="form-control">
										<option value="0" selected="selected">' . t('Herr') . '</option>
										<option value="1">' . t('Frau') . '</option>
									</select>
								</div>
							</div>

							<div class="field-wrapper right">
								<label for="form-field-bill-title">
									' . t('Title') . '
								</label>
								<div class="input-group">
									<input id="form-field-bill-title" class="form-control" type="text">
								</div>
							</div>

							<div class="clear"></div>
						</div>

						<hr>

						<div class="field-wrapper-block">
							<div class="field-wrapper left">
								<label for="form-field-bill-firstname">
									' . t('Vorname') . '<span class="required">*</span>
									<small class="text-warning">' . t('Geben sie hier ihren Vornamen an') . '</small>
								</label>
								<div class="input-group">
									<input id="form-field-bill-firstname" class="form-control" type="text">
								</div>
							</div>

							<div class="field-wrapper right">
								<label for="form-field-bill-lastname">
									' . t('Nachname') . '<span class="required">*</span>
									<small class="text-warning">' . t('Geben sie hier ihren Nachnamen an') . '</small>
								</label>
								<div class="input-group">
										<input id="form-field-bill-lastname" class="form-control" type="text">
								</div>
							</div>

							<div class="clear"></div>
						</div>

						<hr>

						<div class="field-wrapper-block">

							<div class="field-wrapper left">
								<label for="form-field-bill-street">
									' . t('Strasse') . '<span class="required">*</span>
									<small class="text-warning">' . t('Geben sie hier ihre Strasse an') . '</small>
								</label>
								<div class="input-group">
										<input id="form-field-bill-street" class="form-control" type="text">
								</div>
							</div>

							<div class="field-wrapper right">
								<label for="form-field-bill-number">
									' . t('Hausnummer') . '<span class="required">*</span>
									<small class="text-warning">' . t('Geben sie hier ihre Hausnummer an') . '</small>
								</label>
								<div class="input-group">
										<input id="form-field-bill-number" class="form-control input-mask-number" type="text">
								</div>
							</div>

							<div class="clear"></div>
						</div>

						<hr>

						<div class="field-wrapper">
							<label for="form-field-bill-additional">
								' . t('Adresszusatz') . '
								<small class="text-warning">' . t('Geben sie hier einen Adresszusatz an, wenn erforderlich') . '</small>
							</label>
							<div class="input-group">
									<input id="form-field-bill-additional" class="form-control" type="text">
							</div>
						</div>

						<hr>

						<div class="field-wrapper-block">

							<div class="field-wrapper left">
								<label for="form-field-bill-zip">
									' . t('Postleitzahl') . '<span class="required">*</span>
									<small class="text-warning">' . t('Geben sie hier ihre Postleitzahl an') . '</small>
								</label>
								<div class="input-group">
										<input id="form-field-bill-zip" class="form-control input-mask-zip" type="text">
								</div>
							</div>

							<div class="field-wrapper right">
								<label for="form-field-bill-city">
									' . t('Ort') . '<span class="required">*</span>
									<small class="text-warning">' . t('Geben sie hier den Ort an') . '</small>
								</label>
								<div class="input-group">
										<input id="form-field-bill-city" class="form-control" type="text">
								</div>
							</div>

							<div class="clear"></div>
						</div>

						<hr>

						<div class="field-wrapper">
							<label for="form-field-bill-country">
								' . t('Land') . '
								<small class="text-warning">' . t('Geben sie hier ihr Land an') . '</small>
							</label>
							<div class="input-group">';
						echo getSelectShopCountries($userData);
						echo '
							</div>
						</div>

						<hr>

						<div class="field-wrapper-block">

							<div class="field-wrapper left">
								<div class="input-group">
									<input id="form-field-bill-standard" class="shopcard" type="checkbox" checked="checked" value="1">
									<span>' . t('Als Standard Rechnungsandresse festlegen!') . '</span>
								</div>
								<div class="input-group">
									<input id="form-field-bill-standard-ship" class="shopcard" type="checkbox" checked="checked" value="1">
									<span>' . t('und als Lieferanschrift verwenden!') . '</span>
								</div>
							</div>

							<div class="field-wrapper right">
								<button id="add-new-address-save" onclick="AddressBillAdd(' . $userData['userId'] . ', ' . $userData['shopId'] . ', ' . $userData['orderId'] . ');" class="btn btn-add">' . t('Speichern') . '</button>
								<button id="add-new-address-cancel" class="btn btn-add">' . t('Abbrechen') . '</button>
							</div>

							<div class="clear"></div>
						</div>
					</div>
				</div>
			</div>';
	}

	//	loading....
	echo '<script src="' . PATH . 'javascript/shop/AddressBill.php" type="text/javascript"></script>';
	echo '<script type="text/javascript">AddressBillList(' . $userData['userId'] . ', ' . $userData['shopId'] . ', ' . $userData['orderId'] . ');</script>';
	include("templates/".TEMPLATE."/footer.php");

/**
 *	--------------------------------------------------- function -----------------------------------------------------
 *
 */


function getShopcardAssistent($userData)
{
	if (!empty($userData['userId'])) {
		$payment = '<li><em>2.</em>' . t('Zahlungsart') . '</li>';
	} else {
		$payment = '<li>' . t('Zahlungsart') . '</li>';	
	}
	$html ='
	<div class="widget-shopcard-assistent">
		<ul>
			<li class="active"><em>1.</em>' . t('Adresse') . '</li>';
		$html.= $payment;
		$html.= '
			<li><em>3.</em>' . t('Versandart') . '</li>
			<li><em>4.</em>' . t('Bestellübersicht') . '</li>
		</ul>
		<div class="clear"></div>
	</div>';
	return $html;
}

/**
 * Returns a shop countries select field
 *
 * @param $userData
 * @return string
 */
function getSelectShopCountries($userData)
{
	$field = array();
	$field['from'] = 'shop_payment';
	$field['select'] = 'country_id';
	$addWhere = "shop_id=" . $userData['shopId'];
	$shopPayments = SQLSelect($field['from'], $field['select'], $addWhere, 0, 0, 0, 'shop',  __FILE__, __LINE__);
	foreach($shopPayments as $shopPayment)
	{
		$shop_countries[$shopPayments["country_id"]] = $shopPayment["country_id"];
	}

	$field = array();
	$field['from'] = 'shop_countries';
	$field['select'] = '*';
	$field['orderBy'] = 'ordering';
	$shopCountries = SQLSelect($field['from'], $field['select'], 0, $field['orderBy'], 0, 0, 'shop',  __FILE__, __LINE__);
    $option = "";
    foreach($shopCountries as $shopCountry)
	{
		if (isset($shop_countries[$shopCountry["id_country"]]) || ($userData['shopId'] != 2 && $userData['shopId'] != 22))
		{
			if ($userData['ship_country_id'] == $shopCountry["id_country"]) {
				$selected = ' selected="selected"';
			} else {
				$selected = '';
			}
			$option.= '<option' . $selected . ' value="' . $shopCountry["id_country"] . '">' . t($shopCountry["country"]) . '</option>';
		}
	}
	$html = '<select id="form-field-bill-country_id" class="form-control">' . $option . '</select>';
	return  $html;
}
