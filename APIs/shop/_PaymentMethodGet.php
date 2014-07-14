<?
/**********************************************************
*	SOA2 Service
*		shop.PaymentMethodGet
*
*	@author: Christopher Händler <chaendler(at)mapco.de>
*	@version: 0.2
*	@modified: 	10/07/14
*
*********************/

	/*
	*	@description
	*	- get payment methods with filter selection by country_id, customer_type_id, active
	*
	*	@param	int shop_id										(required)
	*	@param	int country_id									(optional)
	*	@param	int customer_type_id							(optional)
	*	@param	int active		[1 = active / 0 = inactive] 	(optional)
	*
	*	@return xml payments sort by ordering
	*/

		// check the minimum required post params	
		check_man_params
		(
			array
			(
				"shop_id" => "numericNN"
			)
		);
		
		// create filter array - we dont use $_POST in SQL Queries
		// TODO: we need a sql injection filter ... 
		$filter = array();
		$filter['shop_id']	=	$_POST['shop_id'];
		$filter['country_id']	= $_POST['country_id'];
		$filter['customer_type_id']	= $_POST['customer_type_id'];
		$filter['payment_type_id'] = $_POST['payment_type_id'];
		$filter['active'] = $_POST['active'];
		
		// default sql extensions
		$country = "";
		$customer_type = "";
		$state ="";
		
		// set sql extensions by filter array
		if (($filter['active'] == '0' || $filter['active'] == '1') && $filter['active'] != "") 
		{
			$state = "AND payments.active = '".$filter['active']."'";
		}
		
		if (isset($filter['country_id']) && $filter['country_id'] != 0)
		{
			$country = "AND countries.country_id = '" . $filter['country_id'] . "'";
		}
		if (isset($filter['customer_type_id']) && $filter['customer_type_id'] != 0)
		{
			$customer_type = "AND customers.customer_type_id = '" . $filter['customer_type_id'] . "'";
		}
		if (isset($filter['payment_type_id']) && $filter['payment_type_id'] != 0)
		{
			$payment_type = "AND shop_types.payment_type_id = '" . $filter['payment_type_id'] ."'";
		}
		
		// do not run without shop_id
        if (isset($filter['shop_id']))
        {
			 
			// sql filter query 
            $QUERY_PAYMENTS = "SELECT DISTINCT
                            payments.*
                        FROM
                            shop_shops_payment_types shop_types 
                                INNER JOIN
                                    shop_payment_dev payments 
										ON 
											shop_types.payment_type_id = payments.paymenttype_id 
											AND payments.shop_id = '" . $filter['shop_id'] . "' 
											".$state."
											".$payment_type."
                                INNER JOIN
                                    shop_payment_countries countries 
										ON 
											payments.id_payment = countries.payment_id 
											".$country." 
                                INNER JOIN
                                    shop_payment_customer_types customers 
										ON 
											payments.id_payment = customers.payment_id 
											".$customer_type." 
                        WHERE 
                            shop_types.shop_id = '" . $filter['shop_id'] . "'
						ORDER BY 
							payments.ordering ASC
						";		
												
			// fire the sql query					
            $Result = q($QUERY_PAYMENTS, $dbshop, __FILE__, __LINE__);
       
	   		// build xml 
			$xml ="";
			while($Payments = mysqli_fetch_assoc($Result))
			{
				$xml .= "<Payment>";
				foreach ($Payments as $key => $payment)
				{
					$xml .="<".$key."><![CDATA[".$payment."]]></".$key.">";
				} 
				$xml .= "</Payment>";
			}
			
			// print xml
			echo $xml; 
		}
	
?>