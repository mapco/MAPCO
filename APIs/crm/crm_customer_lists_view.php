<?php
	//LISTEN LADEN
	
	//GET RC-USERLISTS
	//USERRoles 11 & 14
	$res=q("SELECT * FROM cms_users WHERE userrole_id = 11 OR userrole_id = 14 ;", $dbweb, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($res))
	{
		$rc_userlist[$row["id_user"]]=1;
	}
	
	$public_list=array();
	$private_list=array();
	$rc_list=array();
	$admin_list=array();
	$res=q("SELECT * FROM crm_costumer_lists;", $dbweb, __FILE__, __LINE__);
	while ($row_list=mysqli_fetch_array($res))
	{
		if ($row_list["private"]==1)
		{
			$admin_list[$row_list["id_list"]]=$row_list["firstmod_user"];

			if ($row_list["firstmod_user"]==$_SESSION["id_user"]) 
			{
				$private_list[$row_list["id_list"]]=$row_list["firstmod_user"];
			}
			//FUNKE VIEW
			elseif($_SESSION["id_user"]==22714)
			{
				
				if (isset($rc_userlist[$row_list["firstmod_user"]])) 
				{
					$rc_list[$row_list["id_list"]]=$row_list["firstmod_user"];
				}
			}
		}
		else 
		{
			$public_list[$row_list["id_list"]]=$row_list["firstmod_user"];
		}
		$list_title[$row_list["id_list"]]=$row_list["title"];
	}
	
		
	
	//Reminder laden
	$remind_now=0;
	$remind_future=0;
	$today=number_format(date("Ymd"));
	$res_reminder=q("SELECT * FROM crm_communications WHERE firstmod_user = ".$_SESSION["id_user"]." AND NOT reminder = 0;", $dbweb, __FILE__, __LINE__);
	while ($row_reminder=mysqli_fetch_array($res_reminder))
	{
		$reminder[$row_reminder["id_communication"]]=$row_reminder["reminder"];
		if ($today<=number_format(date("Ymd", $row_reminder["reminder"]))) $remind_future++; else $remind_now++;	
	}

	echo '<ul class="orderlist" id="lists" style="width:312px; margin:0px 5px 5px 5px;">';
	echo '<li  style="background-color:#aaa; cursor:pointer">';
	echo '	<div style="width:236px; text-align:left;" id="menu_searchbox"><a href="javascript:ListType=\'searchbox\';show_searchbox();">Kundensuche</a></div>';
	echo '	<div style="width:24px; float:right;">';
	echo '<div style="width:60px;height:24px; float:right;">';
	echo '</div>';
	echo '	</div>';
	echo '</li>';
	
	echo '<li class="header">';
	echo '	<div style="width:236px; text-align:left;">Listen</div>';
//	echo '	<div style="width:24px; float:right;">';
	echo '<div style="width:60px; float:right;">';
	echo '<img alt="Liste hinzufügen" onclick="add_customer_list();" src="'.PATH.'images/icons/24x24/note_add.png" style="float:right;" title="Liste hinzufügen" />';
	echo '</div>';
	echo '	</div>';
	echo '</li>';
//++++++++++++++++++++++++++
	echo '<li style="background-color:#aaa; cursor:pointer">';
	echo '	<div id="privateList_head" style="width:280px; text-align:left;">';
		if (sizeof($private_list)==0)
		{
			echo 'private Listen ('.sizeof($private_list).')';
		}
		else 
		{
			echo '<a href="javascript:ListType=\'privateList\'; show_customerLists();">private Listen ('.sizeof($private_list).')</a>';
		}
	echo '</div>';
	echo '</li>';
	if (sizeof($private_list)>0)
	{
	while ( list ( $id_list, $user) = each ($private_list))
		{
			
			echo '<li class="privateLists" id="menuList'.$id_list.'" style="display:none">';
			echo '	<div style="width:200px; text-align:left;">&nbsp;&nbsp;&nbsp;';
			if ($_POST["id_list"]*1==$id_list) $style=' style="font-weight:bold; text-align:left;"'; else $style=' style="text-align:left;"';
			echo '		<a'.$style.' href="javascript:id_list='.$id_list.'; show_customerLists(); show_customer_list();">'.$list_title[$id_list].'</a>';
			echo '	</div>';
			echo '	<div style="width:60px; float:right;">';
			if ( $_SESSION["userrole_id"]==1 or $_SESSION["id_user"]==$user)
			{
				//Liste löschen
				echo '		<img src="'.PATH.'images/icons/24x24/remove.png" onclick="del_customer_list('.$id_list.');" alt="Liste löschen" title="Liste löschen" />';
				//Liste bearbeiten
				echo '		<img src="'.PATH.'images/icons/24x24/edit.png" onclick="update_customer_list('.$id_list.', \''.addslashes(stripslashes($list_title[$id_list])).'\', 1);" alt="Liste bearbeiten" title="Liste bearbeiten" />';
			}
			echo '	</div>';
			echo '</li>';
		}
	}
