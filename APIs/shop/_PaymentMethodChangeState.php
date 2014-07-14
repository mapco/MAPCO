<?
/**********************************************************
*	SOA2 Service
*		shop.PaymentMethodChangeState
*
*	@author: Christopher Händler <chaendler(at)mapco.de>
*	@version: 0.2
*	@modified: 	10/07/14
*
*********************/

	/*
	*	@description
	*	- toggle the payment active flag (service call without param active)
	*	- or direct set payment active flag (service call with param active)
	*
	*	@param	int payment_id									(required)
	*	@param	int active		[1 = active / 0 = inactive] 	(optional)
	*
	*/
		
		// setup the currently used sql table name
		define('TBL_SHOP_PAYMENT','shop_payment_dev');
		
		// check the minimum required post params
		check_man_params(
				array
				(
					"payment_id" => "numericNN"
				)	
		);
		
		// create filter array - we dont use $_POST in SQL Queries
		// TODO: we need a sql injection filter ... 
		$filter = array();
		$filter['payment_id'] = $_POST['payment_id'];
		$filter['active'] = $_POST['active'];
		
		// create data array for sql update fields
		$data = array();
		
		// check toggle or direct set
		if (isset($filter['active']) && $filter['active'] != "") 
		{
			// direct mode
			$data['active'] = $filter['active'];
		} 
		else
		{
			// toggle mode
			// get the current state of payment_id
			$Result = q("SELECT active FROM ".TBL_SHOP_PAYMENT." WHERE `id_payment`= '".$filter['payment_id']."' LIMIT 1", $dbshop, __FILE__, __LINE__);
			$Payment = mysqli_fetch_assoc($Result);
			// set new data active field
			if ($Payment['active'] == 1) 
			{
				$data['active'] = 0;
			} else {
				$data['active'] = 1;
			}
		}
		
		// fire the sql update query
		$where = "WHERE `id_payment`='".$filter['payment_id']."' LIMIT 1";	
		q_update(TBL_SHOP_PAYMENT, $data, $where, $dbshop, __FILE__, __LINE__);

?>