<?php
	exit;
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	echo '	<meta http-equiv="refresh" content="300;url=http://www.mapco.de/jobs/update_auctions.php">';

	/*********************************
	 * find shopitems not up to date *
	 *********************************/
	include("config.php");
/*
	$i=0;
	$t200=array();
	$results=q("SELECT * FROM t_200;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$day=substr($row["LETZTE_BEARB"], 8, 2);
		$month=substr($row["LETZTE_BEARB"], 5, 2);
		$year=substr($row["LETZTE_BEARB"], 0, 4);
		$timestamp=mktime(0, 0, 0, $month, $day, $year);
		$t200[$row["ArtNr"]]=$timestamp;
		$i++;
	}
*/	
	echo '<script type="text/javascript">'."\n";
	echo '	var items=new Array()'.";\n";
	echo '	var artnr=new Array()'.";\n";
	$i=0;
	$results=q("SELECT id_item, MPN FROM shop_items WHERE lastmod<".(time()-24*3600)." ORDER BY RAND();", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if( $row["MPN"]!="103230" )
		{
			echo 'artnr['.$i.']='."'".$row["MPN"]."';\n";
			echo 'items['.$i.']='.$row["id_item"].";\n";
			$i++;
		}
	}
?>
	function itemupdate(i)
    {
	    if ( i>=items.length )
        {
        	$("#view").html("Alle Shopartikel erfolgreich aktualisiert.");
	        return;
        }

       	$("#view").html("Aktualisiere Shopartikel ("+artnr[i]+") "+(i+1)+" von "+items.length+".");
		$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ItemUpdate", id_item:items[i] },
        	function(data)
            {
				window.location.href=window.location.href;
            	//itemupdate(i+1);
            }
        );
	}
<?php
	echo '</script>';
	
	echo '<h1>Artikelaktualisierung</h1>';
	echo '<div id="view"></div>';
	echo '<script type="text/javascript"> itemupdate(0); </script>';
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>