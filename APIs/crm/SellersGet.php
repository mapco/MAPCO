<?php

	$xmldata="";

	$res_cms_user=q("SELECT a.id_user, b.firstname, b.lastname FROM cms_users as a, cms_contacts as b WHERE a.userrole_id IN (1,3,4) AND b.idCmsUser = a.id_user;", $dbweb, __FILE__, __LINE__);
	while ($row_cms_user=mysqli_fetch_array($res_cms_user))
	{
		$xmldata.="	<seller>\n";
		$xmldata.="		<id_user>".$row_cms_user["id_user"]."</id_user>\n";
		$xmldata.="		<firstname><![CDATA[".$row_cms_user["firstname"]."]]></firstname>\n";
		$xmldata.="		<lastname><![CDATA[".$row_cms_user["lastname"]."]]></lastname>\n";
		$xmldata.="	</seller>\n";
	}

	echo "<SellersGetResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "<Entries>".$entriesCount."</Entries>\n";
	echo "<SellerList>\n";
		echo $xmldata;
	echo "</SellerList>\n";
	echo "</SellersGetResponse>";

?>