<?php
/**
 *
 * @class MPaymentType
 * @Namespace Mapco.Shop.Payment
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

class MPaymentType {
    
    protected $id;
    protected $_data;
    
    public function __construct($data)
    {
       if (isset($data)) 
       {
           if (is_numeric($data))
           {
                $this->setId($id);
           }
           
           if (is_array($data))
           {
               $this->_setData($data);
           }
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
    
    protected function _setData($data)
    {
        $this->_data = $data;
    }
    
    protected function _load() 
    {
        if (isset($this->id))
        {
            // sql query to get the dataset of the order id
            $results=q("SELECT * FROM `shop_payment_types` WHERE `adr_id`=".$this->id, $dbshop, __FILE__, __LINE__);
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
