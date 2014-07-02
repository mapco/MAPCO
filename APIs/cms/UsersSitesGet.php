<?php
	//SOA2 SERVICE

	$required=array("user_id" => "numericNN");

	check_man_params($required);


	echo '<user_sites>';
	$res = q("SELECT * FROM cms_users_sites WHERE user_id = ".$_POST["user_id"], $dbweb, __FILE__, __LINE);
	while ($row = mysqli_fetch_assoc($res))
	{
		echo '	<site_id>'.$row["site_id"].'</site_id>';
	}
	echo '</user_sites>';

?>