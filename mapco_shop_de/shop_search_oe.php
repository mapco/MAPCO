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
	if ( $_POST["search"]!="" and strlen($_POST["search"]>3) )
	{
		$results=q("SELECT * FROM shop_items WHERE MPN='".mysqli_real_escape_string($dbshop, $_POST["search"])."' and active=1
				    UNION
					SELECT * FROM shop_items WHERE EAN='".mysqli_real_escape_string($dbshop, $_POST["search"])."' and active=1;", $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows($results)==1 )
		{
			$row=mysqli_fetch_array($results);
			$results2=q("SELECT * FROM shop_items_".$_GET["lang"]." WHERE id_item=".$row["id_item"].";", $dbshop, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			header( 'Location: '.PATHLANG.'online-shop/autoteile/'.$row["id_item"].'/'.str_replace("%2F", "%20", rawurlencode($row2["title"])) );
			exit();
		}
	}

	$title="OE-Nummern-Suche ".$_POST["search"];
	include("templates/".TEMPLATE."/header.php");
	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	echo '<div id="mid_right_column">';

	echo '<h1>'.t("OE-Nummer-Suche").' &quot;<i>'.$_POST["search"].'</i>&quot;</h1>';
	echo '<p><a style="font-size:20px;" href="'.PATHLANG.'suche/'.$_POST["search"].'/">Nach Freitext \''.$_POST["search"].'\' suchen.</a></p>';

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
			echo '<p>Die OE-Nummern-Suche liefert '.mysqli_num_rows($results3).' Suchergebnisse für den Begriff \''.$_POST["search"].'\'.		</p>';
//			echo '<table class="shopitems">';
			while( $row3=mysqli_fetch_array($results3) )
			{
				$oenr=$row3["OENr"];
				$results=q("SELECT * FROM shop_items WHERE MPN='".$row3["ArtNr"]."'and active=1;", $dbshop, __FILE__, __LINE__);
				if ( mysqli_num_rows($results)>0 )
				{
					while($row=mysqli_fetch_array($results))
					{
						$results2=q("SELECT * FROM shop_items_".$_GET["lang"]." WHERE id_item=".$row["id_item"].";", $dbshop, __FILE__, __LINE__);
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