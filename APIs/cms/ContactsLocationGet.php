<?php
	/**************************************************
	*** Author: C.Haendler <chaendler(at)mapco.de> 	*** 
	***	Version: 1.0 (SOA2) 27/06/14/ 				***
	***	Last mod: 27/06/14							***
	***************************************************/

		$locations = array();
		$query = "SELECT * FROM `cms_contacts_locations` ORDER BY `ordering` ASC";
		$results=q($query, $dbweb, __FILE__, __LINE__);

		

		$xml = "";
		while($row = mysqli_fetch_array($results)) 
		{
			
			$xml .= "
				<Locations>
					<id_location>".$row['id_location']."</id_location>
					<location_name>".$row['location']."</location_name>
					<company>".$row['company']."</company>
				</Locations>
			";
				
		}
		print $xml;

?>