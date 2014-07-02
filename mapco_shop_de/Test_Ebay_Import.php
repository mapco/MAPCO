<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
?>
<script type="text/javascript">
</script>

<?php

	if (isset($_POST["send_message"]))
	{
		$response=post(PATH."soa2/", array("API" => "crm", "APIRequest" => "EbayOrderImport", "EbayOrderID" => $_POST["ebayorderid"]));

		echo '<p><textarea name="response" cols="40" rows="20">'.$response.'</textarea></p>';

	}


	echo '<form action="'.PATH.'Test_Ebay_Import.php" method="POST">';
	echo '<input name="ebayorderid" type="text" size="20" />';
	echo '<input type="submit" name="send_message" value="Test" />';
	echo '</form>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");

