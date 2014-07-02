<?php
echo '<div class="box_small">';
echo '<h3>'.t("Standort-Infos").'</h3>';
echo '<div class="box_small_content">';

$domain = str_replace("www.", "", $_SERVER['HTTP_HOST']);

$results=q("SELECT * FROM cms_contacts_locations where website LIKE '%".$domain."%' LIMIT 1 ;", $dbweb, __FILE__, __LINE__);
$row=mysqli_fetch_array($results);

echo '<h4>'.t("Anschrift").':</h4>';
echo $row["street"].' '.$row["streetnr"];
echo '<br />';
echo $row["zipcode"].' '.$row["city"];
echo '<br />';
echo '<h4>'.t("Kontakt").':</h4>';
echo 'Tel.: '.$row["phone"];
echo '<br />';
echo 'Fax: '.$row["fax"];
echo '<br />';
echo 'E-Mail: <a style="float:none;" href="mailto:'.$row["mail"].'">'.$row["mail"].'</a>';
echo '<br />';
echo '<h4>'.t("Öffnungszeiten").':</h4>';
$day = date("w");
echo '<table style="border:0;">';
echo '<tr';
if($day==1) echo ' style="font-weight:bold"';
echo '><td>'.t("Montag").'</td><td>'.$row["monday"].'</td></tr>';
echo '<tr';
if($day==2) echo ' style="font-weight:bold"';
echo '><td>'.t("Dienstag").'</td><td>'.$row["tuesday"].'</td></tr>';
echo '<tr';
if($day==3) echo ' style="font-weight:bold"';
echo '><td>'.t("Mittwoch").'</td><td>'.$row["wednesday"].'</td></tr>';
echo '<tr';
if($day==4) echo ' style="font-weight:bold"';
echo '><td>'.t("Donnerstag").'</td><td>'.$row["thursday"].'</td></tr>';
echo '<tr';
if($day==5) echo ' style="font-weight:bold"';
echo '><td>'.t("Freitag").'</td><td>'.$row["friday"].'</td></tr>';
echo '<tr';
if($day==6) echo ' style="font-weight:bold"';
echo '><td>'.t("Samstag").'</td><td>'.$row["saturday"].'</td></tr>';
echo '<tr';
if($day==0) echo ' style="font-weight:bold"';
echo '><td>'.t("Sonntag").'</td><td>'.$row["sunday"].'</td></tr>';
echo '</table>';

echo '</div>';
echo '</div>';

?>
