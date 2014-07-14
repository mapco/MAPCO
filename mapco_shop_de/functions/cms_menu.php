<?php
	///äöüÄÖÜ UTF-8
	if (!function_exists("menu2list")) {
		function menu2list($menu, $id, $userrights, $icons=false)
		{
			if (sizeof($menu)>0) {
				for($i=0; $i<sizeof($menu["title"]); $i++)
				{
					if ($menu["menuitem_id"][$i]==$id)
					{
						$parse=parse_url($menu["link"][$i]);
						$link=$parse["path"];
						if ( $menu["local"][$i]==1 or isset($userrights[$link])) {
							if (isset($_GET["id_menuitem"]) and $menu["id_menuitem"][$i]==$_GET["id_menuitem"]) $selected=' class="selected"'; else $selected='';
							echo '<li>';
							if ( $menu["home"][$i]==1 ) $link = PATHLANG;
							elseif ( !empty($menu["alias"][$i]) ) $link = PATHLANG . $menu["alias"][$i];
							else $link=PATH.$menu["link"][$i].'?lang=' . $_GET["lang"] . '&id_menuitem=' . $menu["id_menuitem"][$i];
							echo '<a'.$selected.' href="'.str_replace(" ", "%20", $link).'" title="'.$menu["description"][$i].'">';
							if ($icons) echo '<img src="'.PATH.$menu["icon"][$i].'" alt="'.$menu["description"][$i].'" title="'.$menu["description"][$i].'" />';
							echo $menu["title"][$i].'</a>';
							$children=0;
							for($j=0; $j<sizeof($menu["title"]); $j++)
							{
								if ($menu["id_menuitem"][$i]==$menu["menuitem_id"][$j])
								{
									$children++;
								}
							}
							if ($children>0) {
								echo '	<ul>';
								menu2list($menu, $menu["id_menuitem"][$i], $userrights, $icons);
								echo '	</ul>';
							}
							echo '</li>';
						}
					}
				} //for
			}//if sizeof menu
		}
	}


	if (!function_exists("show_tree"))
	{
		function show_tree($id_menuitem, $icons=false)
		{
			global $dbweb;
			//read userrights		
			$userrights=array();
			$id_userrole=6; //guest
			if ( isset($_SESSION["id_user"]) ) {
				$results=q("SELECT * FROM cms_users WHERE id_user='".$_SESSION["id_user"]."';", $dbweb, __FILE__, __LINE__);
				if( mysqli_num_rows($results)>0 ) {
					$row=mysqli_fetch_array($results);
					if ($row["userrole_id"]>0) $id_userrole=$row["userrole_id"];
				}
			} else {
				$results=q("SELECT * FROM cms_users WHERE session_id='".session_id()."';", $dbweb, __FILE__, __LINE__);
				if( mysqli_num_rows($results)>0 ) {
					$row=mysqli_fetch_array($results);
					if ($row["userrole_id"]>0) $id_userrole=$row["userrole_id"];
				}
			}
			$results=q("SELECT * FROM cms_userroles_scripts WHERE userrole_id=".$id_userrole.";", $dbweb,__FILE__, __LINE__);
			while($row=mysqli_fetch_array($results))
			{
				$userrights[$row["script"]]=$row["script"];
			}
			//read menu
			$results=q("SELECT * FROM cms_menuitems WHERE menuitem_id=".$id_menuitem." ORDER BY ordering;", $dbweb,__FILE__, __LINE__);
			$i=0;
			while($row=mysqli_fetch_array($results))
			{
				$results2=q("SELECT * FROM cms_menuitems_languages WHERE language_id=".$_SESSION["id_language"]." AND menuitem_id=".$row["id_menuitem"].";", $dbweb,__FILE__, __LINE__);
				if( mysqli_num_rows($results2)>0 )
				{
					$cms_menuitems_languages=mysqli_fetch_array($results2);
				}
				else
				{
					$results2=q("SELECT * FROM cms_menuitems_languages WHERE language_id=1 AND menuitem_id=".$row["id_menuitem"].";", $dbweb,__FILE__, __LINE__);
					$cms_menuitems_languages=mysqli_fetch_array($results2);
				}
				$menu["id_menuitem"][$i]=$row["id_menuitem"];
				$menu["home"][$i]=$row["home"];
				$menu["icon"][$i]=$row["icon"];
				$menu["description"][$i]=$cms_menuitems_languages["description"];
				$menu["title"][$i]=$cms_menuitems_languages["title"];
				$menu["menuitem_id"][$i]=$row["menuitem_id"];
				$menu["link"][$i]=$row["link"];
				$menu["alias"][$i]=$cms_menuitems_languages["alias"];
				$i++;
			}
			//show menu
			menu2list($menu, $id_menuitem, $userrights, $icons);
		}
	}

	if (!function_exists("show_menu"))
	{
		function show_menu($idtag, $icons=false)
		{
			global $dbweb;
			//read userrights		
			$userrights=array();
			$id_userrole=6; //guest
			if ( isset($_SESSION["id_user"]) ) {
				$results=q("SELECT * FROM cms_users WHERE id_user='".$_SESSION["id_user"]."';", $dbweb,__FILE__, __LINE__);
				if( mysqli_num_rows($results)>0 ) {
					$row=mysqli_fetch_array($results);
					if ($row["userrole_id"]>0) $id_userrole=$row["userrole_id"];
				}
			} else {
				$results=q("SELECT * FROM cms_users WHERE session_id='".session_id()."';", $dbweb,__FILE__, __LINE__);
				if( mysqli_num_rows($results)>0 ) {
					$row=mysqli_fetch_array($results);
					if ($row["userrole_id"]>0) $id_userrole=$row["userrole_id"];
				}
			}
			$results=q("SELECT * FROM cms_userroles_scripts WHERE userrole_id=".$id_userrole.";", $dbweb, __FILE__, __LINE__);
			while($row=mysqli_fetch_array($results))
			{
				$userrights[$row["script"]]=$row["script"];
			}
			//read menu
			$results=q("SELECT * FROM cms_menus WHERE site_id IN(0, ".$_SESSION["id_site"].") AND idtag='".$idtag."';", $dbweb, __FILE__, __LINE__);
			if (mysqli_num_rows($results) == 0) return("");
			$row = mysqli_fetch_array($results);
			$results = q("SELECT * FROM cms_menuitems WHERE menu_id=".$row["id_menu"]." ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
			$i = 0;
			$menu = array();
			while($row=mysqli_fetch_array($results))
			{
				$results2=q("SELECT * FROM cms_menuitems_languages WHERE language_id=".$_SESSION["id_language"]." AND menuitem_id=".$row["id_menuitem"].";", $dbweb,__FILE__, __LINE__);
				if( mysqli_num_rows($results2)>0 )
				{
					$cms_menuitems_languages=mysqli_fetch_array($results2);
				}
				else
				{
					$results2=q("SELECT * FROM cms_menuitems_languages WHERE language_id=1 AND menuitem_id=".$row["id_menuitem"].";", $dbweb,__FILE__, __LINE__);
					$cms_menuitems_languages=mysqli_fetch_array($results2);
				}
				$menu["id_menuitem"][$i]=$row["id_menuitem"];
				$menu["home"][$i]=$row["home"];
				$menu["local"][$i]=$row["local"];
				$menu["icon"][$i]=$row["icon"];
				$menu["description"][$i]=$cms_menuitems_languages["description"];
				$menu["title"][$i]=$cms_menuitems_languages["title"];
				$menu["menuitem_id"][$i]=$row["menuitem_id"];
				$menu["link"][$i]=$row["link"];
				$menu["alias"][$i]=$cms_menuitems_languages["alias"];
				$i++;
			}
			//show menu
			echo '<ul id="'.$idtag.'">';
			menu2list($menu, 0, $userrights, $icons);
			echo '</ul>';
		}
	}
?>