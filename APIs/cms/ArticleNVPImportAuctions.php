<?php

	check_man_params(array("item_id" => "numericNN", "language_id" => "numericNN"));

	$check = 0;
	$data = array();
	$entries = 0;
		
	$res_account_sites = q("SELECT id_accountsite, title FROM ebay_accounts_sites WHERE language_id=".$_POST['language_id']." LIMIT 1;", $dbshop, __FILE__, __LINE__);
	$account_site = mysqli_fetch_assoc($res_account_sites);
	
	if ( mysqli_num_rows($res_account_sites) == 0 )
	{
		print '<Error>Keine Auktionen in dieser Sprache verfügbar!</Error>'."\n";
	}
	else
	{
		$res_auction = q("SELECT Title, SubTitle, accountsite_id FROM ebay_auctions WHERE shopitem_id=".$_POST['item_id']." AND accountsite_id=".$account_site['id_accountsite'].";", $dbshop, __FILE__, __LINE__);
		while($auctions = mysqli_fetch_assoc($res_auction))
		{
			$insert = 1;
					
			if ( $auctions['Title'] != '' )
			{
				$ordering = 0;	
				$check++;
				$res_nvp = q("SELECT ordering FROM shop_items_nvp WHERE item_id=".$_POST['item_id']." AND category_id=5 AND language_id=".$_POST['language_id']." AND name='".$auctions['Title']."';", $dbshop, __FILE__, __LINE__);
						
				while($nvp = mysqli_fetch_assoc($res_nvp))
				{
					$insert = 0;
						
					if ( $ordering == 0 )
					{
						$ordering = $nvp['ordering']+1;
					}
					else
					{
						$ordering = 1;
					}		
				}

				if ( $insert == 1 )
				{
					$data=array(); 
					$data['item_id'] = $_POST['item_id'];
					$data['category_id'] = 5;
					$data['language_id'] = $_POST['language_id'];
					$data['name'] = $auctions['Title'];
					$data['value'] = $auctions['SubTitle'];
					$data['comment'] = 'Import aus E-Bay Auktionen ('.$account_site['title'].')';
					$data['ordering']= $ordering;
					$data['active']=0;
					$entries++;
					$ordering++;
					$result=q_insert("shop_items_nvp", $data, $dbshop, __FILE__, __LINE__);
				}
			}
		}

		print '<check>'.$check.'</check>'."\n";
		print '<entries>'.$entries.'</entries>'."\n";
	}
?>