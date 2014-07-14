<?php
/**
 *
 * @class MBillingAddress
 * @Namespace Mapco.Shop.Order
 * @author CHaendler    <chaendler (at) mapco.de>
 * @version 1.0
 * @modified     04/07/14
 * 
 * @require     
 *          global function q()
 *          global var $dbshop
 *          php function mysqli_fetch_assoc()
 * 
 */

class MBillingAddress
{
    
    protected $id;
    protected $_data;
    
    public function __construct($id)
    {
       if (isset($id)) 
       {
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
    
    public function getId() 
    {
        return $this->id;
    }
    
    protected function _load() 
    {
        if (isset($this->id))
        {
            // sql query to get the dataset of the order id
            $results=q("SELECT * FROM `shop_bill_adr` WHERE `adr_id`=".$this->id, $dbshop, __FILE__, __LINE__);
            $this->_data = mysqli_fetch_assoc($results);
        }
    }

    public function getProp($key)
    {
        if (isset($this->_data[$key])) 
        {
            return $this->_data[$key];
        }
    }
    
    public function getProps()
    {
        if (isset($this->_data)) 
        {
            return $this->_data;
        }
    }
    
}