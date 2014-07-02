<?php
	include("config.php");
	include("functions/mapco_gewerblich.php");

	echo '<!DOCTYPE HTML>'."\n";
	echo '<html lang="de">'."\n";
	echo '<head>'."\n";
	echo '	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'."\n";
	echo '</head>'."\n";
	echo '<body>';
	
	$results=q("SELECT email FROM cms_newsletter;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$mail[$row["email"]]=$row["email"];
	}
	echo '<table>';
	echo '<tr>';
	echo '<td>User ID</td>';
	echo '<td>E-Mail</td>';
	echo '<td>Newsletter</td>';
	echo '<td>Name</td>';
	echo '<td>Firma</td>';
	echo '<td>Ansprechpartner</td>';
	echo '<td>Gewerblich</td>';
	echo '</tr>';
	$results2=q("SELECT id_user, usermail, name FROM cms_users WHERE usermail!='' AND language_id IN (0,1);", $dbweb, __FILE__, __LINE__);
	while( $row2=mysqli_fetch_array($results2) )
	{
		echo '<tr>';
		echo '<td>'.$row2["id_user"].'</td>';
		echo '<td>'.$row2["usermail"].'</td>';
		if (isset($mail[$row2["usermail"]])) $newsletter=1;
		else $newsletter=0;
		echo '<td>'.$newsletter.'</td>';
		echo '<td>'.$row2["name"].'</td>';
		$results3=q("SELECT company, gender, firstname, lastname FROM shop_bill_adr WHERE user_id=".$row2["id_user"]." and standard=1 LIMIT 1;", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($results3)>0)
		{ 
			$row3=mysqli_fetch_array($results3);
			echo '<td>'.$row3["company"].'</td>';
			if ($row3["gender"]==0) $name="Herr";
			else $name="Frau";
			if ($row3["firstname"]!="") $name .=' '.$row3["firstname"];
			if ($row3["lastname"]!="") $name .=' '.$row3["lastname"];
			echo '<td>'.$name.'</td>';
		}
		else echo '<td></td><td></td>';
		if (gewerblich($row2["id_user"])) $gewerblich=1;
		else $gewerblich=0;
		echo '<td>'.$gewerblich.'</td>';
		echo '</tr>';
	}
	echo '</table>';
	echo '</body>';
	echo '</html>';
?>