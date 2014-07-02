<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>

	<script type="text/javascript">
		function htmlentities(str) {
			return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
		}

		function view(id_account)
		{
			wait_dialog_show();
			$.post("<?php echo PATH; ?>soa/", { API:"ebay", Action:"GetPromotionalSalesDetails", id_account:id_account },
				function(data)
				{
					$("#view").append('<table>');
					$xml = $($.parseXML(data));
					$xml.find("PromotionalSale").each(
						function()
						{
							$id = $(this).find("PromotionalSaleID");
							$title = $(this).find("PromotionalSaleName");
							$("#view").append('<tr><td>'+$id.text()+'</td><td>'+$title.text()+'</td></tr>');
						}
					);
					$("#view").append('</table>');
					wait_dialog_hide();
				}
			);
		}
	</script>

<?php
	
	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_ebay_index.php">eBay</a>';
	echo ' > Accounts';
	echo '</p>';

	echo '<h1>eBay-Shop-Verkaufsaktionen</h1>';
	echo '<div id="view"></div>';
	echo '<script> view(1); </script>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>