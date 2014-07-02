<?php

	$xml='';
	$xml.='<contacts>'."\n";
	
	$results=q("SELECT * FROM cms_contacts_locations WHERE site_id=".$_SESSION["id_site"]." ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$results2=q("SELECT * FROM cms_contacts_departments WHERE location_id=".$row["id_location"]." ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
		while($row2=mysqli_fetch_array($results2))
		{
			$results3=q("SELECT * FROM cms_contacts WHERE department_id=".$row2["id_department"]." AND active=1 ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
			while($row3=mysqli_fetch_array($results3))
			{
				$xml.='	<contact>'."\n";
				$xml.='		<id_location>'.$row["id_location"].'</id_location>'."\n";
				$xml.='		<location><![CDATA['.$row["location"].']]></location>'."\n";
				$xml.='		<id_department>'.$row2["id_department"].'</id_department>'."\n";
				$xml.='		<department><![CDATA['.$row2["department"].']]></department>'."\n";
				$xml.='		<id_contact>'.$row3["id_contact"].'</id_contact>'."\n";
				$xml.='		<firstname><![CDATA['.$row3["firstname"].']]></firstname>'."\n";
				$xml.='		<lastname><![CDATA['.$row3["lastname"].']]></lastname>'."\n";
				$xml.='		<id>'.$row3["id_contact"].'</id>'."\n";
				$xml.='	</contact>'."\n";
			}
		}
	}
	
	$xml.='</contacts>'."\n";
	
	echo $xml;

?>