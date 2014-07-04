<?php
/**
 *
 * @class MPayment
 * @Namespace Mapco.Shop
 * @author CHaendler    <chaendler (at) mapco.de>
 * @version 1.0
 * @modified     04/07/14
 * 
 * @require
 *          class MObject (Mapco.Object)     
 *          global function q()
 *          global var $dbshop
 *          php function mysqli_fetch_assoc()
 *          php function mysqli_fetch_object()
 * 
 */

i('Mapco.Object');

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
    public function setId($id) 
    {
        if (isset($id)) 
        {
            $this->id = $id;
        }
    }
    
    // returns the payment id
    public function getId() 
    {
        return $this->id;
    }
           
    // returns a MPaymentType instance of the current payment id 
    public function getTypeObj() 
    {
        return $this->type;
    }
    
    public static function queryResultFormat($QueryResult, $output_format)  
    {
        switch ($output_format) 
        {
            case 'assoc':
                $return = mysqli_fetch_assoc($QueryResult);
                break;
            case 'object':
                $return = mysqli_fetch_object($QueryResult);
                break;
            case 'core_object':
                $ArrayObjects = array();
                while ( $PaymentTypesAssoc = mysqli_fetch_assoc($QueryResult) )
                {
                    array_push($ArrayObjects, new MObject($PaymentTypesAssoc));
                }
                $return = $ArrayObjects;
                break;
            case 'type_object':
                $ArrayTypeObjects = array();
                while ( $PaymentTypesAssoc = mysqli_fetch_assoc($QueryResult) )
                {
                    array_push($ArrayObjects, new MPaymentType($PaymentTypesAssoc));
                }
                $return = $ArrayTypeObjects; 
                break;
        } 
        return $return;
    }
    
    public function getPaymentTypes($filter_data, $output_format = "assoc")
    {
        
        $filter = MObject::allToAssoc($filter_data);
        
        if (isset($filter['customer_type_id']) && isset($filter['shop_id']) && isset($filter['country_id']))
        {
            $QUERY = "SELECT
                            shop_types.*
                        FROM
                            shop_shops_payment_types shop_types 
                                INNER JOIN
                                    shop_payment payments ON shop_types.payment_type_id = payments.paymenttype_id AND payments.shop_id = '" . $filter['shop_id'] . "' 
                                INNER JOIN
                                    shop_payment_countries countries ON payments.id_payment = countries.payment_id AND countries.country_id = '" . $filter['country_id'] . "'
                                INNER JOIN
                                    shop_payment_customer_types customers ON payments.id_payment = customers.payment_id AND customer_type_id = '" . $filter['customer_type_id'] . "' 
                        WHERE 
                            shop_types.shop_id = '" . $filter['shop_id'] . "'";

            $QueryResult = q($QUERY, $dbshop, __FILE__, __LINE__);
                        
            return MPayment::queryResultFormat($QueryResult, $output_format);
        }
        
    }
    
    public function getPaymentMethodsByType($data) 
    {
        $filter = MObject::allToAssoc($data);
        
        if (isset($data) && is_numeric($data))
        {
            $QUERY = "SELECT 
                            id_payment
                        FROM
                            shop_payment
                        WHERE 
                            paymenttype_id = '" . $data . "'";

            $Result = q($QUERY, $dbshop, __FILE__, __LINE__);
            $Payments = mysqli_fetch_assoc($Result);
            return $Payments;
        } 
    }
     
}