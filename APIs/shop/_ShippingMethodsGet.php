<?
/**********************************************************
*	SOA2 Service
*		shop.ShippingMethodsGet
*
*	@author: Christopher Händler <chaendler(at)mapco.de>
*	@version: 0.1
*	@modified: 	10/07/14
*
*********************/

		check_man_params
		(
			array
			(
				"shop_id" => "numericNN",
			)
		);
		
		/*
		*	filter setup
		*/
		$filter = array();
		$filter['shop_id']	=	$_POST['shop_id'];
		$filter['country_id']	= $_POST['country_id'];
		$filter['customer_type_id']	= $_POST['customer_type_id'];
		$filter['warehouse_id'] 	= $_POST['warehouse_id'];
		$filter['payment_id']		= $_POST['payment_id'];
		
		$country = "";
		$customer_type = "";
		$warehouse = "";

		/*
		*	sql filter setup
		*/
		if (isset($filter['payment_id'])) 
		{
			$payment = "AND shippings.payment_id = '" . $filter['payment_id'] . "'";
		}
		
		if (isset($filter['country_id']) && $filter['country_id'] != 0)
		{
			$country = "AND countries.country_id = '" . $filter['country_id'] . "'";
		}
		
		if (isset($filter['customer_type_id']) && $filter['customer_type_id'] != 0)
		{
			$customer_type = "AND customers.customer_type_id = '" . $filter['customer_type_id'] . "'";
		}
		
		if (isset($filter['warehouse_id']) && $filter['warehouse_id'] != 0)
		{
			$warehouse = "AND warehouses.warehouse_id = '" .$filter['warehouse_id']. "' ";
		}
		
		if (isset($filter['shop_id'])) 
		{
			/*
			*	get possible shippings for the shop
			*/
			$QUERY = "SELECT DISTINCT
							shippings.*
						FROM
							shop_shops_shipping_types shop_types
								INNER JOIN
									shop_shipping_dev shippings ON shop.types.shipping_type_id = shippings.shippingtype_id AND shippings.shop_id = '" . $filter['shop_id'] ." ".$payment."'
								INNER JOIN
									shop_shipping_countries countries ON shippings.id_shipping = countries.shipping_id ".$country."
								INNER JOIN
									shop_shipping_customer_types customers ON shippings.id_shipping = customers.shipping_id ".$customer_type."
								INNER JOIN
									shop_shipping_warehouses warehouses ON shippings.id_shipping = warehouses.shipping_id ".$warehouse."
						WHERE
							shop_types.shop_id = '" . $filter['shop_id'] . "'
						";	
		
            $QueryResult = q($QUERY, $dbshop, __FILE__, __LINE__);
			$xml ="";
			while($Shippings = mysqli_fetch_assoc($QueryResult))
			{
				$xml .= "<Shipping>";
				foreach ($Shippings as $key => $shipping)
				{
					$xml .="<".$key."><![CDATA[".$shipping."]]></".$key.">";
				} 
				$xml .= "</Shipping>";
			}
			echo $xml; 
		}
?>