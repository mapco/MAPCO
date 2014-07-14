<?
/**********************************************************
*	SOA2 Service
*		shop.PaymentMethodChangeCountry
*
*	@author: Christopher Händler <chaendler(at)mapco.de>
*	@version: 0.1
*	@modified: 	10/07/14
*
*********************/

	/*
	*	@description
	*	- toggle state or create a new active payment country 
	*
	*	@param	int payment_id			(required)
	*	@param	int country_id		 	(required)
	*
	*/
		
		// setup the currently used sql table name
		define('TBL_SHOP_PAYMENT_COUNTRIES','shop_payment_countries');
		
		// delete inactive country items or use active flag (sql delete or sql update)
		define('OPTION_DELETE_COUNTRIES', true);
		
		// check the minimum required post params
		check_man_params(
				array
				(
					"payment_id" => "numericNN",
					"country_id" => "numericNN"
				)	
		);
		
		// create filter array - we dont use $_POST in SQL Queries
		// TODO: we need a sql injection filter ... 
		$filter = array();
		$filter['payment_id'] = $_POST['payment_id'];
		$filter['country_id'] = $_POST['country_id'];
		
		// create data array for sql update fields
		$data = array();
		
		// toggle mode
		// check the current country of payment_id
		$Result = q("SELECT id,active FROM ".TBL_SHOP_PAYMENT_COUNTRIES." WHERE `payment_id`= '".$filter['payment_id']."' AND `country_id`='".$filter['country_id']."' LIMIT 1", $dbshop, __FILE__, __LINE__);

		
		if ($Result->num_rows == 1)
		{
			$PaymentCountry = mysqli_fetch_assoc($Result);
			
			if (OPTION_DELETE_COUNTRIES)
			{
				$ResultDelete = q("DELETE FROM ".TBL_SHOP_PAYMENT_COUNTRIES." WHERE id= '".$PaymentCountry['id']."' LIMIT 1", $dbshop, __FILE__, __LINE__);
			}
			else 
			{				
				// set new data active field
				if ($PaymentCountry['active'] == 1) 
				{
					$data['active'] = 0;
				} else {
					$data['active'] = 1;
				}
				
				// fire the sql update query
				$where = "WHERE `payment_id`='".$filter['payment_id']."' AND `country_id`='".$filter['country_id']."' LIMIT 1";	
				q_update(TBL_SHOP_PAYMENT_COUNTRIES, $data, $where, $dbshop, __FILE__, __LINE__);
			}
		}
		else 
		{
			// create new country item
			$data['payment_id'] = $filter['payment_id'];
			$data['country_id'] = $filter['country_id'];
			$data['active'] = '1';
			
			q_insert(TBL_SHOP_PAYMENT_COUNTRIES, $data, $dbshop, __FILE__, __LINE__);	
		}
?>