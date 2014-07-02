<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

?>

<script language="javascript">

	function idims_send()
	{
		var $xml=$("#orderxml").val();
		$.post("<?php echo PATH; ?>soa/", { API:"idims", Action:"PurchaseOrderSend", orderxml:$xml  },
			function($data)
			{
				show_status2($data);
			}
		);
	}
</script>

<?php
	//PATH
	echo '<p>'."\n";
	echo '<a href="backend_index.php">Backend</a>'."\n";
	echo ' > <a href="backend_shop_index.php">Online-Shop</a>'."\n";
	echo ' > IDIMS-Test'."\n";
	echo '</p>'."\n";


	//HEADLINE
	echo '<h1>IDIMS-Test'."\n";
	echo '<br />';
	echo '<br />';
	echo '<input type="button" onclick="idims_send(0);" value="Auftrag senden" />';
	echo '<br />';
	echo '<br />';

	echo '<textarea id="orderxml" style="width:600px; height:400px;">';
	echo '
<?xml version="1.0" encoding="utf-8"?>
<Auftrag>
  <Rechnungsanschrift>
    <KUN_NR>12345</KUN_NR>
    <KUN_ANDREDE>Herr Dr.</KUN_ANDREDE>
    <KUN_NAME_1>Mustermann</KUN_NAME_1>
    <KUN_NAME_2>Mustermann Autoteile</KUN_NAME_2>
    <KUN_STR_1>Musterstraße 17</KUN_STR_1>
    <KUN_STR_2></KUN_STR_2>
    <KUN_PLZ>12345</KUN_PLZ>
    <KUN_ORT>Musterstadt</KUN_ORT>
    <KUN_LAND>Musterland</KUN_LAND>
    <KUN_TEL_1>0123 456789</KUN_TEL_1>
    <KUN_EMAIL>mustermann@mustermail.mu</KUN_EMAIL>
  </Rechnungsanschrift>
  <Lieferanschrift>
    <ADR_ANREDE>Firma</ADR_ANREDE>
    <ADR_NAME_1>Mustermann Autoteile</ADR_NAME_1>
    <ADR_NAME_2></ADR_NAME_2>
    <ADR_STR_1></ADR_STR_1>
    <ADR_STR_2></ADR_STR_2>
    <ADR_PLZ></ADR_PLZ>
    <ADR_ORT></ADR_ORT>
    <ADR_LAND></ADR_LAND>
    <ADR_TEL_1></ADR_TEL_1>
    <ADR_EMAIL></ADR_EMAIL>
  </Lieferanschrift>
  <Positionen>
    <POS_ART_NR>59818HPS/1</POS_ART_NR>
    <POS_MENGE>10</POS_MENGE>
    <POS_WERT>359.74</POS_WERT>
    <POS_ART_NR>76819</POS_ART_NR>
    <POS_MENGE>1</POS_MENGE>
    <POS_WERT>9.52</POS_WERT>
  </Positionen>
  <Frachtkosten>
    <AUF_FR_WERT>12.90</AUF_FR_WERT>
    <AUF_FR_TXT>DHL Express</AUF_FR_TXT>
  </Frachtkosten>
</Auftrag>
';
	echo '</textarea>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>