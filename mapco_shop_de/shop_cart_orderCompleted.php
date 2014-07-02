<?php
	session_start();
	include("config.php");
	$login_required=true;
	$title="Bestellbestätigung";
	include("templates/".TEMPLATE."/header.php");
?>

<script type="text/javascript">
	
	function vieworder(order_id)
	{

		$.post("<?php echo PATH; ?>APIs/shop/shop_cart_orderView.php", { order_id:order_id }, 
			function(data)
			{

			//	$("#view_order").text(data);
			//	alert(data);
				var $xml=$($.parseXML(data));
				var Ack = $xml.find("Ack").text();
				if (Ack=="Success") {
					$("#view_order").html($xml.find("Response").text());
				}
			}
		);
	
	}


</script>

<?php
	echo '<div id="left_mid_right_column">';

	echo '<div id="view_order" style="border-style:solid; border-width:2px; border-radius:8px; padding:15px; margin-top:15px;"></div>';

	echo '<script> vieworder('.$_SESSION["id_order"].'); </script>';

	echo '</div>';


	include("templates/".TEMPLATE."/footer.php");
?>