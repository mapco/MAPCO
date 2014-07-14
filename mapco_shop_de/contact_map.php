<?php
	include("config.php");
	$title="Alle MAPCO-Filialen in der Filialsuche";
	$keywords="MAPCO, Autotechnik, Teile, Autoteile, Filiale, Filialen, Filialsuche, Brück, Borkheide, Berlin, Dresden, Essen, Frankfurt, Leipzig, Magdeburg, Neubrandenburg, Sömmerda";
	$description="Finden Sie Ihre nächste MAPCO-Filiale bequem über unsere Filialsuche. Sie finden unsere Filialen in Brück, Borkheide, Berlin, Dresden, Essen, Frankfurt, Leipzig, Magdeburg, Neubrandenburg und Sömmerda.";
	include("templates/".TEMPLATE."/header.php");
	include("templates/".TEMPLATE."/cms_leftcolumn.php");
?>
<script language="javascript">
	function show_regions()
	{
		document.getElementById("regions").style.display='block';
	}
	function hide_regions()
	{
		document.getElementById("regions").style.display='none';
	}
	function show_locations()
	{
		document.getElementById("locations").style.display='block';
	}
	function hide_locations()
	{
		document.getElementById("locations").style.display='none';
	}
	function show_region(id_contact, e)
	{
		var person=Array();
		<?php
		$results=q("SELECT * FROM cms_contacts WHERE department_id=33;", $dbweb, __FILE__, __LINE__);
		while($row=mysqli_fetch_array($results))
		{
			echo "person[".$row["id_contact"]."] = new Object();\n";
			echo "person[".$row["id_contact"]."]['name']='".$row["firstname"]." ".$row["lastname"]."';\n";
			echo "person[".$row["id_contact"]."]['mail']='".$row["mail"]."';\n";
			echo "person[".$row["id_contact"]."]['phone']='".$row["phone"]."';\n";
			echo "person[".$row["id_contact"]."]['fax']='".$row["fax"]."';\n";
			echo "person[".$row["id_contact"]."]['mobile']='".$row["mobile"]."';\n";
			echo "person[".$row["id_contact"]."]['image']='".substr($row["mail"], 0, strpos($row["mail"], "@"))."';\n";
		}
		?>
		if (!e) var e=window.event;
		mx=e.clientX;
		my=e.clientY;
		if (person[id_contact]!=undefined)
		{
			document.getElementById("infobox").style.left=+(mx+20) + "px";
			document.getElementById("infobox").style.top=+(my+20) + "px";
			text="";
			text = text + '<img style="margin:0px 5px 2px 0px; float:left;" src="<?php echo PATH; ?>images/employees/' + person[id_contact]["image"] + '.jpg" />';
			text = text + '<b>' + person[id_contact]["name"] + '</b>';
			if (person[id_contact]["phone"]!="") text = text + "<br /><br />Telefon: "+person[id_contact]["phone"];
			if (person[id_contact]["fax"]!="") text = text + "<br />Telefax: "+person[id_contact]["fax"];
			if (person[id_contact]["mobile"]!="") text = text + "<br />Mobil: "+person[id_contact]["mobile"];
			if (person[id_contact]["mail"]!="") text = text + "<br />E-Mail: "+person[id_contact]["mail"];
			document.getElementById("infobox").innerHTML=text;
			document.getElementById("infobox").style.display="block";
		}
	}


	function show_info(nr, e)
  {
    var standorte = new Array();
    standorte[0] = new Object();
    standorte[0]["bild"] = "standort_brueck.jpg";
    standorte[0]["name"] = "MAPCO Zentrale & Autoteile Shop";
    standorte[0]["strasse"] = "Gregor-von-Brück-Ring 1";
    standorte[0]["ort"] = "14822 Brück";
    standorte[0]["fon"] = "0180 20 62 726";
    standorte[0]["fax"] = "033844 75 82 20";
    standorte[0]["mail"] = "brueck@mapco.de";

    standorte[1] = new Object();
    standorte[1]["bild"] = "standort_magdeburg.jpg";
    standorte[1]["name"] = "RegionalCENTER Magdeburg";
    standorte[1]["strasse"] = "Jordanstraße 4";
    standorte[1]["ort"] = "39112 Magdeburg";
    standorte[1]["fon"] = "0180 20 62 726";
    standorte[1]["fax"] = "0391 60 75 818";
    standorte[1]["mail"] = "magdeburg@mapco.de";

    standorte[3] = new Object();
    standorte[3]["bild"] = "standort_neubrandenburg.jpg";
    standorte[3]["name"] = "RegionalCENTER Neubrandenburg";
    standorte[3]["strasse"] = "Gerstenstraße 2";
    standorte[3]["ort"] = "17034 Neubrandenburg";
    standorte[3]["fon"] = "0180 2 06 27 26";
    standorte[3]["fax"] = "0395/4 55 03 97";
    standorte[3]["mail"] = "neubrandenburg@mapco.de";

    standorte[4] = new Object();
    standorte[4]["bild"] = "standort_borkheide.jpg";
    standorte[4]["name"] = "RegionalCENTER Borkheide";
    standorte[4]["strasse"] = "Moosweg 1";
    standorte[4]["ort"] = "14822 Borkheide";
    standorte[4]["fon"] = "0180 2 06 27 26";
    standorte[4]["fax"] = "033845/4 10 32";
    standorte[4]["mail"] = "borkheide@mapco.de";

    standorte[5] = new Object();
    standorte[5]["bild"] = "standort_leipzig.jpg";
    standorte[5]["name"] = "RegionalCENTER Leipzig";
    standorte[5]["strasse"] = "Mommsenstraße 6";
    standorte[5]["ort"] = "04329 Leipzig";
    standorte[5]["fon"] = "0180 2 06 27 26";
    standorte[5]["fax"] = "0341 25 25 560";
    standorte[5]["mail"] = "leipzig@mapco.de";

    standorte[6] = new Object();
    standorte[6]["bild"] = "standort_soemmerda.jpg";
    standorte[6]["name"] = "RegionalCENTER Sömmerda";
    standorte[6]["strasse"] = "Leubinger Straße 7";
    standorte[6]["ort"] = "99610 Sömmerda";
    standorte[6]["fon"] = "0180 2 06 27 26";
    standorte[6]["fax"] = "03634 31 63 05";
    standorte[6]["mail"] = "soemmerda@mapco.de";

    standorte[7] = new Object();
    standorte[7]["bild"] = "standort_dresden.jpg";
    standorte[7]["name"] = "RegionalCENTER Dresden";
    standorte[7]["strasse"] = "Lohrmannstraße 19";
    standorte[7]["ort"] = "01237 Dresden";
    standorte[7]["fon"] = "0180 2 06 27 26";
    standorte[7]["fax"] = "0351 27 05 717";
    standorte[7]["mail"] = "dresden@mapco.de";

    standorte[8] = new Object();
    standorte[8]["bild"] = "standort_essen.jpg";
    standorte[8]["name"] = "RegionalCENTER Essen";
    standorte[8]["strasse"] = "Stauderstraße 83-85";
    standorte[8]["ort"] = "45326 Essen";
    standorte[8]["fon"] = "0180 2 06 27 26";
    standorte[8]["fax"] = "0201 43 64 85 94";
    standorte[8]["mail"] = "essen@mapco.de";

    standorte[36] = new Object();
    standorte[36]["bild"] = "";
    standorte[36]["name"] = "RegionalCENTER Frankfurt (Main)";
    standorte[36]["strasse"] = "Bolongarostraße 55";
    standorte[36]["ort"] = "65934 Frankfurt (Main)";
    standorte[36]["fon"] = "0180 2 06 27 26";
    standorte[36]["fax"] = "069 30 03 56 57";
    standorte[36]["mail"] = "frankfurt@mapco.de";

    standorte[37] = new Object();
    standorte[37]["bild"] = "";
    standorte[37]["name"] = "RegionalCENTER Berlin";
    standorte[37]["strasse"] = "Heusingerstraße 12";
    standorte[37]["ort"] = "12107 Berlin";
    standorte[37]["fon"] = "0180 2 06 27 26";
    standorte[37]["fax"] = "030 70 76 96 14";
    standorte[37]["mail"] = "berlin@mapco.de";

	if (!e) var e=window.event;
	mx=e.clientX;
	my=e.clientY;
	document.getElementById("infobox").style.left=+(mx+20) + "px";
	document.getElementById("infobox").style.top=+(my+20) + "px";
    document.getElementById("infobox").style.display="block";
    text="";
    text = text + '<b>' + standorte[nr]["name"] + '</b>';
    if (standorte[nr]["bild"]!="") text = text + '<br /><img src="<?php echo PATH; ?>images/karten/' + standorte[nr]["bild"] + '" alt="' + standorte[nr]["name"] + '" title="' + standorte[nr]["name"] + '" />';
    if (standorte[nr]["strasse"]!="") text = text + "<br />"+standorte[nr]["strasse"];
    if (standorte[nr]["ort"]!="") text = text + "<br />"+standorte[nr]["ort"];
    if (standorte[nr]["fon"]!="") text = text + "<br /><br />Telefon: "+standorte[nr]["fon"];
    if (standorte[nr]["fax"]!="") text = text + "<br />Telefax: "+standorte[nr]["fax"];
    if (standorte[nr]["mail"]!="") text = text + "<br />E-Mail: "+standorte[nr]["mail"];
    document.getElementById("infobox").innerHTML=text;
  }
  
  
	function hide_info()
	{
	document.getElementById("infobox").style.display="none";
	}
