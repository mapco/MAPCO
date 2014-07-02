<?php
	include("../functions/cms_t.php");

	$results=q("SELECT * FROM kunde LIMIT 1;", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$keys=array_keys($row);
	
	$query="SELECT * FROM kunde";
	if( isset($_POST["search"]) and $_POST["search"]!="" )
	{
		$_POST["search"]=str_replace("*", "", $_POST["search"]);
		if( strpos($query, "WHERE") === false ) $query .= " WHERE "; else $query .= " OR ";
		$query.="ADR_ID='".mysqli_real_escape_string($dbshop, $_POST["search"])."'";
	}
	$query.=" LIMIT 1;";
	$results=q($query, $dbshop, __FILE__, __LINE__);
	
	echo '<AddressGetResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	while($row=mysqli_fetch_array($results))
	{
		echo '<KUNDE>';
		for($i=0; $i<sizeof($keys); $i++)
		{
			if( !is_numeric($keys[$i]) )
				echo '	<'.$keys[$i].'><![CDATA['.$row[$keys[$i]].']]></'.$keys[$i].'>'."\n";
		}
		echo '</KUNDE>';
	}
	echo '</AddressGetResponse>'."\n";

?>