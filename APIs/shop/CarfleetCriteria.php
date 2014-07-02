<?php

	function save_order_event($eventtype_id, $order_id, $data)
	{
		global $dbshop;
		//CREATE XML FROM DATA
		$xml='<data>';
		foreach ($data as $key => $val)
		{
			$xml.='<'.$key.'>';
			if (!is_numeric($val)) $xml.='<![CDATA['.$val.']]>'; else $xml.=$val;
			$xml.='</'.$key.'>';
			
		}
		$xml.='</data>';
		
		//SAVE EVENT
		q("INSERT INTO shop_orders_events (
			order_id, 
			eventtype_id, 
			data, 
			firstmod, 
			firstmod_user
		) VALUES (
			".$order_id.",
			".$eventtype_id.",
			'".mysqli_real_escape_string($dbshop, $xml)."',
			".time().",
			".$_SESSION["id_user"]."
		);", $dbshop, __FILE__, __LINE__);
		
		return mysqli_insert_id($dbshop);
		
	}


	if ( !isset($_POST["mode"]) )
	{
		echo '<CarfleetCriteriaResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Modus nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Modus (new/update/read) angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CarfleetCriteriaResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["id_carfleet"]) )
	{
		echo '<CarfleetCriteriaResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>id_carfleet nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine id_carfleet (= id aus Tabelle shop_carfleet) angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CarfleetCriteriaResponse>'."\n";
		exit;
	}
	
	if($_POST["mode"]=="read_car")
	{
		if ( !isset($_POST["order_id"]) )
		{
			echo '<CarfleetCriteriaResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>order_id nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Es muss eine order_id angegeben werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</CarfleetCriteriaResponse>'."\n";
			exit;
		}
		
		if ( !isset($_POST["order_item_id"]) )
		{
			echo '<CarfleetCriteriaResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>order_item_id nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Es muss eine order_item_id angegeben werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</CarfleetCriteriaResponse>'."\n";
			exit;
		}
		
		if ( !isset($_POST["KritNr"]) )
		{
			echo '<CarfleetCriteriaResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>KritNr nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Es muss eine KritNr angegeben werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</CarfleetCriteriaResponse>'."\n";
			exit;
		}
		
		if ( !isset($_POST["KritWert"]) )
		{
			echo '<CarfleetCriteriaResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>KritWert nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Es muss ein KritWert angegeben werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</CarfleetCriteriaResponse>'."\n";
			exit;
		}
		
		$result=q("SELECT * FROM shop_carfleet_criteria WHERE KritNR='".$_POST["KritNr"]."' AND id_carfleet=".$_POST["id_carfleet"]." AND order_id=".$_POST["order_id"]." AND order_item_id=".$_POST["order_item_id"]." AND KritWert='".$_POST["KritWert"]."';", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($result);
		if(mysqli_num_rows($result)>=1)
		{
			$xmldata="";
			$xmldata.="<KritNumber>1</KritNumber>\n";
			$xmldata.="<Krit>\n";
			$xmldata.="<id>".$row["id"]."</id>\n";
			$xmldata.="<UserKritBez>".$row["UserKritBez"]."</UserKritBez>\n";
			$xmldata.="</Krit>\n";
			echo "<CarfleetCriteriaResponse>\n";
			echo "<Ack>Success</Ack>\n";
			echo $xmldata;
			echo "</CarfleetCriteriaResponse>";
			exit;
		}
		if(mysqli_num_rows($result)==0)
		{
			$xmldata="";
			$xmldata.="<KritNumber>0</KritNumber>\n";
			echo "<CarfleetCriteriaResponse>\n";
			echo "<Ack>Success</Ack>\n";
			echo $xmldata;
			echo "</CarfleetCriteriaResponse>";
			exit;
		}
		else
		{
			echo '<CarfleetCriteriaResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Keine Kriterien gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Es konnten keine Kriterien zu dem angegebenen Fahrzeug gefunden werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</CarfleetCriteriaResponse>'."\n";
			exit;
		}
	}
	
	/*if($_POST["mode"]=="read_car_item")
	{
	}*/
	
	/*if($_POST["mode"]=="update")
	{
		if ( !isset($_POST["KritField"]) )
		{
			echo '<CarfleetCriteriaResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>KritField nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Es muss ein KritField angegeben werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</CarfleetCriteriaResponse>'."\n";
			exit;
		}
		$check=0;
		for($i=0;$i<count($_POST["id_carfleet"]);$i++)
		{
			$result=q("SELECT * FROM shop_carfleet_criteria WHERE KritField='".$_POST["KritField"][$i]."' AND id_carfleet=".$_POST["id_carfleet"][$i].";", $dbshop, __FILE__, __LINE__);
			if(mysqli_num_rows($result)==1)
			{
				if($_POST["UserKritBez"][$i]=="")
				{
					$res=q("DELETE FROM shop_carfleet_criteria WHERE KritField='".$_POST["KritField"][$i]."' AND id_carfleet=".$_POST["id_carfleet"][$i].";", $dbshop, __FILE__, __LINE__);
				}
				else
				{
					$res=q("UPDATE shop_carfleet_criteria SET UserKritBez='".$_POST["UserKritBez"][$i]."', lastmod=".time().", lastmod_user=".$_SESSION["id_user"]." WHERE KritField='".$_POST["KritField"][$i]."' AND id_carfleet=".$_POST["id_carfleet"][$i].";", $dbshop, __FILE__, __LINE__);
				}
			}
			else if(mysqli_num_rows($result)==0)
			{
				if($_POST["UserKritBez"][$i]=="")
				{
					$res=1;
				}
				else
				{
					//insert
					echo'inserting......';
					$res=q("INSERT INTO shop_carfleet_criteria (id_carfleet,
																item_id,
																KritNr,
																KritWert,
																KritBez,
																KritWertBez,
																KritType,
																KritType2,
																KritType3,
																KritField,
																UserKritBez,
																firstmod,
																firstmod_user,
																lastmod,
																lastmod_user) VALUES (".$_POST["id_carfleet"][$i].",
																					  0,
																					  '',
																					  '',
																					  '',
																					  '',
																					  'text',
																					  'fix',
																					  'car',
																					  '".$_POST["KritField"][$i]."',
																					  '".$_POST["UserKritBez"][$i]."',
																					  ".time().",
																					  ".$_SESSION["id_user"].",
																					  ".time().",
																					  ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
				}
			}
			if($res==1)
			{
				$check = $check + 1;
			}
			if($check == count($_POST["id_carfleet"]))
			{
				echo '<CarfleetCriteriaResponse>'."\n";
				echo '<Ack>Success</Ack>'."\n";
				//echo '<manipulated_lines>'.$check.'</manipulated_lines>'."\n";
				echo '</CarfleetCriteriaResponse>';
				exit;
			}
			if($res!=1)
			{
				echo "<CarfleetCriteriaResponse>\n";
				echo '<Ack>Failure</Ack>'."\n";
				echo '	<Error>'."\n";
				echo '		<Code>'.__LINE__.'</Code>'."\n";
				echo '		<shortMsg>Insert/Update/Delete Fehler.</shortMsg>'."\n";
				echo '		<longMsg>Der Datensatz konnte nicht eingefügt/geändert/gelöscht werden.</longMsg>'."\n";
				echo '	</Error>'."\n";
				echo "</CarfleetCriteriaResponse>";
				exit;
			}
		}
		exit;		
	}*/
	
	/*if($_POST["mode"]=="read_car_change")
	{
		$result=q("SELECT * FROM shop_carfleet_criteria WHERE KritType2='".$_POST["KritType2"]."' AND KritType3='".$_POST["KritType3"]."' AND id_carfleet=".$_POST["id_carfleet"].";", $dbshop, __FILE__, __LINE__);
		if(mysqli_num_rows($result)>=1)
		{
			$xmldata="";
			while ($row=mysqli_fetch_array($result))
			{
				$xmldata.="<Krit>\n";
				$xmldata.="<KritField>".$row["KritField"]."</KritField>\n";
				$xmldata.="<UserKritBez>".$row["UserKritBez"]."</UserKritBez>\n";
				$xmldata.="</Krit>\n";
			}
			echo "<CarfleetCriteriaResponse>\n";
			echo "<Ack>Success</Ack>\n";
			echo $xmldata;
			echo "</CarfleetCriteriaResponse>";
			exit;
		}
		else
		{
			echo '<CarfleetCriteriaResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Keine Kriterien gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Es konnten keine Kriterien zu dem angegebenen Fahrzeug gefunden werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</CarfleetCriteriaResponse>'."\n";
			exit;
		}
	}*/
	
	if($_POST["mode"]=="new")
	{
		$check = 0;
		for($i=0;$i<count($_POST["id_carfleet"]);$i++)
		{
			if($_POST["id"][$i]>0)
			{
				if($_POST["UserKritBez"][$i]=="")
				{
					$res=q("DELETE FROM shop_carfleet_criteria WHERE id=".$_POST["id"][$i].";", $dbshop, __FILE__, __LINE__);
				}
				else
				{
					$res=q("UPDATE shop_carfleet_criteria SET UserKritBez='".$_POST["UserKritBez"][$i]."', lastmod=".time().", lastmod_user=".$_SESSION["id_user"]." WHERE id='".$_POST["id"][$i]."';", $dbshop, __FILE__, __LINE__);
					
					$data=array();
					$data["UserKritBez"]=$_POST["UserKritBez"][$i];
					
					//GET ORDERDID
					$res_orderid=q("SELECT * FROM shop_carfleet_criteria WHERE id='".$_POST["id"][$i]."';", $dbshop, __FILE__, __LINE__);
					if (mysqli_num_rows($res_orderid)>0)
					{
						$row_orderid=mysqli_fetch_array($res_orderid);
						$orderid=$row_orderid["order_id"];
					}
					else
					{
						$orderid=0;
					}
					
					$event_id=save_order_event(17, $orderid, $data);
					
				}
			}
			else
			{
				if($_POST["UserKritBez"][$i]!=="")
				{
					$res=q("INSERT INTO shop_carfleet_criteria (id_carfleet,
																order_id,
																order_item_id,
																item_id,
																KritNr,
																KritWert,
																KritBez,
																KritWertBez,
																UserKritBez,
																firstmod,
																firstmod_user,
																lastmod,
																lastmod_user) VALUES (".$_POST["id_carfleet"][$i].",
																					  ".$_POST["order_id"][$i].",
																					  ".$_POST["order_item_id"][$i].",
																					  ".$_POST["item_id"][$i].",
																					  '".$_POST["KritNr"][$i]."',
																					  '".$_POST["KritWert"][$i]."',
																					  '".$_POST["KritBez"][$i]."',
																					  '".$_POST["KritWertBez"][$i]."',
																					  '".$_POST["UserKritBez"][$i]."',
																					  ".time().",
																					  ".$_SESSION["id_user"].",
																					  ".time().",
																					  ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
				
					$data=array();
					$data["id_carfleet"]=$_POST["id_carfleet"][$i];
					$data["order_id"]=$_POST["order_id"][$i];
					$data["order_item_id"]=$_POST["order_item_id"][$i];
					$data["item_id"]=$_POST["item_id"][$i];
					$data["KritNr"]=$_POST["KritNr"][$i];
					$data["KritWert"]=$_POST["KritWert"][$i];
					$data["KritBez"]=$_POST["KritBez"][$i];
					$data["KritWertBez"]=$_POST["KritWertBez"][$i];
					$data["UserKritBez"]=$_POST["UserKritBez"][$i];

																	  
					$event_id=save_order_event(18, $_POST["order_id"][$i], $data);

				}
				else
				{
					$res=1;
				}
			}
			
			if($res==1)
			{
				$check = $check + 1;
			}
			if($check == count($_POST["id_carfleet"]))
			{
				echo '<CarfleetCriteriaResponse>'."\n";
				echo '<Ack>Success</Ack>'."\n";
				echo '<manipulated_lines>'.$check.'</manipulated_lines>'."\n";
				echo '</CarfleetCriteriaResponse>';
				exit;
			}
			if($res!=1)
			{
				echo "<CarfleetCriteriaResponse>\n";
				echo '<Ack>Failure</Ack>'."\n";
				echo '	<Error>'."\n";
				echo '		<Code>'.__LINE__.'</Code>'."\n";
				echo '		<shortMsg>Insert Fehler.</shortMsg>'."\n";
				echo '		<longMsg>Der Datensatz konnte nicht eingefügt werden.</longMsg>'."\n";
				echo '	</Error>'."\n";
				echo "</CarfleetCriteriaResponse>";
				exit;
			}
		}
	}
	
?>