<?php
	include("config.php");
	include("functions/shop_mail_order3.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

$counter=0;
	$res=q("SELECT * FROM shop_orders WHERE id_order = 1740356", $dbshop, __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($res))
	{
		echo $row["id_order"]."<br />";
		mail_order2($row["id_order"], false, true);
		$counter++;
	}
echo $counter;

	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>