//+++++++++++++++++++++++++++++++++++++++++
	echo '<li style="background-color:#aaa; cursor:pointer">';
	echo '	<div id="publicList_head" style="width:280px; text-align:left;">';
		if (sizeof($public_list)==0)
		{
			echo 'öffentliche Listen ('.sizeof($public_list).')';
		}
		else 
		{
			echo '<a href="javascript:ListType=\'publicLists\'; show_customerLists();">öffentliche Listen ('.sizeof($public_list).')</a>';
		}
	echo '</div>';
	echo '</li>';
	if (sizeof($public_list)>0)
	{
		while ( list ( $id_list, $user) = each ($public_list))
		{
			echo '<li class="publicLists" id="menuList'.$id_list.'" style="display:none">';
			echo '	<div style="width:200px; text-align:left;" >&nbsp;&nbsp;&nbsp;';
			if ($_POST["id_list"]*1==$id_list) $style=' style="font-weight:bold; text-align:left;"'; else $style=' style="text-align:left;"';
			echo '		<a'.$style.' href="javascript:id_list='.$id_list.'; show_customerLists(); show_customer_list();">'.$list_title[$id_list].'</a>';
			echo '	</div>';
			echo '	<div style="width:60px; float:right;">';
			if ( $_SESSION["userrole_id"]==1 or $_SESSION["id_user"]==$user)
			{
				//Liste löschen
				echo '		<img src="'.PATH.'images/icons/24x24/remove.png" onclick="del_customer_list('.$id_list.');" alt="Liste löschen" title="Liste löschen" />';
				//Liste bearbeiten
				echo '		<img src="'.PATH.'images/icons/24x24/edit.png" onclick="update_customer_list('.$id_list.', \''.addslashes(stripslashes($list_title[$id_list])).'\', 0);" alt="Liste bearbeiten" title="Liste bearbeiten" />';
			}
			echo '	</div>';
			echo '</li>';
		}
	echo '</div>';
	}
//+++++++++++++++++++++++++++++++++++++++
	if (sizeof($reminder)>0)
	{
		echo '<li style="background-color:#aaa; cursor:pointer">';
		echo '	<div id="reminder_head" style="width:280px; text-align:left;">';
		echo '		<a href="javascript:ListType=\'reminder\'; show_reminder();">Kontakt-Erinnerungen ('.sizeof($reminder).')</a>';
		echo '</div>';
		echo '</li>';
		echo '<div >';
		echo '<li class="reminder_now reminder" style="display:none">';
		echo '	<div style="width:280px; text-align:left;">';
		if ($remind_now>0)
		{
			echo '		<a'.$style.' href="javascript:reminder_type=\'now\'; show_customers_reminders(\'now\'); show_reminder(\'now\')">aktuelle ('.$remind_now.')</a>';
		}
		else
		{
			echo 'aktuelle (0)';
		}
		echo '	</div>';
		echo '</li>';
		echo '<li class="reminder_later reminder" style="display:none">';
		echo '	<div style="width:280px; text-align:left;">';
		if ($remind_future>0)
		{
			echo '		<a'.$style.' href="javascript:reminder_type=\'later\';show_customers_reminders(\'later\'); show_reminder(\'later\');">spätere ('.$remind_future.')</a>';
		}
		else
		{
			echo 'spätere (0)';
		}
		echo '	</div>';
		echo '</li>';
	}
	
	//FUNKE VIEW+++++++++++++++++++++++++++++++++++++
