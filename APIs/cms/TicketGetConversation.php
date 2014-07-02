<?php

	//Kommunikationspartner idendifizieren
	$sql="select * from cms_conversations where id_conv = '".$_POST["conv_id"]."'";
	$results=q($sql, $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results) )  
	{
		$sql2="select * from cms_contacts where idCmsUser = '".$row["conv_start_userid"]."'";
		$results2=q($sql2, $dbweb, __FILE__, __LINE__);
		while($row2=mysqli_fetch_array($results2) )  
		{ 
			$contacts[$row2["idCmsUser"]]["Name"]=$row2["firstname"]." ".$row2["lastname"];
			$contacts[$row2["idCmsUser"]]["Position"]=$row2["position"];
		}

		$sql2="select * from cms_contacts where idCmsUser = '".$row["conv_partner_userid"]."'";
		$results2=q($sql2, $dbweb, __FILE__, __LINE__);
		while($row2=mysqli_fetch_array($results2) )  
		{ 
			$contacts[$row2["idCmsUser"]]["Name"]=$row2["firstname"]." ".$row2["lastname"];
			$contacts[$row2["idCmsUser"]]["Position"]=$row2["position"];
		}
	}

	//---------------------------------------------------------------
	$sql="select * from cms_conversations_posts where id_conv = '".$_POST["conv_id"]."' order by post_date desc";
	$results=q($sql, $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )  
	{
		$sql2 = "select title, article from cms_articles where id_article = '".$row["id_cms_article"]."'";
		$results2=q($sql2, $dbweb, __FILE__, __LINE__);
		while( $row2=mysqli_fetch_array($results2) ) 
		{
			$msg_history.='<p><b>'.$contacts[$row["post_userid"]]["Name"].'</b> <small>'.$contacts[$row["post_usermid"]]["Position"].'</small> schrieb am <i>'.date("d.m.Y H:i", $row["post_date"]).'</i><br /><br />';
			$msg_history.=nl2br($row2["article"]).'<br />';
			$msg_history.='<span style="alignment:center">------------------------------------------------------</span></p>';
				
		}
	}
		
	echo $msg_history;
	
?>