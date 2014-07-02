<?php
	include("../functions/cms_t.php");

	if( !isset($_POST["fields"]) ) $_POST["fields"]="*";
	$results=q("SELECT ".$_POST["fields"]." FROM cms_users LIMIT 1;", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_assoc($results);
	$keys=array_keys($row);

	$query="SELECT ".$_POST["fields"]." FROM cms_users";
	if( isset($_POST["search"]) and $_POST["search"]!="" )
	{
		if( strpos($query, "WHERE") === false ) $query .= " WHERE "; else $query .= " OR ";
		$query.="username LIKE '%".mysqli_real_escape_string($dbweb,$_POST["search"])."%'";
		if( strpos($query, "WHERE") === false ) $query .= " WHERE "; else $query .= " OR ";
		$query.="name LIKE '%".mysqli_real_escape_string($dbweb,$_POST["search"])."%'";
		if( strpos($query, "WHERE") === false ) $query .= " WHERE "; else $query .= " OR ";
		$query.="usermail LIKE '%".mysqli_real_escape_string($dbweb,$_POST["search"])."%'";
	}
	if( isset($_POST["limit"]) ) $query.=" LIMIT ".$_POST["limit"];
	$query.=";";
	$results=q($query, $dbweb, __FILE__, __LINE__);
	
	while($row=mysqli_fetch_assoc($results))
	{
		echo '<User>'."\n";
		for($i=0; $i<sizeof($keys); $i++)
		{
			if ( $row[$keys[$i]]=="" or is_numeric($row[$keys[$i]]) )
				echo '	<'.$keys[$i].'>'.$row[$keys[$i]].'</'.$keys[$i].'>'."\n";
			else
				echo '	<'.$keys[$i].'><![CDATA['.$row[$keys[$i]].']]></'.$keys[$i].'>'."\n";
		}
		echo '</User>'."\n";
	}

?>