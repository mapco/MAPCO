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
		$results=q("SELECT * FROM shop_countries WHERE id_country=".$_POST["bill_country_id"].";", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$check_adr=$_POST["bill_company"].$_POST["bill_firstname"].$_POST["bill_lastname"].$_POST["bill_street"].$_POST["bill_zip"].$_POST["bill_city"];
		if ($_POST["bill_adr_id"]==0 and $check_adr!='')
		{
			if($_POST["bill_standard"]==1)
			{
				q("UPDATE shop_bill_adr SET standard=0 WHERE user_id=".$_SESSION["id_user"].";", $dbshop, __FILE__, __LINE__);
			}
			else
			{
				$results2=q("SELECT * FROM shop_bill_adr WHERE user_id=".$_SESSION["id_user"]." and active=1 LIMIT 1;", $dbshop, __FILE__, __LINE__);
				if(mysqli_num_rows($results2)==0) $_POST["bill_standard"]=1;
			}
			q("INSERT INTO shop_bill_adr (user_id, company, gender, title, firstname, lastname, street, number, additional, zip, city, country, country_id, standard) VALUES(".$_SESSION["id_user"].", '".mysqli_real_escape_string($dbshop, $_POST["bill_company"])."', '".$_POST["bill_gender"]."', '".mysqli_real_escape_string($dbshop, $_POST["bill_title"])."', '".mysqli_real_escape_string($dbshop, $_POST["bill_firstname"])."', '".mysqli_real_escape_string($dbshop, $_POST["bill_lastname"])."', '".mysqli_real_escape_string($dbshop, $_POST["bill_street"])."', '".mysqli_real_escape_string($dbshop, $_POST["bill_number"])."', '".mysqli_real_escape_string($dbshop, $_POST["bill_additional"])."', '".mysqli_real_escape_string($dbshop, $_POST["bill_zip"])."', '".mysqli_real_escape_string($dbshop, $_POST["bill_city"])."', '".$row["country"]."', ".$_POST["bill_country_id"].", ".$_POST["bill_standard"].");", $dbshop, __FILE__, __LINE__);	
			$_POST["bill_adr_id"]=mysqli_insert_id($dbshop);

		}
		elseif ($_POST["bill_adr_id"]>0)
		{
			$results2=q("SELECT * FROM shop_bill_adr 
							WHERE adr_id=".$_POST["bill_adr_id"]." 
							AND company='".mysqli_real_escape_string($dbshop, $_POST["bill_company"])."' 
							AND gender='".$_POST["bill_gender"]."' 
							AND title='".mysqli_real_escape_string($dbshop, $_POST["bill_title"])."' 
							AND firstname='".mysqli_real_escape_string($dbshop, $_POST["bill_firstname"])."' 
							AND lastname='".mysqli_real_escape_string($dbshop, $_POST["bill_lastname"])."' 
							AND street='".mysqli_real_escape_string($dbshop, $_POST["bill_street"])."' 
							AND number='".mysqli_real_escape_string($dbshop, $_POST["bill_number"])."' 
							AND additional='".mysqli_real_escape_string($dbshop, $_POST["bill_additional"])."' 
							AND zip='".mysqli_real_escape_string($dbshop, $_POST["bill_zip"])."' 
							AND city='".mysqli_real_escape_string($dbshop, $_POST["bill_city"])."' 
							AND country_id=".$_POST["bill_country_id"].";", $dbshop, __FILE__, __LINE__);
			if(mysqli_num_rows($results2)>0)
			{
				$row2=mysqli_fetch_array($results2);
				if($_POST["bill_standard"]!=$row2["standard"])
				{
					q("UPDATE shop_bill_adr SET standard=0 WHERE user_id=".$row2["user_id"].";", $dbshop, __FILE__, __LINE__);
					q("UPDATE shop_bill_adr SET standard=".$_POST["bill_standard"]." WHERE adr_id=".$_POST["bill_adr_id"].";", $dbshop, __FILE__, __LINE__);
					if($_POST["bill_standard"]==0)
					{
						$results=q("SELECT * FROM shop_bill_adr WHERE user_id=".$_SESSION["id_user"]." and active=1 ORDER BY adr_id ASC LIMIT 1;", $dbshop, __FILE__, __LINE__);
						$row=mysqli_fetch_array($results);
						q("UPDATE shop_bill_adr SET standard=1 WHERE adr_id=".$row["adr_id"].";", $dbshop, __FILE__, __LINE__);
					}
				}
			}
			else
			{
				if($_POST["bill_standard"]==1)
				{
					q("UPDATE shop_bill_adr SET standard=0 WHERE user_id=".$_SESSION["id_user"].";", $dbshop, __FILE__, __LINE__);
				}
				q("INSERT INTO shop_bill_adr (user_id, company, gender, title, firstname, lastname, street, number, additional, zip, city, country, country_id, standard) VALUES(".$_SESSION["id_user"].", '".mysqli_real_escape_string($dbshop, $_POST["bill_company"])."', '".$_POST["bill_gender"]."', '".mysqli_real_escape_string($dbshop, $_POST["bill_title"])."', '".mysqli_real_escape_string($dbshop, $_POST["bill_firstname"])."', '".mysqli_real_escape_string($dbshop, $_POST["bill_lastname"])."', '".mysqli_real_escape_string($dbshop, $_POST["bill_street"])."', '".mysqli_real_escape_string($dbshop, $_POST["bill_number"])."', '".mysqli_real_escape_string($dbshop, $_POST["bill_additional"])."', '".mysqli_real_escape_string($dbshop, $_POST["bill_zip"])."', '".mysqli_real_escape_string($dbshop, $_POST["bill_city"])."', '".$row["country"]."', ".$_POST["bill_country_id"].", ".$_POST["bill_standard"].");", $dbshop, __FILE__, __LINE__);				
			}
			
		}
		$_SESSION["bill_adr_id"]=$_POST["bill_adr_id"];
		$_SESSION["bill_company"]=$_POST["bill_company"];
		$_SESSION["bill_gender"]=$_POST["bill_gender"];
		$_SESSION["bill_title"]=$_POST["bill_title"];
		$_SESSION["bill_firstname"]=$_POST["bill_firstname"];
		$_SESSION["bill_lastname"]=$_POST["bill_lastname"];
		$_SESSION["bill_street"]=$_POST["bill_street"];
		$_SESSION["bill_number"]=$_POST["bill_number"];
		$_SESSION["bill_additional"]=$_POST["bill_additional"];
		$_SESSION["bill_zip"]=$_POST["bill_zip"];
		$_SESSION["bill_city"]=$_POST["bill_city"];
		$_SESSION["bill_country_id"]=$_POST["bill_country_id"];
		$_SESSION["bill_country"]=$row["country"];
		$_SESSION["bill_standard"]=$_POST["bill_standard"];
		$_SESSION["id_payment"]="";
		$_SESSION["id_billping"]="";
		if($_SESSION["ship_country_id"]!=$_SESSION["bill_country_id"] and (!isset($_SESSION["ship_adr_id"])or !($_SESSION["ship_adr_id"]>0))) unset($_SESSION["id_shipping"]);



	//return success
//	echo '<ItemExportResponse>'."\n";
//	echo '	<Ack>Success</Ack>'."\n";
//	echo '	<ListID>'.$id_list.'</ListID>'."\n";
//	echo '</ItemExportResponse>'."\n";

?>