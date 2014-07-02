<?php
	function createPassword($length)
	{
		$chars = "1234567890abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$i = 0;
		$password = "";
		while ($i <= $length) {
			$password .= $chars{mt_rand(0,strlen($chars)-1)};
			$i++;
		}
		return $password;
	}

	//get filename
	do
	{
		if( !isset($_POST["extension"]) ) $_POST["extension"]="tmp";
		$filename2="../temp/".createPassword(20).'.'.$_POST["extension"];
		$filename=substr($filename2, 3);
	}
	while( file_exists($filename2) );
	
	//create file
	$handle=fopen($filename2, "w");
	
	echo '<TempFileAdd>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<File>'.str_replace("../", "", $filename2).'</File>'."\n";
	echo '	<Filename>'.$filename2.'</Filename>'."\n";
	echo '	<Path>'.PATH.$filename.'</Path>'."\n";
	echo '</TempFileAdd>'."\n";

?>