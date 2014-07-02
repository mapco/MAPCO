<?php
	session_start();
	if (($_GET["id_item"]>0) and ($_GET["amount"]>0))
	{
		include("../config.php");
		if (isset($_SESSION["id_user"]))
		{
			$results=q("SELECT * FROM shop_carts WHERE item_id=".$_GET["id_item"]." AND user_id='".$_SESSION["id_user"]."';", $dbshop, __FILE__, __LINE__);
		}
		else
		{
			$results=q("SELECT * FROM shop_carts WHERE item_id=".$_GET["id_item"]." AND session_id='".session_id()."';", $dbshop, __FILE__, __LINE__);
		}
		if (mysql_num_rows($results)>0)
		{
			$row=mysql_fetch_array($results);
			$results=q("UPDATE shop_carts SET amount='".($row["amount"]+$_GET["amount"])."' WHERE id_carts='".$row["id_carts"]."';", $dbshop, __FILE__, __LINE__);
			echo 'Die Menge wurde erfolgreich aktualisiert..';
		}
		else
		{
			$results=q("INSERT INTO shop_carts (item_id, amount, session_id, user_id) VALUES('".$_GET["id_item"]."', '".$_GET["amount"]."', '".session_id()."', '".$_SESSION["id_user"]."');", $dbshop, __FILE__, __LINE__);
			echo 'Die Ware wurde erfolgreich in den Warenkorb gelegt..';
		}
	} else echo 'ERROR: Item not found!';
?>