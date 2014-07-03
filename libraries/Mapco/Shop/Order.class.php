<?php

i('Mapco.Shop.BillingAddress');

/**
 * Description of Order
 *
 * @author CHaendler
 */
class MOrder {
    
    // the order id
    protected $id;
    
    // the sql data set of the current order id
    protected $_data;
    
    // the shop_id of the current order id
    protected $shop_id;
    
    
    public function __construct($id = NULL) 
    {
        if (isset($id)) { 
           $this->setId($id);
        }
        
    }
    
    public function setId($id) 
    {
        if (isset($id)) 
        {
            $this->id = $id;
            $this->_load();
        }
    }
    
    public function getProp($key)
    {
        if (isset($this->_data[$key])) 
        {
            return $this->_data[$key];
        }
    }
    
    public function getId() 
    {
        return $this->id;
    }
    
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
        
    public static function getPaymentTypes($order_id) 
    {
        if (isset($order_id)) {
            $nOrder = new MOrder($order_id);
            $BillingAddress = $nOrder->getBillingAddress();

        }
        
        if ()
        
        // sql query to find possible payment_types
        // create assoc array with MPaymentType instances
        // return assoc array
    }
    
    private function _load() 
    {
        if (isset($this->id))
        {
            // sql query to get the dataset of the order id
            $results=q("SELECT * FROM `shop_orders` WHERE `id_order`=".$this->id, $dbweb, __FILE__, __LINE__);
            $this->_data = mysqli_fetch_assoc($results);
            $this->shop_id = $this->_data['shop_id'];
        }
    }
    
    
}
