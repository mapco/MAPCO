<?php
	include("../functions/cms_t.php");
	
	check_man_params(array("id_user" => "numericNN", "username" => "textNN", "name" => "text", "userrole" => "numeric", "language" => "numeric", "country" => "text"));

	$check_name = true;
	$check_mail = true;
	$save_name = true;
	$save_mail = true;
	
	$status_name = 0;
	$status_mail = 0;

	$res = q("SELECT username, usermail, name, userrole_id, language_id, origin FROM cms_users WHERE id_user=".$_POST['id_user'].";",$dbweb, __FILE__,__LINE__);
	$user = mysqli_fetch_assoc($res);
		
	if ( $_POST['username'] == $user['username'] ) 
	{ 
		$status_name =	1;
		$check_name = false;
		$save_name = false;	
	}

	if ( $_POST['usermail'] == $user['usermail'] ) 
	{
		$status_mail =	1;
		$check_mail = false;
		$save_mail = false;
	}

	if ( $check_name == true || $check_mail == true )
	{		
		$res = q("SELECT site_id FROM cms_users_sites WHERE user_id=".$_POST['id_user'].";",$dbweb, __FILE__,__LINE__);
		while ( $row= mysqli_fetch_assoc($res) )
		{
			$user_sites[] = $row['site_id'];
		}
		
		$num_sites = sizeof($user_sites);
		$user_sites = implode(",", $user_sites);
		
		$sql = "SELECT DISTINCT us.user_id, u.username, u.usermail FROM cms_users_sites AS us, cms_users AS u WHERE us.site_id";
		
		if ( $num_sites>0 )
		{
			$sql .= " IN (".$user_sites.")";
		}
		else
		{
			$sql .= "=".$user_sites;
		}
		 
		$sql .= " AND us.user_id!=".$_POST['id_user']." AND us.user_id=u.id_user;";
		
		$res = q($sql,$dbweb, __FILE__,__LINE__);
		while ( $row = mysqli_fetch_assoc($res) )
		{
			if ( $_POST['username'] == $row['username'] )
			{
				$save_name = false;
			}
			
			if ( $_POST['usermail'] == $row['usermail'] )
			{
				$save_mail = false;
			}
		}
		
		if ($save_name == true)
		{
			$status_name = 3;
			$data['username'] = $_POST['username'];
		}
		else if ( $check_name == true )
		{
			$status_name =	2;
		}
		
		if ($save_mail == true)
		{
			$status_mail = 3;
			$data['usermail'] = $_POST['usermail'];
		}
		else if ( $check_mail == true )
		{
			$status_mail =	2;
		}		
	}	
		
	if ( $_POST['name'] != $user['name']) {	$data['name'] = $_POST['name']; }
	if ( $_POST['userrole'] != $user['userrole_id']) { $data['userrole_id'] = $_POST['userrole']; }
	if ( $_POST['language'] != $user['language_id']) { $data['language_id'] = $_POST['language']; }
	if ( $_POST['country'] != $user['origin']) { $data['origin'] = $_POST['country']; }
	
	if ( isset($data) )
	{
		$where = 'WHERE id_user='.$_POST['id_user'];
		q_update('cms_users', $data, $where, $dbweb, __FILE__, __LINE__);
		$num_rows = mysqli_num_rows($dbweb);
		if ( $num_rows==1 )
		{
			$status = 3;
		}
	}
	
	print '<status_name>'.$status_name.'</status_name>'."\n";
	print '<status_mail>'.$status_mail.'</status_mail>'."\n";
?>