<?php
	include("../functions/cms_t.php");

	//default first page and default entries
	$query="SELECT id_job FROM ebay_jobs";
	if( isset($_POST["id_account"]) and $_POST["id_account"]>0 )
	{
		if( strpos($query, "WHERE") === false ) $query .= " WHERE "; else $query .= " AND ";
		$query.="account_id=".$_POST["id_account"];
	}
	if( isset($_POST["jobType"]) and $_POST["jobType"]!="Alle" )
	{
		if( strpos($query, "WHERE") === false ) $query .= " WHERE "; else $query .= " AND ";
		$query.="jobType='".$_POST["jobType"]."'";
	}
	if( isset($_POST["jobStatus"]) and $_POST["jobStatus"]!="Alle" )
	{
		if( strpos($query, "WHERE") === false ) $query .= " WHERE "; else $query .= " AND ";
		$query.="jobStatus='".$_POST["jobStatus"]."'";
	}
	$query.=";";
	$results=q($query, $dbshop, __FILE__, __LINE__);
	if( !isset($_POST["page"]) ) $_POST["page"]=1;
	$entries=50;

	//get number of entries
	$pages=ceil(mysqli_num_rows($results)/$entries);

	//select jobs
	$query="SELECT * FROM ebay_jobs";
	if( isset($_POST["id_account"]) and $_POST["id_account"]>0 )
	{
		if( strpos($query, "WHERE") === false ) $query .= " WHERE "; else $query .= " AND ";
		$query.="account_id=".$_POST["id_account"];
	}
	if( isset($_POST["jobType"]) and $_POST["jobType"]!="Alle" )
	{
		if( strpos($query, "WHERE") === false ) $query .= " WHERE "; else $query .= " AND ";
		$query.="jobType='".$_POST["jobType"]."'";
	}
	if( isset($_POST["jobStatus"]) and $_POST["jobStatus"]!="Alle" )
	{
		if( strpos($query, "WHERE") === false ) $query .= " WHERE "; else $query .= " AND ";
		$query.="jobStatus='".$_POST["jobStatus"]."'";
	}
	$query .= " ORDER BY creationTime DESC LIMIT ".(($_POST["page"]-1)*$entries).", $entries;";
	$results=q($query, $dbshop, __FILE__. __LINE__);

	//output
	echo '<JobsGetResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '	<Pages>'.$pages.'</Pages>'."\n";
	while( $row=mysqli_fetch_array($results) )
	{
		echo '<Job>';
		$keys=array_keys($row);
		
		for($i=0; $i<sizeof($keys); $i++)
		{
			if( !is_numeric($keys[$i]) )
				echo '	<'.$keys[$i].'>'.str_replace("&", "&amp;", ($row[$keys[$i]])).'</'.$keys[$i].'>'."\n";
		}
		echo '</Job>';
	}
	echo '</JobsGetResponse>'."\n";

?>