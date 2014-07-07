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
            $criteria['htmlImgTag'] = true;
			$images = getImagesByArticleId($shopItem, $criteria);
			$templateCar.= '
			<tr>
				<td>Für Model:
					<div><strong>' . $shopItemsKeyword['keyword'] . '</strong></div>
				</td>
				<td style="text-align: center;"><strong>' . $shopItem['MPN'] . '</strong></td>
				<td style="text-align: center;width:200px;float:none;">'. $images . '</td>
			</tr>';
		}
$templateCar.= '
		</tbody>
	</table>';