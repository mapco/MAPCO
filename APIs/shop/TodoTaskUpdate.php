<?php
	
	include("../functions/cms_t.php");
	
	check_man_params(array("mode"	=> "text",
						   "id" 	=> "numeric"));
	
	if($_POST["mode"]=="edit")
	{
		$required=array("title" 		=> 	"text",
						"description" 	=> 	"text",
						"cat_id_new"	=>	"numeric");
		
		check_man_params($required);
	}
	
	if($_POST["mode"]=="order" or $_POST["mode"]=="order_all" or $_POST["mode"]=="order_private")
	{
		$required=array("ids" 	=> "numeric");
		
		check_man_params($required);
	}
	
	
	//erledigt-button
	if($_POST["mode"]=="done")
	{
		$xml='';
		$results=q("SELECT * FROM todo_tasks WHERE id=".$_POST["id"].";", $dbshop, __FILE__, __LINE__);
		if(mysqli_num_rows($results)>0)
		{
			$row=mysqli_fetch_array($results);
			if($row["done"]==0)
			{
				if($row["in_work_by"]==0)
					$xml.='<status>'.t("Sie müssen die Aufgabe zuerst auschecken.").'</status>'."\n";
				else if($row["in_work_by"]>0 and $row["in_work_by"]!=$_SESSION["id_user"])
					$xml.='<status>'.t("Die Aufgabe wird bereits von einem anderen Mitarbeiter bearbeitet.").'</status>'."\n";
				else if($row["in_work_by"]==$_SESSION["id_user"])
				{
					$results2=q("UPDATE todo_tasks SET done=1, lastmod=".time().", lastmod_user=".$_SESSION["id_user"]." WHERE id=".$_POST["id"].";", $dbshop, __FILE__, __LINE__);
					$xml.='<status>done</status>'."\n";
				}
			}
			else
				$xml.='<status>'.t("Die Aufgabe ist bereits als erledigt markiert.").'</status>'."\n";
			$xml.='<task_title><![CDATA['.$row["title"].']]></task_title>'."\n";
				//$results2=q("UPDATE todo_tasks SET done=0, lastmod=".time().", lastmod_user=".$_SESSION["id_user"]." WHERE id=".$_POST["id"].";", $dbshop, __FILE__, __LINE__);
		}
		echo $xml;
	}
	
	//auschecken/einchecken
	if($_POST["mode"]=="check")
	{
		$results=q("SELECT * FROM todo_tasks WHERE id=".$_POST["id"].";", $dbshop, __FILE__, __LINE__);
		if(mysqli_num_rows($results)>0)
		{
			$row=mysqli_fetch_array($results);
			if($row["in_work_by"]==0)
				$results2=q("UPDATE todo_tasks SET in_work_by=".$_SESSION["id_user"].", lastmod=".time().", lastmod_user=".$_SESSION["id_user"]." WHERE id=".$_POST["id"].";", $dbshop, __FILE__, __LINE__);
			else
			{
				if($row["in_work_by"]==$_SESSION["id_user"] && $row["done"]==0)
				{
					$results2=q("UPDATE todo_tasks SET in_work_by=0, private_priority=0, lastmod=".time().", lastmod_user=".$_SESSION["id_user"]." WHERE id=".$_POST["id"].";", $dbshop, __FILE__, __LINE__);
					$results3=q("SELECT * FROM todo_tasks WHERE in_work_by=".$_SESSION["id_user"]." AND private_priority>".$row["private_priority"].";", $dbshop, __FILE__, __LINE__);
					if(mysqli_num_rows($results3)>0)
					{
						while($row3=mysqli_fetch_array($results3))
						{
							$results4=q("UPDATE todo_tasks SET private_priority=".($row3["private_priority"]*1-1)." WHERE id=".$row3["id"].";", $dbshop, __FILE__, __LINE__);
						}
					}
				}
			}
		}
	}
	
	//bearbeiten
	if($_POST["mode"]=="edit")
	{
		if($_POST["cat_id_new"]==0) //task bleibt in der alten Kategorie
		{
			$results=q("UPDATE todo_tasks SET title='".$_POST["title"]."', description='".$_POST["description"]."', lastmod=".time().", lastmod_user=".$_SESSION["id_user"]." WHERE id=".$_POST["id"].";", $dbshop, __FILE__, __LINE__);
		}
		else
		{
			//alte Kategorie und ordering bestimmen
			$results=q("SELECT * FROM todo_tasks WHERE id=".$_POST["id"].";", $dbshop, __FILE__, __LINE__);
			$row=mysqli_fetch_array($results);
			$cat_id_old=$row["cat_id"];
			$ordering_old=$row["ordering"];
			//neue ordering bestimmen
			$results=q("SELECT * FROM todo_tasks WHERE cat_id=".$_POST["cat_id_new"]." ORDER BY ordering DESC;", $dbshop, __FILE__, __LINE__);
			if(mysqli_num_rows($results)==0)
				$ordering_new=1;
			else
			{
				$row=mysqli_fetch_array($results);
				$ordering_new=$row["ordering"]*1+1;
			}
			if($cat_id_old!=$_POST["cat_id_new"])
			{
				//ordering in alter Kategorie neu setzen
				$results=q("SELECT * FROM todo_tasks WHERE ordering>".$ordering_old." AND cat_id=".$cat_id_old.";", $dbshop, __FILE__, __LINE__);
				if(mysqli_num_rows($results)>0)
				{
					while($row=mysqli_fetch_array($results))
					{
						$results2=q("UPDATE todo_tasks SET ordering=".($row["ordering"]*1-1)." WHERE id=".$row["id"].";", $dbshop, __FILE__, __LINE__);
					}
				}
				//task updaten
				$results3=q("UPDATE todo_tasks SET title='".$_POST["title"]."', description='".$_POST["description"]."',ordering=".$ordering_new." , cat_id=".$_POST["cat_id_new"].", lastmod=".time().", lastmod_user=".$_SESSION["id_user"]." WHERE id=".$_POST["id"].";", $dbshop, __FILE__, __LINE__);
			}
			else
			{
				$results=q("UPDATE todo_tasks SET title='".$_POST["title"]."', description='".$_POST["description"]."', lastmod=".time().", lastmod_user=".$_SESSION["id_user"]." WHERE id=".$_POST["id"].";", $dbshop, __FILE__, __LINE__);
			}
		}
	}
	
	//sortieren innerhalb Kategorie
	if($_POST["mode"]=="order")
	{
		for($i=0; $i<sizeof($_POST["ids"]); $i++)
		{
			$results=q("UPDATE todo_tasks SET ordering=".($i+1)." WHERE id=".$_POST["ids"][$i].";", $dbshop, __FILE__, __LINE__);
		}
	}
	
	//sortieren Gesamtliste
	if($_POST["mode"]=="order_all")
	{
		for($i=0; $i<sizeof($_POST["ids"]); $i++)
		{
			$results=q("UPDATE todo_tasks SET priority=".($i+1)." WHERE id=".$_POST["ids"][$i].";", $dbshop, __FILE__, __LINE__);
		}
	}
	
	//sortieren private Liste
	if($_POST["mode"]=="order_private")
	{
		for($i=0; $i<sizeof($_POST["ids"]); $i++)
		{
			$results=q("UPDATE todo_tasks SET private_priority=".($i+1)." WHERE id=".$_POST["ids"][$i].";", $dbshop, __FILE__, __LINE__);
		}
	}

?>
