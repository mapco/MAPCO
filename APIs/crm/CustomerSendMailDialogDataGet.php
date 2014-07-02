<?php

	/*************************
	********** SOA 2 *********
	*************************/
	
	$res3 = q( "SELECT * FROM shop_listtypes WHERE id_listtype!=5 ORDER BY ordering", $dbshop, __FILE__, __LINE__ );
	while ( $shop_listtypes = mysqli_fetch_assoc( $res3 ) ) 
	{
		print '<shop_list>'."\n";
		print '	<shop_list_group><![CDATA['. $shop_listtypes[ 'title' ] . ']]></shop_list_group>'."\n";
		if ( $shop_listtypes[ 'id_listtype' ] == 2 ) 
		{
			$res4 = q( "SELECT * FROM shop_lists WHERE listtype_id=" . $shop_listtypes[ 'id_listtype' ] . " AND firstmod_user=" . $_SESSION[ 'id_user' ] . " ORDER BY title", $dbshop, __FILE__, __LINE__ );
		}
		else
		{
			$res4 = q( "SELECT * FROM shop_lists WHERE listtype_id=" . $shop_listtypes[ 'id_listtype' ] . " ORDER BY title", $dbshop, __FILE__, __LINE__ );
		}
		while ( $shop_lists = mysqli_fetch_assoc( $res4 ) ) 
		{
			print '	<shop_list_item>'."\n";
			print '		<id>'.$shop_lists[ 'id_list' ].'</id>'."\n";
			print '		<title><![CDATA[' . $shop_lists[ 'title' ] . ']]></title>'."\n";
			print '	</shop_list_item>'."\n";
		}
		print '</shop_list>'."\n";
	}
	
	$res=q("SELECT * FROM cms_contacts WHERE idCmsUser = ".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
	if (mysqli_num_rows($res)==1)
	{ 
		$row=mysqli_fetch_array($res);
		$copyTo=$row["mail"];
	}
	else 
	{ 
		$copyTo="";
	}
	print '		<copyTo><![CDATA['.$copyTo.']]></copyTo>'."\n";
?>