if ($_SESSION["id_user"]==22714)
{
	echo '<li style="background-color:#aaa; cursor:pointer">';
	echo '	<div id="RCList_head" style="width:280px; text-align:left;">';
		if (sizeof($rc_list)==0)
		{
			echo "Regionalcenter Listen (".sizeof($rc_list).")";
		}
		else 
		{
			echo '<a href="javascript:ListType=\'RCList\'; show_customerLists();">Regionalcenter Listen ('.sizeof($rc_list).')</a>';
		}
	echo '</div>';
	echo '</li>';

	if (sizeof($rc_list)>0)
	{
	while ( list ( $id_list, $user) = each ($rc_list))
		{
			
			echo '<li class="RCLists" id="menuList'.$id_list.'" style="display:none">';
			echo '	<div style="width:200px; text-align:left;">&nbsp;&nbsp;&nbsp;';
			if ($_POST["id_list"]*1==$id_list) $style=' style="font-weight:bold; text-align:left;"'; else $style=' style="text-align:left;"';
			echo '		<a'.$style.' href="javascript:id_list='.$id_list.'; show_customerLists(); show_customer_list();">'.$list_title[$id_list].'</a>';
			echo '	</div>';
			echo '	<div style="width:60px; float:right;">';
			if ( $_SESSION["userrole_id"]==1 or $_SESSION["id_user"]==$user)
			{
				//Liste löschen
				echo '		<img src="'.PATH.'images/icons/24x24/remove.png" onclick="del_customer_list('.$id_list.');" alt="Liste löschen" title="Liste löschen" />';
				//Liste bearbeiten
				echo '		<img src="'.PATH.'images/icons/24x24/edit.png" onclick="update_customer_list('.$id_list.', \''.addslashes(stripslashes($list_title[$id_list])).'\', 1);" alt="Liste bearbeiten" title="Liste bearbeiten" />';
			}
			echo '	</div>';
			echo '</li>';
		}
	}
}

	//ADMIN VIEW+++++++++++++++++++++++++++++++++++++
if ($_SESSION["id_user"]==21371)
{
	echo '<li style="background-color:#aaa; cursor:pointer">';
	echo '	<div id="RCList_head" style="width:280px; text-align:left;">';
		if (sizeof($admin_list)==0)
		{
			echo "Alle Listen (".sizeof($admin_list).")";
		}
		else 
		{
			echo '<a href="javascript:ListType=\'RCList\'; show_customerLists();">Alle Listen ('.sizeof($admin_list).')</a>';
		}
	echo '</div>';
	echo '</li>';

	if (sizeof($admin_list)>0)
	{
	while ( list ( $id_list, $user) = each ($admin_list))
		{
			
			echo '<li class="RCLists" id="menuList'.$id_list.'" style="display:none">';
			echo '	<div style="width:200px; text-align:left;">&nbsp;&nbsp;&nbsp;';
			if ($_POST["id_list"]*1==$id_list) $style=' style="font-weight:bold; text-align:left;"'; else $style=' style="text-align:left;"';
			echo '		<a'.$style.' href="javascript:id_list='.$id_list.'; show_customerLists(); show_customer_list();">'.$list_title[$id_list].'</a>';
			$results=q("SELECT * FROM cms_users WHERE id_user=".$user.";", $dbweb, __FILE__, __LINE__);
			$row=mysqli_fetch_array($results);
			echo '	<br />'.$row["username"];
			echo '	</div>';
			echo '	<div style="width:60px; float:right;">';
			if ( $_SESSION["userrole_id"]==1 or $_SESSION["id_user"]==$user)
			{
				//Liste löschen
				echo '		<img src="'.PATH.'images/icons/24x24/remove.png" onclick="del_customer_list('.$id_list.');" alt="Liste löschen" title="Liste löschen" />';
				//Liste bearbeiten
				echo '		<img src="'.PATH.'images/icons/24x24/edit.png" onclick="update_customer_list('.$id_list.', \''.addslashes(stripslashes($list_title[$id_list])).'\', 1);" alt="Liste bearbeiten" title="Liste bearbeiten" />';
			}
			echo '	</div>';
			echo '</li>';
		}
	}
}
	echo '</ul>';
