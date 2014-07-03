<?php

MLoader::import('Mapco.Object');

/**
 * Description of MPayment
 *
 * @author CHaendler
 */
class MPayment extends MObject{
    
    // assoc array sql table data of selected payment id
    protected $_data;
    
    // the selected payment id
    protected $id;
    
    // the MPaymentType instance of the selected payment id
    protected $type;

    // the payment type id of the selected payment
    protected $type_id;
    
    // the shop id of the selected payment
    protected $shop_id;
    
    public function _construct($properties) 
    {
        
        parent::__construct($properties);
        
    }
    
    // sets the payment id
    public function setID($id) 
    {
        if (isset($id)) 
        {
            $this->id = $id;
        }
    }
    
    // returns the payment id
    public function getID() 
    {
        return $this->id;
    }
           
    // returns a MPaymentType instance of the current payment id 
    public function getTypeObj() 
    {
        return $this->type;
    }
    
    public function getPayments($filter = NULL)
    {
        // if filter is defined convert filter to assoc array
        if (isset($filter)) 
        {
            $filter_assoc = MObject::allToAssoc($filter);
        }
        
        $filter_options = "
            {
                'order': 
                    {
                        'type': 'int',
                        'obj': 'MOrder'
                    },
                'type':
                    {
                        'type': 'int',
                        'obj': 'MPaymentType'
                    },
                'country':
                    {
                        'type': 'int',
                        'obj': 'MCountry'
                    },                    
                'shop_id':
                    {
                        'type': 'int',
                        'obj': 'MShop'
                    },
                'active':
                    {
                        'type':int'
                    }
            }
        ";
        
        $filters = MObject::allToAssoc($filter_options);
        
        $filter_ref = array();
        
        foreach ($filters as $key => $option)
        {
            if (isset($filter_assoc[$key]))
            {
                if (isset($option['obj']))
                {
                    $class = $option['obj'];
                    $filter_key = $filter_assoc[$key];
                    $type = $option['type'];
                }
                if ($filter_key instanceof $class)
                {
                    // key is a instance of a class
                    $filter_ref[$key] = $filter_key->getId();
                }
                else if(isset($type))
                {
                    switch ($type) {
                        case 'int':
                            if (is_numeric($filter['key'])) 
                            {
                               $filter_ref[$key] = $filter_key;
                            }
                        break;
                    }
                }
                
                
            }
            
            // build sql query for payments with filter options
            
            if (isset($filter_ref['shop_id']))
            {
                $where .= "`shop_id`= '".$filter_ref['shop_id']."'";
            }
            
            if (isset($filter_ref['order']))
            {
                $Order = new MOrder($filter_ref['order']);
                $PaymentTypes = $Order->getPaymentTypes();
                
            }
            
            
            $SQL_QUERY = "SELECT * FROM `shop_payment` WHERE $where";
            
            
        }       
    }
      
}

/*
 * Examples for using OOP in API services
 * 
 * 
 * 
 * 
 */




/*************************************************************
 * Example for MOrder
 *  - get order by id
 *  - get possible PaymentTypes by order id
 *  requires:
 *               order_id
 */

$order_id = 12345;

i('Mapco.Shop.Order');


// get all possible PaymentTypes of the current instance order id
$PaymentTypes = MOrder::getPaymentTypes($ORDER_ID);






/******************************************************************
 * Example
 *  - get possible payments by order id and payment_type_id
 *  required keys (json or array): 
 *              order (order_id)
 *              type (payment_type_id)
 */

i('Mapco.Shop.Payment');

// json example
$Payments = MPayment::getPayments("{'order': $ORDER_ID, 'type': $PAYMENT_TYPE_ID}");

// array example
$filter = array();
$filter['order'] = $order_id;
$filter['type'] = $payment_type_id;
$Payments = MPayment::getPayments($filter);



// display the payments by loop $Payments


/******************************************************************
 * Example
 *  Shipping Selection
 *  requires: 
 *              payment_id
 *              order_id
 *              
 */

$order_id = $_POST['order_id'];
        
$payment_id = $_POST['payment_id'];

i('Mapco.Shop.Payment');
i('Mapco Shop.Order');

// get instance of Payment
$Payment = new MPayment();

// get instance of the order
$Order = MOrder::Get($order_id);


// add payment method to payment
$Payment->setID($payment_id);

// add order and payment_type to payment
$Payment->addOrder($Order);

if ($Payment->isValid())
{
    $Shippings = $Payment->getAvailableShippings();
}

// display the shippings by loop $Shippings

/******************************************************************
 * Example
 *  Proceed checkout
 *  requires: 
 *              shipping_id
 *              order_id
 *              
 */

$shipping_id = $_POST['shipping_id'];
$order_id = $_POST['order_id'];








