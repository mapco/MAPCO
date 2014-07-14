<?
/**********************************************************
*	SOA2 Service
*		shop.PaymentMethodUpdate
*
*	@author: Christopher Händler <chaendler(at)mapco.de>
*	@version: 0.1
*	@modified: 	10/07/14
*
*********************/

	/*
	*	@description
	*	- sql update payments (fields restricted)
	*
	*	@param	int payment_id			(required)
	*	@param	string payment		 	(optional)
	*	@param	string payment_memo		(optional)
	*/
		
		// setup the currently used sql table name
		define('TBL_SHOP_PAYMENT','shop_payment_dev');
		
		// check the minimum required post params
		check_man_params(
				array
				(
					"id_payment" => "numericNN"
				)	
		);
		
		// create filter array - we dont use $_POST in SQL Queries
		// TODO: we need a sql injection filter ... 
		$filter = array();
		$filter['id_payment'] = $_POST['id_payment'];
		if (isset($_POST['payment']))
		{
			$filter['payment'] = $_POST['payment'];
		}
		
		if (isset($_POST['payment_memo']))
		{
			$filter['payment_memo'] = $_POST['payment_memo'];
		}
		
		// fire the sql update query
		$where = "WHERE `id_payment`='".$filter['id_payment']."' LIMIT 1";	
		q_update(TBL_SHOP_PAYMENT, $filter, $where, $dbshop, __FILE__, __LINE__);

?>