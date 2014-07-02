<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Fahrzeugdatenbank</title>
<script src="http://code.jquery.com/jquery-latest.js"></script>
<link rel="stylesheet" type="text/css" href="style.css" />
</head>

<body>

<script language="javascript">
function upload(item_id, original_id, filename)
{
	$.post("modules/ebay_images_upload.php", { item_id:item_id , original_id:original_id, filename:filename }, function (data) { alert(data); });
}
</script>

<div id="ergebnis">
</div>

<?php

include("config.php");

//vorhandene Ebay Autopartner Bilder auslesen
$results=q("SELECT * FROM ebay_items_files WHERE account=2;", $dbshop, __FILE__, __LINE__);
while($row=mysqli_fetch_array($results))
{
	$file[$row["file_id"]]=$row["file_id"];
}

//zu vorhandenen Bildern die original_id bestimmen
$results2=q("SELECT * FROM cms_files WHERE original_id>0;", $dbweb, __FILE__, __LINE__);
while($row2=mysqli_fetch_array($results2))
{	
	if (isset($file[$row2["id_file"]]))
	{
		$original[$row2["original_id"]]=$row2["original_id"];	
	}
}

$count=0;

//Bilder erstellen
$results3=q("SELECT * FROM shop_items_files;", $dbshop, __FILE__, __LINE__);
while($row3=mysqli_fetch_array($results3))
{	
	$results4=q("SELECT * FROM cms_files WHERE id_file=".$row3["file_id"]." LIMIT 1;", $dbweb, __FILE__, __LINE__);
	if(mysqli_num_rows($results4)>0)
	{
		$row4=mysqli_fetch_array($results4);
		if (!isset($original[$row4["original_id"]]))
		{
			$count++;
			echo $row3["item_id"].' item_id / ';
			echo $row4["original_id"].' original_id / ';
			echo $row4["filename"].' filename<br />';
			echo '<script language="javascript"> upload('.$row3["item_id"].', '.$row4["original_id"].', '.$row4["filename"].'); </script>';
			if($count==3) exit;
		}
	}



}

/*

$results3=q("SELECT * FROM cms_files WHERE original_id=0;", $dbweb, __FILE__, __LINE__);
while($row3=mysqli_fetch_array($results3))
{	
	if (!isset($original[$row3["id_file"]]))
	{
		echo $row3["id_file"].'<br />';
		$results4=q("SELECT * FROM shop_items_files WHERE file_id=".$row3["id_file"]." LIMIT 1;", $dbshop, __FILE__, __LINE__);
		if(mysqli_num_rows($results4)>0)
		{
			$row4=mysqli_fetch_array($results4);
			echo $row4["item_id"].' item_id<br />';
			echo $row3["id_file"].' original_id<br />';
			echo $row3["filename"].' filename<br />';

//			echo '<script language="javascript"> upload('.$row4["item_id"].', '.$row3["id_file"].', '.$row3["filename"].'); </script>';

			exit;					
		}
	}
}
*/
?>

</body>
</html>

