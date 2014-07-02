<?php

	//cut MIME part
	if( strpos($_POST["Data"], ",") )
	{
		$split=explode(",", $_POST["Data"]);
		$_POST["Data"]=$split[1];
	}
	
	//decode Base64 data
	$Data=base64_decode($_POST["Data"]);

	//save binary to file
	$success = file_put_contents($_POST["Filename"], $Data, FILE_APPEND);
	if( !$success )
	{
		echo '<TempFileUpdate>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Data>'.$Data.'</Data>'."\n";
		echo '	<Filename>'.$_POST["Filename"].'</Filename>'."\n";
		echo '</TempFileUpdate>'."\n";
		exit;
	}
	
	echo '<TempFileUpdate>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Filename>'.$_POST["Filename"].'</Filename>'."\n";
	echo '</TempFileUpdate>'."\n";

?>