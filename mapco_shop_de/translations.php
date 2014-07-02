<?php
	if ($_GET["lang"]=="") $_GET["lang"]="de";

	$results=q("SELECT * FROM web_translations;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		if ($row[$_GET["lang"]]!="") define($row["constant"], $row[$_GET["lang"]]);
		elseif ($row["en"]!="") define($row["constant"], $row["en"]);
		else define($row["constant"], $row["de"]);
	}

	//SprachNr
	$sprachnr=array("de" => "001",
				   "en" => "004",
				   "fr" => "006",
				   "it" => "007",
				   "es" => "008",
				   "nl" => "009",
				   "da" => "010",
				   "sv" => "011",
				   "no" => "012",
				   "fi" => "013",
				   "hu" => "014",
				   "pt" => "015",
				   "ru" => "016",
				   "sk" => "017",
				   "cs" => "018",
				   "pl" => "019",
				   "el" => "020",
				   "ro" => "021",
				   "tr" => "023",
				   "hr" => "024",
				   "sr" => "025",
				   "zh" => "004", //031
				   "bg" => "032",
				   "lv" => "033",
				   "lt" => "034",
				   "et" => "035",
				   "sl" => "036",
				   "qa" => "037",
				   "qb" => "038");
?>