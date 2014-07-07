<?php


$templateCar = '
	<table>
		<thead>
			<tr>
				<th colspan="3">' . $vehicleResult['BEZ1'] . '</th>
			</tr>		
		</thead>
		
		<tbody>';
		foreach($shopItems as $shopItem)
		{
			$templateCar.= '
			<tr>
				<td>Für Model:
					<div><strong>' . $shopItemsKeyword['keyword'] . '</strong></div>
				</td>
				<td style="text-align: center;"><strong>' . $shopItem['MPN'] . '</strong></td>
				<td style="text-align: center;">Bild</td>
			</tr>';
		}
$templateCar.= '			
		</tbody>
	</table>';