<?php

	if( !isset($_POST["id_item"]) )
	{
		echo 'id_item nicht gesetzt';
		exit;
	}

	$results=q("SELECT * FROM shop_items WHERE id_item='".$_POST["id_item"]."';", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo 'id_item unbekannt';
		exit;
	}
	$row=mysqli_fetch_array($results);
	$ARTNR=$row["MPN"];

	echo '<StockGetResponse>';
	//AUTOPARTNER + LENKUNG24 + MAPCO ENDKUNDEN
	if( $_SESSION["id_shop"]==2 or $_SESSION["id_shop"]==7 or $_SESSION["id_shop"]==8 )
	{
		$results=q("SELECT ISTBESTAND, MOCOMBESTAND FROM lager WHERE ARTNR='".$ARTNR."';", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)==0 )
		{
			echo '<Stock id_item="'.$_POST["id_item"].'">0</Stock>';
		}
		else
		{
			$row=mysqli_fetch_array($results);
			echo '<Stock id_item="'.$_POST["id_item"].'">'.($row["ISTBESTAND"]+$row["MOCOMBESTAND"]+$row["ONLINEBESTAND"]).'</Stock>';
		}
	}
	//MMAPCO GEWERBEKUNDEN
	elseif( $_SESSION["id_shop"]==1 or $_SESSION["id_shop"]==18 )
	{
		$results=q("SELECT ISTBESTAND FROM lager WHERE ARTNR='".$ARTNR."';", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)==0 )
		{
			echo '<Stock id_item="'.$_POST["id_item"].'">0</Stock>';
		}
		else
		{
			$row=mysqli_fetch_array($results);
			echo '<Stock id_item="'.$_POST["id_item"].'">'.$row["ISTBESTAND"].'</Stock>';
		}
	}
	//RC Neubrandenburg
	elseif( $_SESSION["id_shop"]==9 )
	{
		$results=q("SELECT SUM(ISTBESTAND) AS ISTBESTAND FROM (
					SELECT ISTBESTAND FROM lagerrc WHERE ARTNR='".$ARTNR."' AND RCNR=15
					UNION
					SELECT ISTBESTAND FROM lager WHERE ARTNR='".$ARTNR."') AS BESTAND;", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)==0 )
		{
			echo '<Stock id_item="'.$_POST["id_item"].'">0</Stock>';
		}
		else
		{
			$row=mysqli_fetch_array($results);
			echo '<Stock id_item="'.$_POST["id_item"].'">'.$row["ISTBESTAND"].'</Stock>';
		}
	}
	//RC Leipzig
	elseif( $_SESSION["id_shop"]==10 )
	{
		$results=q("SELECT SUM(ISTBESTAND) AS ISTBESTAND FROM (
					SELECT ISTBESTAND FROM lagerrc WHERE ARTNR='".$ARTNR."' AND RCNR=16
					UNION
					SELECT ISTBESTAND FROM lager WHERE ARTNR='".$ARTNR."') AS BESTAND;", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)==0 )
		{
			echo '<Stock id_item="'.$_POST["id_item"].'">0</Stock>';
		}
		else
		{
			$row=mysqli_fetch_array($results);
			echo '<Stock id_item="'.$_POST["id_item"].'">'.$row["ISTBESTAND"].'</Stock>';
		}
	}
	//RC Sömmerda
	elseif( $_SESSION["id_shop"]==11 )
	{
		$results=q("SELECT SUM(ISTBESTAND) AS ISTBESTAND FROM (
					SELECT ISTBESTAND FROM lagerrc WHERE ARTNR='".$ARTNR."' AND RCNR=17
					UNION
					SELECT ISTBESTAND FROM lager WHERE ARTNR='".$ARTNR."') AS BESTAND;", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)==0 )
		{
			echo '<Stock id_item="'.$_POST["id_item"].'">0</Stock>';
		}
		else
		{
			$row=mysqli_fetch_array($results);
			echo '<Stock id_item="'.$_POST["id_item"].'">'.$row["ISTBESTAND"].'</Stock>';
		}
	}
	//RC Dresden
	elseif( $_SESSION["id_shop"]==12 )
	{
		$results=q("SELECT SUM(ISTBESTAND) AS ISTBESTAND FROM (
					SELECT ISTBESTAND FROM lagerrc WHERE ARTNR='".$ARTNR."' AND RCNR=18
					UNION
					SELECT ISTBESTAND FROM lager WHERE ARTNR='".$ARTNR."') AS BESTAND;", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)==0 )
		{
			echo '<Stock id_item="'.$_POST["id_item"].'">0</Stock>';
		}
		else
		{
			$row=mysqli_fetch_array($results);
			echo '<Stock id_item="'.$_POST["id_item"].'">'.$row["ISTBESTAND"].'</Stock>';
		}
	}
	//RC Magdeburg
	elseif( $_SESSION["id_shop"]==13 )
	{
		$results=q("SELECT SUM(ISTBESTAND) AS ISTBESTAND FROM (
					SELECT ISTBESTAND FROM lagerrc WHERE ARTNR='".$ARTNR."' AND RCNR=19
					UNION
					SELECT ISTBESTAND FROM lager WHERE ARTNR='".$ARTNR."') AS BESTAND;", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)==0 )
		{
			echo '<Stock id_item="'.$_POST["id_item"].'">0</Stock>';
		}
		else
		{
			$row=mysqli_fetch_array($results);
			echo '<Stock id_item="'.$_POST["id_item"].'">'.$row["ISTBESTAND"].'</Stock>';
		}
	}
	//RC Frankfurt/Main
	elseif( $_SESSION["id_shop"]==14 )
	{
		$results=q("SELECT SUM(ISTBESTAND) AS ISTBESTAND FROM (
					SELECT ISTBESTAND FROM lagerrc WHERE ARTNR='".$ARTNR."' AND RCNR=20
					UNION
					SELECT ISTBESTAND FROM lager WHERE ARTNR='".$ARTNR."') AS BESTAND;", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)==0 )
		{
			echo '<Stock id_item="'.$_POST["id_item"].'">0</Stock>';
		}
		else
		{
			$row=mysqli_fetch_array($results);
			echo '<Stock id_item="'.$_POST["id_item"].'">'.$row["ISTBESTAND"].'</Stock>';
		}
	}
	//RC Berlin
	elseif( $_SESSION["id_shop"]==15 )
	{
		$results=q("SELECT SUM(ISTBESTAND) AS ISTBESTAND FROM (
					SELECT ISTBESTAND FROM lagerrc WHERE ARTNR='".$ARTNR."' AND RCNR=21
					UNION
					SELECT ISTBESTAND FROM lager WHERE ARTNR='".$ARTNR."') AS BESTAND;", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)==0 )
		{
			echo '<Stock id_item="'.$_POST["id_item"].'">0</Stock>';
		}
		else
		{
			$row=mysqli_fetch_array($results);
			echo '<Stock id_item="'.$_POST["id_item"].'">'.$row["ISTBESTAND"].'</Stock>';
		}
	}
	//RC Essen
	elseif( $_SESSION["id_shop"]==16 )
	{
		$results=q("SELECT SUM(ISTBESTAND) AS ISTBESTAND FROM (
					SELECT ISTBESTAND FROM lagerrc WHERE ARTNR='".$ARTNR."' AND RCNR=22
					UNION
					SELECT ISTBESTAND FROM lager WHERE ARTNR='".$ARTNR."') AS BESTAND;", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)==0 )
		{
			echo '<Stock id_item="'.$_POST["id_item"].'">0</Stock>';
		}
		else
		{
			$row=mysqli_fetch_array($results);
			echo '<Stock id_item="'.$_POST["id_item"].'">'.$row["ISTBESTAND"].'</Stock>';
		}
	}
	//RC Roma
	elseif( $_SESSION["id_shop"]==17 )
	{
		$results=q("SELECT * FROM lagerrc WHERE ARTNR='".$ARTNR."' AND RCNR=44;", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)==0 )
		{
			echo '<Stock id_item="'.$_POST["id_item"].'">0</Stock>';
		}
		else
		{
			$row=mysqli_fetch_array($results);
			echo '<Stock id_item="'.$_POST["id_item"].'">'.$row["ISTBESTAND"].'</Stock>';
		}
	}
	//general
	else
	{
		$results=q("SELECT ISTBESTAND FROM lager WHERE ARTNR='".$ARTNR."';", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)==0 )
		{
			echo '<Stock id_item="'.$_POST["id_item"].'">0</Stock>';
		}
		else
		{
			$row=mysqli_fetch_array($results);
			echo '<Stock id_item="'.$_POST["id_item"].'">'.$row["ISTBESTAND"].'</Stock>';
		}
	}
	
	echo '</StockGetResponse>';

?>