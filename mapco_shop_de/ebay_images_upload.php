<?php
include("modules/config.php");

$item_id = $_POST["item_id"];
$filename = $_POST["filename"];
$original_file_id = $_POST["original_id"];
$druck_filename = '../files/'.bcdiv($original_file_id, 1000, 0).'/'.$original_file_id.'.jpg';


//create Autopartner ebay image
q("INSERT INTO cms_files (filename, extension, filesize, description, original_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".$filename."', 'jpg', 0, '', ".$original_file_id.", ".time().", 0, ".time().", 0);", $dbweb, __FILE__, __LINE__);
$file_id=mysqli_insert_id($dbweb);
$dir=bcdiv($file_id, 1000, 0);
if (!file_exists("../files/".$dir)) mkdir("../files/".$dir);
$web_filename='../files/'.$dir.'/'.$file_id.'.jpg';
require_once('../phpThumb/phpthumb.class.php');
$phpThumb = new phpThumb();
$phpThumb->setSourceFilename('../'.$druck_filename);
$phpThumb->w = 540;
$phpThumb->h = 380;
$phpThumb->aoe = 1; //vergrößere kleinere fotos
$phpThumb->config_output_format = 'jpeg';
$phpThumb->config_error_die_on_error = false;
if ($phpThumb->GenerateThumbnail())
{
	if (!$phpThumb->RenderToFile("../ebay_tmp.jpg"))
	{
		echo 'ERROR: '.implode("\n", $phpThumb->debugmessages);
	}
}
else
{
	echo 'ERROR: '.implode("\n", $phpThumb->debugmessages);
}

$phpThumb = new phpThumb();
$phpThumb->setSourceFilename("../../images/library/rahmen_bg.jpg");
$phpThumb->w = 600;
$phpThumb->h = 400;
$phpThumb->aoe = 1; //vergrößere kleinere fotos
$phpThumb->setParameter('fltr', 'wmi|../ebay_tmp.jpg|C|100');
$phpThumb->setParameter('fltr', 'wmi|../../images/library/rahmen_ap.png|C|100');
$phpThumb->config_output_format = 'jpeg';
$phpThumb->config_error_die_on_error = false;
if ($phpThumb->GenerateThumbnail())
{
	if (!$phpThumb->RenderToFile('../'.$web_filename))
	{
		echo 'ERROR: '.implode("\n", $phpThumb->debugmessages);
	}
}
else
{
	echo 'ERROR: '.implode("\n", $phpThumb->debugmessages);
}
q("UPDATE cms_files SET filesize=".filesize($web_filename)." WHERE id_file=".$file_id.";", $dbweb, __FILE__, __LINE__);
q("UPDATE shop_items SET lastmod=".time()." WHERE id_item=".$item_id.";", $dbshop, __FILE__, __LINE__);

$results=q("SELECT * FROM ebay_items_files WHERE item_id=".$item_id." ORDER BY ordering DESC;", $dbshop, __FILE__, __LINE__);
$row=mysqli_fetch_array($results);
q("INSERT INTO ebay_items_files_ap (item_id, file_id, active, ordering) VALUES (".$item_id.", ".$file_id.", 1, ".($row["ordering"]+1).");", $dbshop, __FILE__, __LINE__);

echo 'Ebay Bild für Item_id:'.$item_id.' erfolgreich angelegt';

?>