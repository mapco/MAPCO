<?php
	///äöüÄÖÜ UTF-8
	if (!function_exists("itemstatus"))
	{
		function itemstatus($id_item, $mail=0, $amount=0)
		{
			global $dbshop;

			$results=q("SELECT a.MPN, ISTBESTAND FROM shop_items AS a, lager AS b WHERE a.id_item=".$id_item." AND a.MPN=b.ArtNr LIMIT 1;", $dbshop, __FILE__, __LINE__);
			$row=mysqli_fetch_array($results);
			$artnr=$row["MPN"];

			$status  = '<div id="itemstatus">';
			
		//Hauptansicht
		$results=q("SELECT * FROM lager WHERE ArtNr='".$artnr."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
        
		if ($mail==1)
		{
			If($amount>0)
			{
				if ($row["ISTBESTAND"]>=$amount) $best= '<img src="http://www.mapco.de/images/lieferbar.jpg" alt="'.t("sofort lieferbar").'" title="'.t("sofort lieferbar").'" />';
				else $best= '<img src="http://www.mapco.de/images/nicht_lieferbar.jpg" alt="'.t("z.Z nicht lieferbar").'" title="'.t("z.Z nicht lieferbar").'" />';
			}
			else
			{
				if ($row["ISTBESTAND"]>10) $best= '<img src="http://www.mapco.de/images/lieferbar.jpg" alt="'.t("sofort lieferbar").'" title="'.t("sofort lieferbar").'" />';
				elseif ($row["ISTBESTAND"]>0) $best= '<img src="http://www.mapco.de/images/auf_anfrage.jpg" alt="'.t("Nur noch wenige lieferbar").'" title="'.t("Nur noch wenige lieferbar").'" />';
				else $best= '<img src="http://www.mapco.de/images/nicht_lieferbar.jpg" alt="'.t("z.Z nicht lieferbar").'" title="'.t("z.Z nicht lieferbar").'" />';				
			}
/*
			if ($row["Bestand"]==1) $best= '<img src="http://www.mapco.de/images/lieferbar.jpg" alt="'.t("sofort lieferbar").'" title="'.t("sofort lieferbar").'" />';
			elseif ($row["Bestand"]==2) $best= '<img src="http://www.mapco.de/images/auf_anfrage.jpg" alt="'.t("Liefertermin auf Anfrage").'" title="'.t("Liefertermin auf Anfrage").'" />';
			else $best= '<img src="http://www.mapco.de/images/nicht_lieferbar.jpg" alt="'.t("z.Z nicht lieferbar").'" title="'.t("z.Z nicht lieferbar").'" />';
*/
			$status .= $best;
			$status .= '</div>';
		}
		elseif ($mail==2)
		{
			if ($row["ISTBESTAND"]>10) $best= '';
			elseif ($row["ISTBESTAND"]>0) $best= '<img style="margin:0px 0px -8px 10px" src="http://www.mapco.de/images/icons/24x24/warning.png" alt="'.t("Nur noch wenige lieferbar").'" title="'.t("Nur noch wenige lieferbar").'" />';
			else $best= '<img src="http://www.mapco.de/images/icons/24x24/warning.png" alt="'.t("Liefertermin auf Anfrage").'" title="'.t("Liefertermin auf Anfrage").'" />';

			$status = $best;
		}
		else
		{
/*			if($amount>0)
			{
				if ($row["ISTBESTAND"]>=$amount) $color='#008000';
				elseif ($row["ISTBESTAND"]>0) $color='#000080';
				else $color='#b30000';
				if ($row["ISTBESTAND"]>=$amount) $best=t("sofort lieferbar");
				elseif ($row["ISTBESTAND"]>0) $best=t("Nur noch wenige lieferbar");
				else $best=t("z.Z nicht lieferbar");
			}
			else
*/
			{
				if ($row["ISTBESTAND"]>10) $color='#008000';
				elseif ($row["ISTBESTAND"]>0) $color='#000080';
				else $color='#b30000';
				if ($row["ISTBESTAND"]>10) $best=t("sofort lieferbar");
				elseif ($row["ISTBESTAND"]>0) $best=t("Nur noch wenige lieferbar");
				else $best=t("z.Z nicht lieferbar");
			}
//			$status .= '<a href="'.PATHLANG.'online-shop/status/'.$id_item.'/'.$artnr.'" style="color:'.$color.';">'.$best.'</a>';

			$status .= '<span style="color:'.$color.';">'.$best.'</span>';
			$status .= '<ul><li>';
			$status .= '<table class="hover" style="width:400px;">';
			$status .= '	<tr>';
			$status .= '		<th>'.t("Standort").'</th>';
			$status .= '		<th>'.t("Lieferstatus").'</th>';
			$status .= '	</tr>';
	
			//Zentrallager
			$status .= '	<tr>';
			$status .= '		<td>'.t("Zentrallager").'</td>';
			$status .= '		<td style="color:'.$color.'">'.$best.'</td>';
			$status .= '	</tr>';
	
			if ( $_GET["lang"]=="it")
			{
				//RegionalCenter Italien
				$results=q("SELECT * FROM lagerrc WHERE RCNR=40 AND ARTNR='".$artnr."';", $dbshop, __FILE__, __LINE__);
				if( mysqli_num_rows($results)==0 )
				{
					$row["RCBEZ"]="DEPOT Baratta Italien";
					$row["ISTBESTAND"]=0;
					$status .= '	<tr>';
					$status .= '		<td>MAPCO '.str_replace("_", " ", $row["RCBEZ"]).'</td>';
					if ($row["ISTBESTAND"]>10) $color='#008000';
					elseif ($row["ISTBESTAND"]>0) $color='#000080';
					else $color='#b30000';
					if ($row["ISTBESTAND"]>10) $best=t("sofort lieferbar");
					elseif ($row["ISTBESTAND"]>0) $best=t("Nur noch wenige lieferbar");
					else $best=t("z.Z nicht lieferbar");
					$status .= '		<td><span style="color:'.$color.';">'.$best.'</span></td>';
					$status .= '	</tr>';
				}
				while($row=mysqli_fetch_array($results))
				{
					$status .= '	<tr>';
					$status .= '		<td>MAPCO '.str_replace("_", " ", $row["RCBEZ"]).'</td>';
					if ($row["ISTBESTAND"]>10) $color='#008000';
					elseif ($row["ISTBESTAND"]>0) $color='#000080';
					else $color='#b30000';
					if ($row["ISTBESTAND"]>10) $best=t("sofort lieferbar");
					elseif ($row["ISTBESTAND"]>0) $best=t("Nur noch wenige lieferbar");
					else $best=t("z.Z nicht lieferbar");
					$status .= '		<td><span style="color:'.$color.';">'.$best.'</span></td>';
					$status .= '	</tr>';
				}
			}

			if ( $_GET["lang"]=="fr")
			{
				//RegionalCenter Frankreich
				$results=q("SELECT * FROM lagerrc WHERE RCNR=39 AND ARTNR='".$artnr."';", $dbshop, __FILE__, __LINE__);
				if( mysqli_num_rows($results)==0 )
				{
					$row["RCBEZ"]="DEPOT Chassieu France";
					$row["ISTBESTAND"]=0;
					$status .= '	<tr>';
					$status .= '		<td>MAPCO '.str_replace("_", " ", $row["RCBEZ"]).'</td>';
					if ($row["ISTBESTAND"]>10) $color='#008000';
					elseif ($row["ISTBESTAND"]>0) $color='#000080';
					else $color='#b30000';
					if ($row["ISTBESTAND"]>10) $best=t("sofort lieferbar");
					elseif ($row["ISTBESTAND"]>0) $best=t("Nur noch wenige lieferbar");
					else $best=t("z.Z nicht lieferbar");
					$status .= '		<td><span style="color:'.$color.';">'.$best.'</span></td>';
					$status .= '	</tr>';
				}
				while($row=mysqli_fetch_array($results))
				{
					$status .= '	<tr>';
					$status .= '		<td>MAPCO '.str_replace("_", " ", $row["RCBEZ"]).'</td>';
					if ($row["ISTBESTAND"]>10) $color='#008000';
					elseif ($row["ISTBESTAND"]>0) $color='#000080';
					else $color='#b30000';
					if ($row["ISTBESTAND"]>10) $best=t("sofort lieferbar");
					elseif ($row["ISTBESTAND"]>0) $best=t("Nur noch wenige lieferbar");
					else $best=t("z.Z nicht lieferbar");
					$status .= '		<td><span style="color:'.$color.';">'.$best.'</span></td>';
					$status .= '	</tr>';
				}
			}

			if ($_SESSION["id_user"]==21371)
			{
				//RegionalCenter
				$results=q("SELECT * FROM lagerrc WHERE ARTNR='".$artnr."';", $dbshop, __FILE__, __LINE__);
				while($row=mysqli_fetch_array($results))
				{
					$status .= '	<tr>';
					$status .= '		<td>MAPCO RegionalCENTER '.str_replace("_", " ", $row["RCBEZ"]).'</td>';
					if ($row["ISTBESTAND"]>10) $color='#008000';
					elseif ($row["ISTBESTAND"]>0) $color='#000080';
					else $color='#b30000';
					if ($row["ISTBESTAND"]>10) $best=t("sofort lieferbar");
					elseif ($row["ISTBESTAND"]>0) $best=t("Nur noch wenige lieferbar");
					else $best='>10 Artikel';
					$status .= '		<td><span style="color:'.$color.';">'.$best.'</span></td>';
					$status .= '	</tr>';
				}
			}

			$status .= '</table></li></ul>';
			$status .= '</div>';
		}
		
		return($status);
		}
	}
	
	if (!function_exists("itemstatus_rc"))
	{
		function itemstatus_rc($id_item, $mail=0, $amount=1)
		{
			global $dbshop;
			
			$results=q("SELECT * FROM lagerrc AS a, shop_items AS b WHERE b.id_item='".$id_item."' AND b.MPN=a.ARTNR AND a.RCNR='".$_SESSION["rcid"]."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
			$row=mysqli_fetch_array($results);
			
			$status  = '<div id="itemstatus">';
			
			//Hauptansicht      
			if ($mail>0)
			{
				if ($row["ISTBESTAND"]>=$amount) $best= '<img src="http://www.mapco.de/images/lieferbar.jpg" alt="'.t("sofort lieferbar").'" title="'.t("sofort lieferbar").'" />';
				else
				{
					$results2=q("SELECT * FROM lager AS a, shop_items AS b WHERE b.id_item=".$id_item." AND b.MPN=a.ArtNr LIMIT 1;", $dbshop, __FILE__, __LINE__);
					$row=mysqli_fetch_array($results2);
					if ($row["ISTBESTAND"]>=$amount) $best= '<img src="http://www.mapco.de/images/auf_anfrage.jpg" alt="'.t("in der Zentrale vorrätig").'" title="'.t("in der Zentrale vorrätig").'" />';
					else $best= '<img src="http://www.mapco.de/images/nicht_lieferbar.jpg" alt="'.t("z.Z nicht lieferbar").'" title="'.t("z.Z nicht lieferbar").'" />';
				}
				$status .= $best;
				$status .= '</div>';
			}
			else
			{
				if ($row["BESTAND"]==1 or $row["BESTAND"]==2 or $row["BESTAND"]==3)
				{
					$color='#008000';
					$best=t("in").' '.$_SESSION["rcbez"].' '.t("vorrätig");
				}
				else
				{
					$results2=q("SELECT * FROM lager AS a, shop_items AS b WHERE b.id_item=".$id_item." AND b.MPN=a.ArtNr LIMIT 1;", $dbshop, __FILE__, __LINE__);
					$row2=mysqli_fetch_array($results2);
					if ($row2["Bestand"]==1)
					{
						$color='#DE9800';
						$best=t("in der Zentrale vorrätig");
					}
					else 
					{
						$color='#000080';
						$best=t("Liefertermin auf Anfrage");
					}
				}

//				$status .= '<a href="'.PATHLANG.'online-shop/status/'.$id_item.'/'.$artnr.'" style="color:'.$color.';">'.$best.'</a>';
	
				$status .= '<span style="color:'.$color.';">'.$best.'</span>';
				$status .= '<ul><li>';
				$status .= '<table class="hover" style="width:400px;">';
				$status .= '	<tr>';
				$status .= '		<th>'.t("Standort").'</th>';
				$status .= '		<th>'.t("Lieferstatus").'</th>';
				$status .= '	</tr>';
		
				//Zentrallager
				$results2=q("SELECT * FROM lager AS a, shop_items AS b WHERE b.id_item=".$id_item." AND b.MPN=a.ArtNr LIMIT 1;", $dbshop, __FILE__, __LINE__);
				$row2=mysqli_fetch_array($results2);				
				if ($row2["Bestand"]==1) $color='#008000';
				elseif ($row2["Bestand"]==2) $color='#000080';
				else $color='#b30000';
				if ($row2["Bestand"]==1) $best=t("1 Werktag Lieferzeit");
				elseif ($row2["Bestand"]==2) $best=t("Liefertermin auf Anfrage");
				else $best=t("z.Z nicht lieferbar");

				$status .= '	<tr>';
				$status .= '		<td>'.t("Zentrallager").'</td>';
				$status .= '		<td style="color:'.$color.'">'.$best.'</td>';
				$status .= '	</tr>';
		
				//RegionalCenter
				$status .= '	<tr>';
				$status .= '		<td>MAPCO RegionalCENTER '.$_SESSION["rcbez"].'</td>';
				if ($row["BESTAND"]==0) $color='#000080';
				elseif ($row["BESTAND"]==3) $color='#DE9800';
				elseif ($row["BESTAND"]==2) $color='#008000';
				else $color='#008000';
				if ($row["BESTAND"]==0) $best=t("Liefertermin auf Anfrage");
				elseif ($row["BESTAND"]==3) $best='< 5 '.t("Artikel").' '.t("vorrätig");
				elseif ($row["BESTAND"]==2) $best='> 5 '.t("Artikel").' '.t("vorrätig");
				else $best='> 10 '.t("Artikel").' '.t("vorrätig");
				$status .= '		<td><span style="color:'.$color.';">'.$best.'</span></td>';
				$status .= '	</tr>';
		
				$status .= '</table></li></ul>';
				$status .= '</div>';
			}
			
			return($status);
		}
	}
	
	if (!function_exists("itemstatus2"))
	{
		function itemstatus2($MPN, $mail=0)
		{
			global $dbshop;
			
			$status  = '<div id="itemstatus">';
			
		//Hauptansicht
		$results=q("SELECT * FROM lager WHERE ArtNr='".$MPN."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
        
		if ($mail>0)
		{
			if ($row["Bestand"]==1) $best= '<img src="http://www.mapco.de/images/lieferbar.jpg" alt="'.t("sofort lieferbar").'" title="'.t("sofort lieferbar").'" />';
			elseif ($row["Bestand"]==2) $best= '<img src="http://www.mapco.de/images/auf_anfrage.jpg" alt="'.t("Liefertermin auf Anfrage").'" title="'.t("Liefertermin auf Anfrage").'" />';
			else $best= '<img src="http://www.mapco.de/images/nicht_lieferbar.jpg" alt="'.t("z.Z nicht lieferbar").'" title="'.t("z.Z nicht lieferbar").'" />';

			$status .= $best;
			$status .= '</div>';
		}
		else
		{
			if ($row["Bestand"]==1) $color='#008000';
			elseif ($row["Bestand"]==2) $color='#000080';
			else $color='#b30000';
			if ($row["Bestand"]==1) $best=t("sofort lieferbar");
			elseif ($row["Bestand"]==2) $best=t("Liefertermin auf Anfrage");
			else $best=t("z.Z nicht lieferbar");
//			$status .= '<a href="'.PATHLANG.'online-shop/status/'.$id_item.'/'.$artnr.'" style="color:'.$color.';">'.$best.'</a>';

			$status .= '<span style="color:'.$color.';">'.$best.'</span>';
			$status .= '<ul><li>';
			$status .= '<table class="hover" style="width:400px;">';
			$status .= '	<tr>';
			$status .= '		<th>'.t("Standort").'</th>';
			$status .= '		<th>'.t("Lieferstatus").'</th>';
			$status .= '	</tr>';
	
			//Zentrallager
			$status .= '	<tr>';
			$status .= '		<td>'.t("Zentrallager").'</td>';
			$status .= '		<td style="color:'.$color.'">'.$best.'</td>';
			$status .= '	</tr>';
	
			if ($_SESSION["id_user"]==21371)
			{
				//RegionalCenter
				$results=q("SELECT * FROM lagerrc WHERE ARTNR='".$MPN."';", $dbshop, __FILE__, __LINE__);
				while($row=mysqli_fetch_array($results))
				{
					$status .= '	<tr>';
					$status .= '		<td>MAPCO RegionalCENTER '.str_replace("_", " ", $row["RCBEZ"]).'</td>';
					if ($row["BESTAND"]==0) $color='#b30000';
					elseif ($row["BESTAND"]==3) $color='#000080';
					elseif ($row["BESTAND"]==2) $color='#000080';
					else $color='#008000';
					if ($row["BESTAND"]==0) $best=t("z.Z nicht lieferbar");
					elseif ($row["BESTAND"]==3) $best='<5 '.t("Artikel");
					elseif ($row["BESTAND"]==2) $best='>5 '.t("Artikel");
					else $best='>10 Artikel';
					$status .= '		<td><span style="color:'.$color.';">'.$best.'</span></td>';
					$status .= '	</tr>';
				}
			}
	
			$status .= '</table></li></ul>';
			$status .= '</div>';
		}
		
		return($status);
		}
	}
?>