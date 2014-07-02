<?php
/*
	if ( !isset($_POST["title"]) )
	{
		echo '<ItemExportResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Titel nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Die Liste muss einen Titel haben.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemExportResponse>'."\n";
		exit;
	}

	if ( $_POST["title"]=="" )
	{
		echo '<ItemExportResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Titel leer.</shortMsg>'."\n";
		echo '		<longMsg>Der Titel der Liste darf nicht leer sein.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemExportResponse>'."\n";
		exit;
	}
*/
		$results=q("SELECT * FROM shop_countries WHERE id_country=".$_POST["ship_country_id"].";", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$check_adr=$_POST["ship_company"].$_POST["ship_firstname"].$_POST["ship_lastname"].$_POST["ship_street"].$_POST["ship_zip"].$_POST["ship_city"];
		if ($_POST["ship_adr_id"]==0 and $check_adr!='')
		{
			if($_POST["ship_standard"]==1)
			{
				q("UPDATE shop_bill_adr SET standard_ship_adr=0 WHERE user_id=".$_SESSION["id_user"].";", $dbshop, __FILE__, __LINE__);
			}
			q("INSERT INTO shop_bill_adr (user_id, company, gender, title, firstname, lastname, street, number, additional, zip, city, country, country_id, standard_ship_adr) VALUES(".$_SESSION["id_user"].", '".mysqli_real_escape_string($dbshop, $_POST["ship_company"])."', '".$_POST["ship_gender"]."', '".mysqli_real_escape_string($dbshop, $_POST["ship_title"])."', '".mysqli_real_escape_string($dbshop, $_POST["ship_firstname"])."', '".mysqli_real_escape_string($dbshop, $_POST["ship_lastname"])."', '".mysqli_real_escape_string($dbshop, $_POST["ship_street"])."', '".mysqli_real_escape_string($dbshop, $_POST["ship_number"])."', '".mysqli_real_escape_string($dbshop, $_POST["ship_additional"])."', '".mysqli_real_escape_string($dbshop, $_POST["ship_zip"])."', '".mysqli_real_escape_string($dbshop, $_POST["ship_city"])."', '".$row["country"]."', ".$_POST["ship_country_id"].", ".$_POST["ship_standard"].");", $dbshop, __FILE__, __LINE__);	
			$_POST["ship_adr_id"]=mysqli_insert_id($dbshop);
		}
		elseif ($_POST["ship_adr_id"]>0)
		{
			$results2=q("SELECT * FROM shop_bill_adr 
							WHERE adr_id=".$_POST["ship_adr_id"]." 
							AND company='".mysqli_real_escape_string($dbshop, $_POST["ship_company"])."' 
							AND gender='".$_POST["ship_gender"]."' 
							AND title='".mysqli_real_escape_string($dbshop, $_POST["ship_title"])."' 
							AND firstname='".mysqli_real_escape_string($dbshop, $_POST["ship_firstname"])."' 
							AND lastname='".mysqli_real_escape_string($dbshop, $_POST["ship_lastname"])."' 
							AND street='".mysqli_real_escape_string($dbshop, $_POST["ship_street"])."' 
							AND number='".mysqli_real_escape_string($dbshop, $_POST["ship_number"])."' 
							AND additional='".mysqli_real_escape_string($dbshop, $_POST["ship_additional"])."' 
							AND zip='".mysqli_real_escape_string($dbshop, $_POST["ship_zip"])."' 
							AND city='".mysqli_real_escape_string($dbshop, $_POST["ship_city"])."' 
							AND country_id=".$_POST["ship_country_id"].";", $dbshop, __FILE__, __LINE__);
			if(mysqli_num_rows($results2)>0)
			{
				$row2=mysqli_fetch_array($results2);
				if($_POST["ship_standard"]!=$row2["standard"])
				{
					q("UPDATE shop_bill_adr SET standard_ship_adr=0 WHERE user_id=".$row2["user_id"].";", $dbshop, __FILE__, __LINE__);
					q("UPDATE shop_bill_adr SET standard_ship_adr=".$_POST["ship_standard"]." WHERE adr_id=".$_POST["ship_adr_id"].";", $dbshop, __FILE__, __LINE__);
				}
			}
			else
			{
				if($_POST["ship_standard"]==1)
				{
					q("UPDATE shop_bill_adr SET standard_ship_adr=0 WHERE user_id=".$_SESSION["id_user"].";", $dbshop, __FILE__, __LINE__);
				}
				q("INSERT INTO shop_bill_adr (user_id, company, gender, title, firstname, lastname, street, number, additional, zip, city, country, country_id, standard_ship_adr) VALUES(".$_SESSION["id_user"].", '".mysqli_real_escape_string($dbshop, $_POST["ship_company"])."', '".$_POST["ship_gender"]."', '".mysqli_real_escape_string($dbshop, $_POST["ship_title"])."', '".mysqli_real_escape_string($dbshop, $_POST["ship_firstname"])."', '".mysqli_real_escape_string($dbshop, $_POST["ship_lastname"])."', '".mysqli_real_escape_string($dbshop, $_POST["ship_street"])."', '".mysqli_real_escape_string($dbshop, $_POST["ship_number"])."', '".mysqli_real_escape_string($dbshop, $_POST["ship_additional"])."', '".mysqli_real_escape_string($dbshop, $_POST["ship_zip"])."', '".mysqli_real_escape_string($dbshop, $_POST["ship_city"])."', '".$row["country"]."', ".$_POST["ship_country_id"].", ".$_POST["ship_standard"].");", $dbshop, __FILE__, __LINE__);				
			}
			
		}
		$_SESSION["ship_adr_id"]=$_POST["ship_adr_id"];
		$_SESSION["ship_company"]=$_POST["ship_company"];
		$_SESSION["ship_gender"]=$_POST["ship_gender"];
		$_SESSION["ship_title"]=$_POST["ship_title"];
		$_SESSION["ship_firstname"]=$_POST["ship_firstname"];
		$_SESSION["ship_lastname"]=$_POST["ship_lastname"];
		$_SESSION["ship_street"]=$_POST["ship_street"];
		$_SESSION["ship_number"]=$_POST["ship_number"];
		$_SESSION["ship_additional"]=$_POST["ship_additional"];
		$_SESSION["ship_zip"]=$_POST["ship_zip"];
		$_SESSION["ship_city"]=$_POST["ship_city"];
		$_SESSION["ship_country_id"]=$_POST["ship_country_id"];
		$_SESSION["ship_country"]=$row["country"];
		$_SESSION["ship_standard"]=$_POST["ship_standard"];
		$_SESSION["id_payment"]="";
		$_SESSION["id_shipping"]="";



	//return success
//	echo '<ItemExportResponse>'."\n";
//	echo '	<Ack>Success</Ack>'."\n";
//	echo '	<ListID>'.$id_list.'</ListID>'."\n";
//	echo '</ItemExportResponse>'."\n";

?>