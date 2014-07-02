<?php
	$results=q("SELECT * FROM cms_users LIMIT 1;", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$keys=array_keys($row);

	$query="SELECT * FROM cms_users";
	if( isset($_POST["id_user"]) and $_POST["id_user"]!="" )
	{
		if( strpos($query, "WHERE") === false ) $query .= " WHERE "; else $query .= " AND ";
		$query.="id_user=".$_POST["id_user"]."";
	}
	$query.=";";
	$results=q($query, $dbweb, __FILE__, __LINE__);
	
	echo '<UserGetResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	while($row=mysqli_fetch_array($results))
	{
		for($i=0; $i<sizeof($keys); $i++)
		{
			if( !is_numeric($keys[$i]) )
				echo '	<'.$keys[$i].'><![CDATA['.$row[$keys[$i]].']]></'.$keys[$i].'>'."\n";
		}
	}
	//sites of user
	echo '	<Sites>'."\n";
	$results=q("SELECT * FROM cms_users_sites WHERE user_id=".$_POST["id_user"].";", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		echo '		<Site>'.$row["site_id"].'</Site>'."\n";
	}
	echo '	</Sites>'."\n";
	echo '</UserGetResponse>'."\n";

?>