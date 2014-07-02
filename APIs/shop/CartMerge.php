<?php
	
	check_man_params(array( "mode" => "text" ));
	
	if(isset($_POST["for_link"]) and $_POST["for_link"]!='') //Weiterleitung
	{
		if($_POST["mode"]=="merge")
		{
			$results=q("UPDATE shop_carts SET user_id=".$_POST["for_user_id"].", shop_id=".$_POST["for_shop_id"]." WHERE session_id='".session_id()."' AND user_id=0;", $dbshop, __FILE__, __LINE__);
		}
		
		if($_POST["mode"]=="delete")
		{
			$results=q("DELETE FROM shop_carts WHERE session_id='".session_id()."' AND user_id=0;", $dbshop, __FILE__, __LINE__);
		}
	}
	else //keine Weiterleitung
	{
		if($_POST["mode"]=="merge")
		{
			$results=q("UPDATE shop_carts SET user_id=".$_SESSION["id_user"]." WHERE session_id='".session_id()."' AND user_id=0;", $dbshop, __FILE__, __LINE__);
		}
		
		if($_POST["mode"]=="delete")
		{
			$results=q("DELETE FROM shop_carts WHERE session_id='".session_id()."' AND user_id=0;", $dbshop, __FILE__, __LINE__);
		}
	}

?>