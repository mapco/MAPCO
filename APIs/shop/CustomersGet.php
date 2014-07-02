<?php

	$xml='';
	
	if( isset($_POST["id_user"]) )
	{
		if( $_POST["id_user"]=="" ) $_POST["id_user"]=0;
		$results=q("SELECT id_user, username, name FROM cms_users WHERE id_user IN (".$_POST["id_user"].");", $dbweb, __FILE__, __LINE__);
	}
	else
	{
		$results=q("SELECT id_user, username, name FROM cms_users;", $dbweb, __FILE__, __LINE__);
	}
	
	while($row=mysqli_fetch_array($results))
	{
		$xml.='<customer>'."\n";
		$xml.='		<user_id>'.$row["id_user"].'</user_id>'."\n";
		$xml.='		<username><![CDATA['.$row["username"].']]></username>'."\n";
		$xml.='		<name><![CDATA['.$row["name"].']]></name>'."\n";
		$xml.='</customer>'."\n";
	}

	echo $xml;

?>