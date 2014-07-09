<?php
/**
 *
 * @class MOrder
 * @Namespace Mapco.Shop.Order
 * @author CHaendler    <chaendler (at) mapco.de>
 * @version 1.0
 * @modified     04/07/14
 * 
 * @require     
 *          class MBillingAddress (Mapco.Shop.Order.BillingAddress)
 *          class MShippingAddress (Mapco.Shop.Order.ShippingAddress)
 *          class MPaymentType    (Mapco.Shop.Payment.PaymentType)
 *          global function q()
 *          global function i()
 *          global function gewerblich()
 *          global var $dbshop
 *          php function array_push()
 *          php function mysqli_fetch_assoc()
 * 
 */

i('Mapco.Shop.Order.BillingAddress');
i('Mapco.Shop.Order.ShippingAddress');
i('Mapco.Shop.Payment.PaymentType');

class MOrder {
    
    // the order id
    protected $id;
    
    // the sql data set of the current order id
    protected $_data;
    
    // the shop_id of the current order id
    protected $shop_id;
    
    /*
     * __construct
     * 
     * @description     creates a new order instance, if param id is set - the instance autoloads the properties from database
     * 
     */
    public function __construct($id = NULL) 
    {
        if (isset($id)) { 
           $this->setId($id);
        }
        
    }
    
    /*
     * setId
     * 
     * @description     set a order id of the instance and autoload properties from database
     * 
     * @param       int $id
     * 
     */
    
    public function setId($id) 
    {
        if (isset($id)) 
        {
            $this->id = $id;
            $this->_load();
        }
    }
    
    /*
     * getProp
     * 
     * @description     get a property by field name/key
     * 
     * @return      mixed $value
     */
            
    public function getProp($key)
    {
        if (isset($this->_data[$key])) 
        {
            return $this->_data[$key];
        }
    }
    
    /*
     * getId
     * 
     * @description     get the order id of the instance
     * 
     * @return      int $id
     */
    
    public function getId() 
    {
        return $this->id;
    }
    
    /*
     * getBillingAddress
     * 
     * @description     get a MBillingAddress instance from the current order
     * 
     * @return  inst MBillingAddress    
     * 
     */
    
    public function getBillingAddress() 
    {

        if (isset($this->getProp('bill_adr_id')))
        {
            return new MBillingAddress($this->getProp('bill_adr_id'));
        }
        else 
        {
            return false;
        }
    }

    /*
     * getShippingAddress
     * 
     * @description     get a MShippingAddress instance from the current order
     * 
     * @return  inst MShippingAddress    
     * 
     */
    
    public function getShippingAddress()
    {
        if (isset($this->getProp('ship_adr_id')))
        {
            return new MShippingAddress($this->getProp('ship_adr_id'));
        }
        else if (isset($this->getProp('bill_adr_id')))
        {
            return new MShippingAddress($this->getProp('bill_adr_id'));
        }
    }
    
    /*
     * static getPaymentTypes
     * 
     * @description     get a array with MPaymentType instances of available PaymentTypes by order_id
     * 
     * @params  int $order_id 
     * 
     * @return  array of available MPaymentType instances
     * 
     */
        
    public static function getPaymentTypes($order_id) 
    {
                
        if (isset($order_id)) {
            
            // get order
            $Order = new MOrder($order_id);
            
            // get order BillingAddress instance
            $BillingAddress = $Order->getBillingAddress();
            
            // get order billing address country id
            $country_id = $BillingAddress->getProp('country_id');
            
            // get order shop id
            $shop_id = $Order->getProp('shop_id');
            
            // get order customer id
            $customer_id = $Order->getProp('customer_id');
            
            // TODO: GET ORDER CUSTOMER_TYPE_ID
            $customer_type_id = "";
            
            // set default customer_type_id
            $gewerblich = gewerblich($customer_id);
            if($customer_type_id == "" && $gewerblich == true) 
            {
                // default business customer_type_id 
                $customer_type_id = '2';
            }
            else if ($customer_type_id == "" && $gewerblich == false)
            {
                // default private customer_type_id
                $customer_type_id = '1';
            }
            
            // create empty array for return
            $return = array();
            
            // get available payment_types from database for $customer_type_id, $shop_id,$country_id
            if (isset($customer_type_id) && isset($shop_id) && isset($country_id))
            {
                $QUERY = "SELECT
                                shop_types.payment_type_id
                            FROM
                                shop_shops_payment_types shop_types 
                                    INNER JOIN
                                        shop_payment payments ON shop_types.payment_type_id = payments.paymenttype_id AND payments.shop_id = '" . $shop_id . "' 
                                    INNER JOIN
                                        shop_payment_countries countries ON payments.id_payment = countries.payment_id AND countries.country_id = '" . $country_id . "'
                                    INNER JOIN
                                        shop_payment_customer_types customers ON payments.id_payment = customers.payment_id AND customer_type_id = '" . $customer_type_id . "' 
                            WHERE 
                                shop_types.shop_id = '" . $shop_id . "'";

                $QueryResult = q($QUERY, $dbshop, __FILE__, __LINE__);
                
                while ($PaymentTypes = mysqli_fetch_assoc($QueryResult)) 
                {
                    // add for each payment_type a new MPaymentType instance to return array
                    array_push($return, new MPaymentType($PaymentTypes['payment_type_id']));
                }
            }
            
            return $return;
        }
    }
    
    /*
     * _load
     * 
     * @description     internal used function to get a order from database by selected instance id
     * 
     */
    
    private function _load() 
    {
        if (isset($this->id))
        {
            // sql query to get the dataset of the order id
            $results=q("SELECT * FROM `shop_orders` WHERE `id_order`=".$this->id, $dbshop, __FILE__, __LINE__);
            $this->_data = mysqli_fetch_assoc($results);
            $this->shop_id = $this->_data['shop_id'];
        }
    }
}