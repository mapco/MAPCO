<script>
	function showhide(id)
	{
		$('#submenu'+id).toggle('slow');
//		$('#shopmenu').toggle('slow');
	}
</script>
<?php
	///äöüÄÖÜ UTF-8
	if (!function_exists("mapco_menu2list"))
	{
		function mapco_menu2list($menu, $id, $userrights, $icons=false)
		{
			for($i=0; $i<sizeof($menu["title"]); $i++)
			{
				if ($menu["menuitem_id"][$i]==$id)
				{
					if ( isset($userrights[$menu["link"][$i]]) )
					{
						if ( isset($menu["id_menuitem"][$i]) and isset($_GET["id_menuitem"]) and $menu["id_menuitem"][$i]==$_GET["id_menuitem"] ) $selected=' class="selected"'; else $selected='';

						$children=0;
						for($j=0; $j<sizeof($menu["title"]); $j++)
						{
							if ($menu["id_menuitem"][$i]==$menu["menuitem_id"][$j])
							{
								$children++;
							}
						}

						echo '<li>';
						if ($children>0)
						{
							echo '<a'.$selected.' href="javascript:showhide(\''.$menu["id_menuitem"][$i].'\');" title="'.t($menu["description"][$i]).'">';
							if ($icons) echo '<img src="'.$menu["icon"][$i].'" alt="'.t($menu["description"][$i]).'" title="'.t($menu["description"][$i]).'" />';
							echo t($menu["title"][$i]).'</a>';
						}
						else
						{
							echo '<a'.$selected.' href="'.$menu["link"][$i].'?lang='.$_GET["lang"].'&amp;id_menuitem='.$menu["id_menuitem"][$i].'" title="'.t($menu["description"][$i]).'">';
							if ($icons) echo '<img src="'.$menu["icon"][$i].'" alt="'.t($menu["description"][$i]).'" title="'.t($menu["description"][$i]).'" />';
							echo t($menu["title"][$i]).'</a>';
						}
						if ($children>0)
						{
							echo '	<ul id="submenu'.$menu["id_menuitem"][$i].'">';
							mapco_menu2list($menu, $menu["id_menuitem"][$i], $userrights, $icons);
							echo '	</ul>';
						}
						echo '</li>';
					}
				}
			}
		}
	}


	if (!function_exists("show_mapco_tree"))
	{
		function show_mapco_tree($id_menuitem, $icons=false)
		{
			global $dbweb;
			//read userrights		
			$userrights=array();
			$id_userrole=6; //guest
			if ($_SESSION["id_user"]>0)
			{
				$results=q("SELECT * FROM cms_users WHERE id_user=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
				$row=mysqli_fetch_array($results);
				if ($row["userrole_id"]>0) $id_userrole=$row["userrole_id"];
			}
			$results=q("SELECT * FROM cms_userroles_scripts WHERE userrole_id=".$id_userrole.";", $dbweb, __FILE__, __LINE__);
			while($row=mysqli_fetch_array($results))
			{
				$userrights[$row["script"]]=$row["script"];
			}
			//read menu
			$results=q("SELECT * FROM cms_menuitems WHERE menuitem_id=".$id_menuitem." ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
			$i=0;
			while($row=mysqli_fetch_array($results))
			{
				$menu["id_menuitem"][$i]=$row["id_menuitem"];
				$menu["icon"][$i]=$row["icon"];
				$menu["description"][$i]=$row["description"];
				$menu["title"][$i]=$row["title"];
				$menu["menuitem_id"][$i]=$row["menuitem_id"];
				$menu["link"][$i]=$row["link"];
				$i++;
			}
			//show menu
			mapco_menu2list($menu, $id_menuitem, $userrights, $icons);
		}
	}


	if (!function_exists("show_mapco_menu"))
	{
		function show_mapco_menu($idtag, $icons=false)
		{
			global $dbweb;
			//read userrights		
			$userrights=array();
			$id_userrole=6; //guest
			if ( isset($_SESSION["id_user"]) and $_SESSION["id_user"]>0 )
			{
				$results=q("SELECT * FROM cms_users WHERE id_user=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
				$row=mysqli_fetch_array($results);
				if ($row["userrole_id"]>0) $id_userrole=$row["userrole_id"];
			}
			$results=q("SELECT * FROM cms_userroles_scripts WHERE userrole_id=".$id_userrole.";", $dbweb, __FILE__, __LINE__);
			while($row=mysqli_fetch_array($results))
			{
				$userrights[$row["script"]]=$row["script"];
			}
			//read menu
			if ($idtag=="shopmenu")	
			{
				$results2=q("SELECT * FROM cms_menuitems WHERE menu_id=".$row["id_menu"]." AND menuitem_id>0;", $dbweb, __FILE__, __LINE__);
				while($row2=mysqli_fetch_array($results2))
				{
					$not_empty[$row2["menuitem_id"]]=$row2["menuitem_id"];
				}
			}
			$results=q("SELECT * FROM cms_menus WHERE idtag='".$idtag."';", $dbweb, __FILE__, __LINE__);
			$row=mysqli_fetch_array($results);
			$results=q("SELECT * FROM cms_menuitems WHERE menu_id=".$row["id_menu"]." ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
			$i=0;
			while($row=mysqli_fetch_array($results))
			{
				if ($idtag!="shopmenu" or $row["menuitem_id"]>0 or isset($not_empty[$row["id_menuitem"]]))	
				{
					$menu["id_menuitem"][$i]=$row["id_menuitem"];
					$menu["icon"][$i]=$row["icon"];
					$menu["description"][$i]=$row["description"];
					$menu["title"][$i]=$row["title"];
					$menu["menuitem_id"][$i]=$row["menuitem_id"];
					$menu["link"][$i]="shop.php";
					$i++;
				}

				
			}
			//show menu
			if ($idtag=="shopmenu")	echo '<ul id="'.$idtag.'">';
			else echo '<ul id="'.$idtag.'">';
			mapco_menu2list($menu, 0, $userrights, $icons);
			echo '</ul>';
		}
	}
?>