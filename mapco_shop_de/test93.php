<?php

include("config.php");
$kba=array();
$results=q("SELECT KTypNr, KBANr FROM t_121", $dbshop, __FILE__, __LINE__);
while($row=mysqli_fetch_array($results))
{
	if(isset($kba[$row["KTypNr"]]))
	{
		$kba[$row["KTypNr"]].=', '.$row["KBANr"];
	}
	else $kba[$row["KTypNr"]]=$row["KBANr"];
	
}
print_r($kba);
exit;

//DE
$results2=q("SELECT * FROM vehicles_de WHERE KBA='';", $dbshop, __FILE__, __LINE__);
while($row2=mysqli_fetch_array($results2))
{
	if(isset($kba[$row2["KTypNr"]]))
	{
		q("UPDATE vehicles_de SET KBA='".$kba[$row2["KTypNr"]]."' WHERE id_vehicle=".$row2["id_vehicle"].";", $dbshop, __FILE__, __LINE__);	
		echo $row2["KTypNr"].' / '.$kba[$row2["KTypNr"]];
	}
	
}
//EN
$results2=q("SELECT * FROM vehicles_en WHERE KBA='';", $dbshop, __FILE__, __LINE__);
while($row2=mysqli_fetch_array($results2))
{
	if(isset($kba[$row2["KTypNr"]]))
	{
		q("UPDATE vehicles_en SET KBA='".$kba[$row2["KTypNr"]]."' WHERE id_vehicle=".$row2["id_vehicle"].";", $dbshop, __FILE__, __LINE__);	
		echo $row2["KTypNr"].' / '.$kba[$row2["KTypNr"]];
	}
	
}
//ES
$results2=q("SELECT * FROM vehicles_es WHERE KBA='';", $dbshop, __FILE__, __LINE__);
while($row2=mysqli_fetch_array($results2))
{
	if(isset($kba[$row2["KTypNr"]]))
	{
		q("UPDATE vehicles_es SET KBA='".$kba[$row2["KTypNr"]]."' WHERE id_vehicle=".$row2["id_vehicle"].";", $dbshop, __FILE__, __LINE__);	
		echo $row2["KTypNr"].' / '.$kba[$row2["KTypNr"]];
	}
	
}
//FR
$results2=q("SELECT * FROM vehicles_fr WHERE KBA='';", $dbshop, __FILE__, __LINE__);
while($row2=mysqli_fetch_array($results2))
{
	if(isset($kba[$row2["KTypNr"]]))
	{
		q("UPDATE vehicles_fr SET KBA='".$kba[$row2["KTypNr"]]."' WHERE id_vehicle=".$row2["id_vehicle"].";", $dbshop, __FILE__, __LINE__);	
		echo $row2["KTypNr"].' / '.$kba[$row2["KTypNr"]];
	}
	
}
//IT
$results2=q("SELECT * FROM vehicles_it WHERE KBA='';", $dbshop, __FILE__, __LINE__);
while($row2=mysqli_fetch_array($results2))
{
	if(isset($kba[$row2["KTypNr"]]))
	{
		q("UPDATE vehicles_it SET KBA='".$kba[$row2["KTypNr"]]."' WHERE id_vehicle=".$row2["id_vehicle"].";", $dbshop, __FILE__, __LINE__);	
		echo $row2["KTypNr"].' / '.$kba[$row2["KTypNr"]];
	}
	
}
//PL
$results2=q("SELECT * FROM vehicles_pl WHERE KBA='';", $dbshop, __FILE__, __LINE__);
while($row2=mysqli_fetch_array($results2))
{
	if(isset($kba[$row2["KTypNr"]]))
	{
		q("UPDATE vehicles_pl SET KBA='".$kba[$row2["KTypNr"]]."' WHERE id_vehicle=".$row2["id_vehicle"].";", $dbshop, __FILE__, __LINE__);	
		echo $row2["KTypNr"].' / '.$kba[$row2["KTypNr"]];
	}
	
}
//RU
$results2=q("SELECT * FROM vehicles_ru WHERE KBA='';", $dbshop, __FILE__, __LINE__);
while($row2=mysqli_fetch_array($results2))
{
	if(isset($kba[$row2["KTypNr"]]))
	{
		q("UPDATE vehicles_ru SET KBA='".$kba[$row2["KTypNr"]]."' WHERE id_vehicle=".$row2["id_vehicle"].";", $dbshop, __FILE__, __LINE__);	
		echo $row2["KTypNr"].' / '.$kba[$row2["KTypNr"]];
	}
	
}
//ZH
$results2=q("SELECT * FROM vehicles_zh WHERE KBA='';", $dbshop, __FILE__, __LINE__);
while($row2=mysqli_fetch_array($results2))
{
	if(isset($kba[$row2["KTypNr"]]))
	{
		q("UPDATE vehicles_zh SET KBA='".$kba[$row2["KTypNr"]]."' WHERE id_vehicle=".$row2["id_vehicle"].";", $dbshop, __FILE__, __LINE__);	
		echo $row2["KTypNr"].' / '.$kba[$row2["KTypNr"]];
	}
	
}
echo 'fertig';

?>