</script>

<?php
	echo '<div id="mid_column">';
	echo '<h1>Filialsuche</h1>';
	echo '<div id="infobox"></div>';
	
	
	if(isset($_GET["regions"]))	echo '<div id="regions">';
	else echo '<div id="regions" style="display:none;">';
	echo '<a style="width:500px;" class="category" href="javascript: show_locations(); hide_regions();" />'.t("Standorte anzeigen").'</a>';
	echo '
	<img src="'.PATH.'images/maps/germany_by_region.jpg" border="0" usemap="#Map" />
<map name="Map" id="Map">
  <area shape="poly" coords="71,205,85,215,91,227,101,228,110,228,119,228,125,230,139,224,164,234,163,247,169,254,169,266,171,278,160,287,144,293,141,317,130,323,137,330,129,345,142,349,151,346,163,372,167,399,153,398,123,394,118,401,93,389,73,389,60,386,43,362,36,341,38,317,46,302,37,284,37,268,34,260,49,235,40,223,40,208,57,206" href="#west" onmouseover="this.style.cursor = \'default\'"  onmousemove="show_region(0, event);" onmouseout="hide_info();" />
  <area shape="poly" coords="168,375,206,376,201,359,208,334,232,322,243,318,250,330,256,338,273,331,295,328,301,320,326,323,337,332,348,340,361,334,366,347,365,365,418,404,435,426,432,437,419,441,377,468,384,482,393,499,399,516,389,518,374,501,348,502,312,512,284,524,263,517,252,515,239,531,227,524,207,512,197,508,177,502,162,503,147,496,134,496,135,510,126,510,93,511,84,499,105,437,126,403,125,398,144,398,163,399" href="#sued" onmouseover="this.style.cursor = \'default\'" onmousemove="show_region(79, event);" onmouseout="hide_info();" />
  <area shape="poly" coords="75,206,76,190,97,179,83,159,96,154,108,122,102,115,103,100,133,91,148,93,152,104,159,101,178,86,187,83,200,81,185,68,177,53,194,49,165,29,169,20,187,23,217,29,237,32,243,38,240,48,261,51,282,53,288,41,298,53,285,68,276,74,291,76,307,76,324,68,342,58,355,50,369,50,385,37,399,45,417,65,428,85,439,103,437,132,429,141,451,161,450,169,459,185,462,210,441,224,420,228,414,233,384,224,360,221,344,221,333,228,309,231,296,225,281,235,270,244,254,235,233,224,212,218,203,224,188,227,197,237,186,245,173,255,162,242,155,234,138,224,129,231,105,229,89,224" href="#nord" onmouseover="this.style.cursor = \'default\'" onmousemove="show_region(78, event);" onmouseout="hide_info();" />
  <area shape="poly" coords="462,212,466,225,475,236,480,257,471,268,459,263,446,259,443,270,399,287,376,301,351,305,350,315,354,335,342,338,323,328,305,325,296,319,273,324,268,331,257,325,245,317,233,320,213,329,204,347,201,371,189,375,162,376,149,356,142,346,131,341,136,327,143,313,146,292,163,281,169,271,168,256,187,244,195,235,189,227,208,219,232,225,258,234,274,240,293,227,312,233,331,227,348,218,381,221,414,233,444,221" href="#ost" onmouseover="this.style.cursor = \'default\'" onmousemove="show_region(77, event);" onmouseout="hide_info();" />
