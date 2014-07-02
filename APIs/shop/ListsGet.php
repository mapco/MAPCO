<?php

	check_man_params(array("mode" => "text"));
	
	if ($_POST["mode"]=="items")
	{
		$required=array("list_item_ids"	=>"numeric"); 
		
		check_man_params($required);
	}
	
	if($_POST["mode"]=="lists")
	{
		$results=q("SELECT * FROM shop_lists;", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$keys=array_keys($row);
		
		$xmldata='';
		$idnr=0;
		$listtypenr=0;
		
		$res=q("SELECT * FROM shop_lists;", $dbshop, __FILE__, __LINE__);
		while ($row=mysqli_fetch_array($res))
		{
			$xmldata.="	<list>\n";
			for($i=0; $i<sizeof($keys); $i++)
			{
				if( !is_numeric($keys[$i]) )
					$xmldata.='	 <'.$keys[$i].'><![CDATA['.$row[$keys[$i]].']]></'.$keys[$i].'>'."\n";
				if(!is_numeric($keys[$i]) && $keys[$i]=="id_list")
					$idnr = $row[$keys[$i]];
				if(!is_numeric($keys[$i]) && $keys[$i]=="listtype_id")
					$listtypenr = $row[$keys[$i]];
			}
			$result=q("SELECT * FROM shop_listtypes WHERE id_listtype=".$listtypenr.";", $dbshop, __FILE__, __LINE__);
			if(mysqli_num_rows($result)>0)
			{
				$row3=mysqli_fetch_array($result);
				$xmldata.='<listtype_title><![CDATA['.$row3["title"].']]></listtype_title>'."\n";
			}
			$result=q("SELECT * FROM shop_lists_items WHERE list_id=".$idnr.";", $dbshop, __FILE__, __LINE__);
			while($row2=mysqli_fetch_array($result))
			{
				//$result3=q("SELECT * FROM shop_items WHERE id_item=".$row2["item_id"].";", $dbshop, __FILE__, __LINE__);
				//$result4=q("SELECT * FROM shop_items_de WHERE id_item=".$row2["item_id"].";", $dbshop, __FILE__, __LINE__);
				$xmldata.="	<item>\n";
				$xmldata.='  <item_id><![CDATA['.$row2["item_id"].']]></item_id>'."\n";
				/*if(mysqli_num_rows($result3)>0)
				{
					$row3=mysqli_fetch_array($result3);
					$xmldata.='  <MPN><![CDATA['.$row3["MPN"].']]></MPN>'."\n";
				}
				else
					$xmldata.='  <MPN><![CDATA[0]]></MPN>'."\n";
				if(mysqli_num_rows($result4)>0)
				{
					$row4=mysqli_fetch_array($result4);
					$xmldata.='  <item_title><![CDATA['.$row4["title"].']]></item_title>'."\n";
				}
				else
					$xmldata.='  <item_title><![CDATA[0]]></item_title>'."\n";*/
				$xmldata.="	</item>\n";
			}
			$xmldata.="	</list>\n";
		}
	
		//echo "<ListsGetResponse>\n";
		//echo "<Ack>Success</Ack>\n";
		echo $xmldata;
		//echo "</ListsGetResponse>";
	}
	
	if($_POST["mode"]=="items")
	{
		$xmldata = '';
		//$xmldata.="<items>\n";
		for($i=0; $i<count($_POST["list_item_ids"]); $i++)
		{
			$xmldata.="<item>\n";
			$xmldata.='  <item_id><![CDATA['.$_POST["list_item_ids"][$i].']]></item_id>'."\n";
			$result=q("SELECT * FROM shop_items WHERE id_item=".$_POST["list_item_ids"][$i].";", $dbshop, __FILE__, __LINE__);
			if(mysqli_num_rows($result)>0)
			{
				$row=mysqli_fetch_array($result);
				$xmldata.='  <MPN><![CDATA['.$row["MPN"].']]></MPN>'."\n";
			}
			else
				$xmldata.='  <MPN><![CDATA[0]]></MPN>'."\n";
			$result=q("SELECT * FROM shop_items_de WHERE id_item=".$_POST["list_item_ids"][$i].";", $dbshop, __FILE__, __LINE__);
			if(mysqli_num_rows($result)>0)
			{
				$row=mysqli_fetch_array($result);
				$xmldata.='  <item_title><![CDATA['.$row["title"].']]></item_title>'."\n";
			}
			else
				$xmldata.='  <item_title><![CDATA[]]></item_title>'."\n";
			$xmldata.="</item>\n";
		}
		//$xmldata.="</items>\n";
		echo $xmldata;
	}

?>
