<?php

	$xmldata='';
	
	$results=q("SELECT * FROM cms_menuitems WHERE menu_id=5;", $dbweb, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($results))
	{
		if($row["menuitem_id"]==0)
		{
			$xmldata.="	<group>\n";
			$xmldata.="		<type><![CDATA[main_group]]></type>\n";
			$xmldata.="		<id_menuitem>".$row["id_menuitem"]."</id_menuitem>\n";
			$xmldata.="		<menuitem_id>".$row["menuitem_id"]."</menuitem_id>\n";
			$xmldata.="		<title><![CDATA[".$row["title"]."]]></title>\n";
			$xmldata.="	</group>\n";
		}
		elseif($row["menuitem_id"]!=0)
		{
			$xmldata.="	<group>\n";
			$xmldata.="		<type><![CDATA[sub_group]]></type>\n";
			$xmldata.="		<id_menuitem>".$row["id_menuitem"]."</id_menuitem>\n";
			$xmldata.="		<menuitem_id>".$row["menuitem_id"]."</menuitem_id>\n";
			$xmldata.="		<title><![CDATA[".$row["title"]."]]></title>\n";
			$xmldata.="	</group>\n";
		}
	}

	echo $xmldata;

?>
