<?php

	$date_now=time();

	$sql="UPDATE cms_conversations set last_mod_date = '".$date_now."', end_date = '".$date_now."', state = 'closed' where id_conv = '".$_POST["conv_id"]."'";
	q($sql, $dbweb, __FILE__, __LINE__);	


?>