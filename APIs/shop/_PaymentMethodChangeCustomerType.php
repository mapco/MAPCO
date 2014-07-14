<?
/**********************************************************
*	SOA2 Service
*		shop.PaymentMethodChangeCustomerType
*
*	@author: Christopher Händler <chaendler(at)mapco.de>
*	@version: 0.1
*	@modified: 	10/07/14
*
*********************/

	/*
	*	@description
	*	- toggle state or create a new active payment customer_type 
	*
	*	@param	int payment_id					(required)
	*	@param	int customer_type_id		 	(required)
	*
	*/
		
		// setup the currently used sql table name
		define('TBL_SHOP_PAYMENT_CUSTOMER_TYPES','shop_payment_customer_types');
		
		// delete inactive customer_type items or use active flag (sql delete or sql update)
		define('OPTION_DELETE_CUSTOMER_TYPES', true);
		
		// check the minimum required post params
		check_man_params(
				array
				(
					"payment_id" => "numericNN",
					"customer_type_id" => "numericNN"
				)	
		);
		
		// create filter array - we dont use $_POST in SQL Queries
		// TODO: we need a sql injection filter ... 
		$filter = array();
		$filter['payment_id'] = $_POST['payment_id'];
		$filter['customer_type_id'] = $_POST['customer_type_id'];
		
		// create data array for sql update fields
		$data = array();
		
		// toggle mode
		// check the current customer_type of payment_id
		$Result = q("SELECT id_payment_customer_types,active FROM ".TBL_SHOP_PAYMENT_CUSTOMER_TYPES." WHERE `payment_id`= '".$filter['payment_id']."' AND `customer_type_id`='".$filter['customer_type_id']."' LIMIT 1", $dbshop, __FILE__, __LINE__);

		
		if ($Result->num_rows == 1)
		{
			$PaymentCustomerType = mysqli_fetch_assoc($Result);
			
			if (OPTION_DELETE_CUSTOMER_TYPES)
			{
				$ResultDelete = q("DELETE FROM ".TBL_SHOP_PAYMENT_CUSTOMER_TYPES." WHERE id_payment_customer_types= '".$PaymentCustomerType['id_payment_customer_types']."' LIMIT 1", $dbshop, __FILE__, __LINE__);
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
				$where = "WHERE `payment_id`='".$filter['payment_id']."' AND `customer_type_id`='".$filter['customer_type_id']."' LIMIT 1";	
				q_update(TBL_SHOP_PAYMENT_CUSTOMER_TYPES, $data, $where, $dbshop, __FILE__, __LINE__);
			}
		}
		else 
		{
			// create new customer_type item
			$data['payment_id'] = $filter['payment_id'];
			$data['customer_type_id'] = $filter['customer_type_id'];
			$data['active'] = '1';
			
			q_insert(TBL_SHOP_PAYMENT_CUSTOMER_TYPES, $data, $dbshop, __FILE__, __LINE__);	
		}
?>