</map></div>
';


	if(isset($_GET["regions"]))	echo '<div id="locations" style="display:none;">';
	else echo '<div id="locations">';
	echo '<a style="width:500px;" class="category" href="javascript: hide_locations(); show_regions();" />'.t("Gebiete anzeigen").'</a><br style="clear:both;" />';
	echo '<div style="position:relative;" id="map_de">';
	$results=q("SELECT * FROM cms_contacts_locations where id_location <11;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		if($row["id_location"]==1) echo '<div class="headquarter" style="left:310px; top:178px; position:absolute;">';
		elseif($row["id_location"]==2) echo '<div class="regionalcenter" style="left:320px; top:165px; position:absolute;">';
		elseif($row["id_location"]==3) echo '<div class="regionalcenter" style="left:349px; top:84px; position:absolute;">';
		elseif($row["id_location"]==4) echo '<div class="regionalcenter" style="left:317px; top:227px; position:absolute;">';
		elseif($row["id_location"]==5) echo '<div class="regionalcenter" style="left:226px; top:249px; position:absolute;">';
		elseif($row["id_location"]==6) echo '<div class="regionalcenter" style="left:389px; top:238px; position:absolute;">';
		elseif($row["id_location"]==7) echo '<div class="regionalcenter" style="left:268px; top:162px; position:absolute;">';
		elseif($row["id_location"]==8) echo '<div class="regionalcenter" style="left:124px; top:309px; position:absolute;">';
		elseif($row["id_location"]==9) echo '<div class="regionalcenter" style="left:358px; top:143px; position:absolute;">';
		elseif($row["id_location"]==10) echo '<div class="regionalcenter" style="left:62px; top:216px; position:absolute;">';
		echo '<span>';
		echo '<a href="'.$row["website"].'" target="_blank">';
		if($row["id_location"]==1) echo '<b>MAPCO Zentrale '.$row["location"].'</b>';
		else echo '<b>RegionalCENTER '.$row["location"].'</b>';
		echo '</a>';
		echo '<br />';
		echo '<img src="'.PATH.'images/karten/standort_'.$row["id_location"].'.jpg" alt="'.$row["location"].'" title="'.$row["location"].'" />';
		echo '<br /><br />';
		echo $row["street"].' '.$row["streetnr"].'<br />';
		echo $row["zipcode"].' '.$row["city"].'<br /><br />';
		echo 'Tel.: '.$row["phone"].'<br />';
		echo 'Fax: '.$row["fax"].'<br />';
		echo 'E-Mail: <a href="mailto:'.$row["mail"].'">'.$row["mail"].'</a>';
		echo '</span>';
		echo '</div>';
	}
    
	echo '</div></div>';
	echo '</div>';

	include("templates/".TEMPLATE."/cms_rightcolumn.php");
	include("templates/".TEMPLATE."/footer.php");
?>