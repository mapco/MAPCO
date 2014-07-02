<?php
	
	check_man_params(array( "request_uri" => "text" ));
	
	$check=0;
	
	//get scriptname
	$scriptname=$_POST["request_uri"];
	if(strpos($scriptname, '.php')==false)
	{
		$scriptname=substr($scriptname, 1);
		$results=q("SELECT * FROM cms_menuitems WHERE alias='".$scriptname."';", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$scriptname=$row["link"];
	}
	else
	{
		$scriptname=substr($scriptname, strrpos($scriptname, "/")+1);
		$scriptname=substr($scriptname, 0, strrpos($scriptname, ".php")+4);
	}
	
	//get user userrole_id
	$results2=q("SELECT * FROM cms_users WHERE id_user=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
	$row2=mysqli_fetch_array($results2);
	$user_userrole_id=$row2["userrole_id"];
	
	//get script userrole_id and check
	$results3=q("SELECT * FROM cms_userroles_scripts WHERE userrole_id=".$user_userrole_id." AND script='".$scriptname."';", $dbweb, __FILE__, __LINE__);
	if(mysqli_num_rows($results3)>0) $check=1;

	$xml='<userrole_check><![CDATA['.$check.']]></userrole_check>';
	
	echo $xml;
	
?>