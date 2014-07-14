<?php
	include("config.php");
	include("functions/shop_show_item.php");
	include("functions/mapco_get_titles.php");
	include("functions/shop_itemstatus.php");
	include("functions/cms_t.php");

	$i=2;
	while ( isset($_GET["getvars".$i]) and $_GET["getvars".$i]!="" )
	{
		if( $_GET["getvars1"]!="" ) $_GET["getvars1"].="/";
		$_GET["getvars1"].=$_GET["getvars".$i];
		$i++;
	}
	if ( isset($_GET["getvars1"]) ) $_POST["search"]=$_GET["getvars1"];

	//undefined index debug
	if ( !isset($_POST["search"]) ) $_POST["search"]="";

	//redirect to item if unique
/*
	if ( $_POST["search"]!="" and strlen($_POST["search"]>3) )
	{
		$results=q("SELECT * FROM shop_items WHERE MPN='".mysqli_real_escape_string($dbshop, $_POST["search"])."';", $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows($results)==1 )
		{
			$row=mysqli_fetch_array($results);
			$results2=q("SELECT * FROM shop_items_".$_SESSION["lang"]." WHERE id_item=".$row["id_item"].";", $dbshop, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			header( 'Location: '.PATHLANG.'online-shop/autoteile/'.$row["id_item"].'/'.str_replace("%2F", "%20", rawurlencode($row2["title"])) );
			exit();
		}
	}
*/

	$title="Shop-Suche ".$_POST["search"];
	include("templates/".TEMPLATE."/header.php");
	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	echo '<div id="mid_right_column">';

	echo '<h1>'.t("Shop-Suche").' &quot;<i>'.$_POST["search"].'</i>&quot;</h1>';	
	echo '<a style="font-size:20px;" href="'.PATHLANG.'oe-nummern-suche/'.$_POST["search"].'/">Nach OE-Nummer \''.$_POST["search"].'\' suchen.</a>';

	//Shop-Suche
	$results=q("SELECT * FROM shop_items_".$_SESSION["lang"]." WHERE title LIKE '%".$_POST["search"]."%' LIMIT 20;", $dbshop, __FILE__, __LINE__);
	echo '<p>Die Shop-Suche liefert '.mysqli_num_rows($results).' Suchergebnisse für den Begriff \''.$_POST["search"].'\'.</p>';
	while( $row=mysqli_fetch_array($results) )
	{
		show_item($row["id_item"], "", $row["title"], $row["short_description"]);
	}
	include("templates/".TEMPLATE."/footer.php");
	exit;


	echo '<form method="post">';
	echo '<table>';
	echo '	<tr>';
	echo '		<td>';
	echo t("Nummernsuche").': <input type="text" name="search" value="'.$_POST["search"].'" /><input type="submit" value="'.t("Suchen").'" />';
	echo '<br /><i>'.t("Geben Sie eine OE-Nummer oder eine Mitbewerbernummer ein, um vergleichbare MAPCO-Produkte zu suchen.").'</i>';
	echo '</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</form>';
	
	//too short
	if ( strlen($_POST["search"])<3 )
	{
		echo 'Geben Sie mindestens 3 Zeichen ein! Danke.';
		include("templates/".TEMPLATE."/footer.php");
		exit;
	}

	//number search
	if ($_POST["search"]!="")
	{
		$search=str_replace(" ", "", $_POST["search"]);
		$search=str_replace(".", "", $search);
		$search=str_replace(",", "", $search);
		$search=str_replace("-", "", $search);
		q("SET NAMES latin1", $dbshop, __FILE__, __LINE__);
		$results3=q("SELECT * FROM t_203 WHERE SOE LIKE '%".mysqli_real_escape_string($dbshop, $search)."%';", $dbshop, __FILE__, __LINE__);
		q("SET NAMES utf8", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($results3)>0)
		{
//			echo '<table class="shopitems">';
			while( $row3=mysqli_fetch_array($results3) )
			{
				$oenr=$row3["OENr"];
				$results=q("SELECT * FROM shop_items WHERE MPN='".$row3["ArtNr"]."';", $dbshop, __FILE__, __LINE__);
				if ( mysqli_num_rows($results)>0 )
				{
					while($row=mysqli_fetch_array($results))
					{
						$results2=q("SELECT * FROM shop_items_".$_SESSION["lang"]." WHERE id_item=".$row["id_item"].";", $dbshop, __FILE__, __LINE__);
						$row2=mysqli_fetch_array($results2);
						show_item($row["id_item"], $row["MPN"], $row2["title"], $row2["short_description"], $oenr);
					}
				}
			}
//			echo '</table>';
		}
		else
		{
			echo 'Keine Treffer.';
		}
	}
	
	
	echo '</div>';
	include("templates/".TEMPLATE."/footer.php");
?>