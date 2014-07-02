<?php
/*
	$sql = "SELECT * FROM  `crm_costumer_lists`";
	if ($_SESSION["userrole_id"]!=1) 
	{
		$sql .= " WHERE type!=1 OR ( type=1 AND firstmod_user=".$_SESSION['user_id']." )";
	}
	$sql .= ';';
	
	$res=q($sql, $dbweb, __FILE__, __LINE__);
	while($row = mysqli_fetch_assoc($res))
	{
		$lists[$row['type']]=$row;
	}
	
	print_r($lists);
*/

?>