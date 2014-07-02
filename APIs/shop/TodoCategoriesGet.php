<?php

	check_man_params(array("parent_id" => "numeric"));
	
	$xml='';
	// get parents
	$p_ids=array();
	$p_parent_ids=array();
	$p_title=array();
	$p_t_childs=array();
	
	$p=$_POST["parent_id"];
	
	while($p>0)
	{
		$num_p_t_childs=0;
		$results=q("SELECT * FROM todo_categories WHERE id=".$p.";", $dbshop, __FILE__, __LINE__);
		$results2=q("SELECT * FROM todo_tasks WHERE cat_id=".$p.";", $dbshop, __FILE__, __LINE__);
		if(mysqli_num_rows($results2)>0)
			$num_p_t_childs=mysqli_num_rows($results2);
		$row=mysqli_fetch_array($results);
		$p_ids[]=$row["id"];
		$p_parent_ids[]=$row["parent_id"];
		$p_title[]=$row["title"];
		$p_t_childs[]=$num_p_t_childs;
		$p=$row["parent_id"];
	}
	
	for($i=(count($p_ids)-1); $i>=0; $i--)
	{
		$xml.='	<p_cat>'."\n";
		$xml.='		<p_cat_id>'.$p_ids[$i].'</p_cat_id>'."\n";
		$xml.='		<p_cat_title><![CDATA['.$p_title[$i].']]></p_cat_title>'."\n";
		$xml.='		<p_cat_parent_id>'.$p_parent_ids[$i].'</p_cat_parent_id>'."\n";
		$xml.='		<p_t_childs>'.$p_t_childs[$i].'</p_t_childs>'."\n";
		$xml.='		<p_cat_undone_tasks>'.undone_tasks_get($p_ids[$i]).'</p_cat_undone_tasks>'."\n";
		$xml.='		<p_cnt>'.$i.'</p_cnt>'."\n";
		$xml.='	</p_cat>'."\n";
	}
	
	//number of undone tasks home
	$results=q("SELECT * FROM todo_tasks WHERE done=0;", $dbshop, __FILE__, __LINE__);
	$undone_tasks_home=mysqli_num_rows($results);
	
	//number of undone tasks private lists
	$results=q("SELECT * FROM todo_tasks WHERE in_work_by=".$_SESSION["id_user"]." AND done=0;", $dbshop, __FILE__, __LINE__);
	$undone_tasks_private=mysqli_num_rows($results);
	
	//private lists data
	$results=q("SELECT * FROM cms_users WHERE id_user=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$userrole_id_session_user=$row["userrole_id"];
	
	if($userrole_id_session_user==1)	
		$results=q("SELECT * FROM todo_tasks WHERE in_work_by!=0 GROUP BY in_work_by;", $dbshop, __FILE__, __LINE__);
	else
		$results=q("SELECT * FROM todo_tasks WHERE in_work_by=".$_SESSION["id_user"]." GROUP BY in_work_by;", $dbshop, __FILE__, __LINE__);
	if(mysqli_num_rows($results)>0)
	{
		$xml.='	<private_lists>'."\n";
		while($row=mysqli_fetch_array($results))
		{
			$xml.='		<list>'."\n";
			$xml.='			<list_user_id>'.$row["in_work_by"].'</list_user_id>'."\n";
			$results2=q("SELECT * FROM cms_users WHERE id_user=".$row["in_work_by"].";", $dbweb, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			$xml.='			<list_username><![CDATA['.$row2["username"].']]></list_username>'."\n";
			$results3=q("SELECT * FROM todo_tasks WHERE in_work_by=".$row["in_work_by"]." AND done=0;", $dbshop, __FILE__, __LINE__);
			//$row3=mysqli_fetch_array($results3);
			$xml.='			<list_undone_tasks>'.mysqli_num_rows($results3).'</list_undone_tasks>'."\n";
			$xml.='		</list>'."\n";
		}
		$xml.='	</private_lists>'."\n";
	}
	
	// get categories
	$results=q("SELECT * FROM todo_categories WHERE parent_id=".$_POST["parent_id"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
	
	$cat_cnt=0;
	while($row=mysqli_fetch_array($results))
	{		
		//category data
		$results2=q("SELECT * FROM todo_categories WHERE parent_id=".$row["id"].";", $dbshop, __FILE__, __LINE__);
		$c_childs=mysqli_num_rows($results2);
		$results3=q("SELECT * FROM todo_tasks WHERE cat_id=".$row["id"].";", $dbshop, __FILE__, __LINE__);
		//$t_childs=$childs+mysqli_num_rows($results3);
		$t_childs=mysqli_num_rows($results3);

		$xml.='	<cat>'."\n";
		$xml.='		<cat_id>'.$row["id"].'</cat_id>'."\n";
		$xml.='		<cat_title><![CDATA['.$row["title"].']]></cat_title>'."\n";
		$xml.='		<cat_ordering>'.$row["ordering"].'</cat_ordering>'."\n";
		$xml.='		<cat_childs>'.($c_childs+$t_childs).'</cat_childs>'."\n";
		$xml.='		<cat_c_childs>'.$c_childs.'</cat_c_childs>'."\n";
		$xml.='		<cat_t_childs>'.$t_childs.'</cat_t_childs>'."\n";
		$xml.='		<cat_undone_tasks>'.undone_tasks_get($row["id"]).'</cat_undone_tasks>';
		$xml.='	</cat>'."\n";
		$cat_cnt+=1;
	}
	
	$xml.='<cat_cnt>'.$cat_cnt.'</cat_cnt>'."\n";
	$xml.='<undone_tasks_home>'.$undone_tasks_home.'</undone_tasks_home>'."\n";
	$xml.='<undone_tasks_private>'.$undone_tasks_private.'</undone_tasks_private>'."\n";
	
	echo $xml;
	
	function undone_tasks_get($cat_id)
	{
		global $dbshop;
		
		$undone_tasks_cnt=0;
		$results=q("SELECT * FROM todo_categories WHERE parent_id=".$cat_id.";", $dbshop, __FILE__, __LINE__);
		if(mysqli_num_rows($results)==0)
		{
			$results=q("SELECT * FROM todo_tasks WHERE cat_id=".$cat_id." AND done=0;", $dbshop, __FILE__, __LINE__);
			$undone_tasks_cnt+=mysqli_num_rows($results);
		}
		else
		{
			while($row=mysqli_fetch_array($results))
			{
				$undone_tasks_cnt+=undone_tasks_get($row["id"]);
			}
		}
		return $undone_tasks_cnt;
	}

?>
