<?php
	
	check_man_params(array("mode" => "text"));
	
	if($_POST["mode"] == "category") {
		check_man_params(array("cat_id" => "numeric"));
	}
	
	if($_POST["mode"] == "private") {
		check_man_params(array("private_user_id" => "numeric"));
	}
	
	if($_POST["mode"] == "category") {
		$results = q("SELECT * FROM todo_tasks WHERE cat_id=" . $_POST["cat_id"] . " ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
	}
	
	if($_POST["mode"] == "all") {
		$results =q ("SELECT * FROM todo_tasks ORDER BY priority;", $dbshop, __FILE__, __LINE__);
	}
	
	if ($_POST["mode"] == "latest") {
		$results = q("SELECT * FROM todo_tasks ORDER BY firstmod DESC LIMIT 25;", $dbshop, __FILE__, __LINE__);
	}
	
	$xml = '';
	
	if ($_POST["mode"] == "private") {
		//private_priority vergeben für Einträge, die bis jetzt noch nicht auf der privaten Liste angezeigt wurden
		$results = q("SELECT * FROM todo_tasks WHERE in_work_by=".$_POST["private_user_id"]." ORDER BY private_priority DESC;", $dbshop, __FILE__, __LINE__);
		if(mysqli_num_rows($results) > 0)
		{
			$cnt = 0;
			$private_priority = 0;
			while ($row=mysqli_fetch_array($results))
			{
				if ($cnt == 0)
					$private_priority = $row["private_priority"]+1;
				//if($private_priority==0)
					//$private_priority=1;
				if ($row["private_priority"] == 0) {
					$results2 = q("UPDATE todo_tasks SET private_priority = " . $private_priority . " WHERE id = " . $row["id"] . ";", $dbshop, __FILE__, __LINE__);
					$private_priority++;
				}				
				$cnt++;
			}
		}
		
		//eigentliche Auswahl
		$results = q("SELECT * FROM todo_tasks WHERE in_work_by = " . $_POST["private_user_id"] . " ORDER BY private_priority;", $dbshop, __FILE__, __LINE__);
	}
	
	$task_cnt = 0;
	while($row = mysqli_fetch_array($results))
	{
		// get parent-path
		$parent_path = "";
		$p_ids = array();
		$p_parent_ids = array();
		$p_title = array();
		$p_t_childs = array();
		
		$p = $row["cat_id"];
		
		while($p > 0)
		{
			//$num_p_t_childs=0;
			$results4=q("SELECT * FROM todo_categories WHERE id=".$p.";", $dbshop, __FILE__, __LINE__);
			//$results2=q("SELECT * FROM todo_tasks WHERE cat_id=".$p.";", $dbshop, __FILE__, __LINE__);
			//if(mysqli_num_rows($results2)>0)
				//$num_p_t_childs=mysqli_num_rows($results2);
			$row4 = mysqli_fetch_array($results4);
			$p_ids[] = $row4["id"];
			$p_parent_ids[] = $row4["parent_id"];
			$p_title[] = $row4["title"];
			//$p_t_childs[]=$num_p_t_childs;
			$p = $row4["parent_id"];
		}
		
		for($i = (count($p_ids) -1); $i >= 0; $i--)
		{
			$parent_path.= $p_title[$i] . "/";
			/*$xml.='	<p_cat>'."\n";
			$xml.='		<p_cat_id>'.$p_ids[$i].'</p_cat_id>'."\n";
			$xml.='		<p_cat_title><![CDATA['.$p_title[$i].']]></p_cat_title>'."\n";
			$xml.='		<p_cat_parent_id>'.$p_parent_ids[$i].'</p_cat_parent_id>'."\n";
			$xml.='		<p_t_childs>'.$p_t_childs[$i].'</p_t_childs>'."\n";
			$xml.='		<p_cnt>'.$i.'</p_cnt>'."\n";
			$xml.='	</p_cat>'."\n";*/
		}
		
		$in_work_by_name = "";
		if ($row["in_work_by"] > 0) {
			$in_work_by_name = "";
			$results2 = q("SELECT * FROM cms_users WHERE id_user = " . $row["in_work_by"] . ";", $dbweb, __FILE__, __LINE__);
			if (mysqli_num_rows($results2) > 0) {
				$row2 = mysqli_fetch_array($results2);
				$in_work_by_name = $row2["username"];
			}
		}
		
		$firstmod_user = "";
		$results3 = q("SELECT * FROM cms_users WHERE id_user=".$row["firstmod_user"].";", $dbweb, __FILE__, __LINE__);
		if (mysqli_num_rows($results3) > 0) {
			$row3=mysqli_fetch_array($results3);
			$firstmod_user = $row3["username"];
		}
		
		$xml.='	<task>'."\n";
		$xml.='		<task_id>'.$row["id"].'</task_id>'."\n";
		$xml.='		<task_cat_id>'.$row["cat_id"].'</task_cat_id>'."\n";
		$xml.='		<task_title><![CDATA['.$row["title"].']]></task_title>'."\n";
		$xml.='		<task_description><![CDATA['.$row["description"].']]></task_description>'."\n";
		$xml.='		<task_ordering>'.$row["ordering"].'</task_ordering>'."\n";
		$xml.='		<task_priority>'.$row["priority"].'</task_priority>'."\n";
		$xml.='		<task_private_priority>'.$row["private_priority"].'</task_private_priority>'."\n";
		$xml.='		<task_in_work_by>'.$row["in_work_by"].'</task_in_work_by>'."\n";
		$xml.='		<task_in_work_by_name>'.$in_work_by_name.'</task_in_work_by_name>'."\n";
		$xml.='		<task_done>'.$row["done"].'</task_done>'."\n";
		$xml.='		<task_firstmod_user>'.$firstmod_user.'</task_firstmod_user>'."\n";
		$xml.='		<task_parent_path>'.$parent_path.'</task_parent_path>'."\n";
		$xml.='	</task>'."\n";
		$task_cnt += 1;
	}
	
	$xml.='		<task_cnt>'.$task_cnt.'</task_cnt>'."\n";
	echo $xml;
?>
