<?php

/***
 *	@author: rlange@mapco.de
 *	Amazon Service for product bundles get
 *	- get the product bundles
 *
*******************************************************************************/

$PATH = dirname(__FILE__);
require_once($PATH . '/Model/AmazonModel.php');
include("../functions/cms_core.php");

// keep post submit
$post = $_POST;

/*
 *--------------------------------------- Product Bundle Items ------------------------------------------------
 */

    /**
     * get amazon products bundle listing
     */
    if ($post['action'] == 'listProductBundle') 
	{	
		$data = array();
		$data['from'] = 'amazon_products';
		$data['select'] = '*';
		$data['where'] = "
			bundle = 1
            AND account_id = '" . $post['account_id'] . "'
            AND accountsite_id = '" . $post['accountsite_id'] . "'
		";
		$data['orderBy'] = "lastmod DESC";
		$amazonProductsResults = SQLSelect($data['from'], $data['select'], $data['where'], $data['orderBy'], 0, $post['limit'], 'shop',  __FILE__, __LINE__);
		if (count($amazonProductsResults) > 0) 
		{
            $xml = '';
            foreach($amazonProductsResults as $product)
            {			
                $xml.= '<AmazonProductsBundles>';
                    $xml.= '<id_product>' . $product["id_product"] . '</id_product>' . "\n";
                    $xml.= '<SKU><![CDATA[' . $product["SKU"] . ']]></SKU>' . "\n";
					$xml.= '<EAN><![CDATA[' . $product["EAN"] . ']]></EAN>' . "\n";
					$xml.= '<StandardPrice>' . $product["StandardPrice"] . '</StandardPrice>' . "\n";
                    $xml.= '<SellerSKU><![CDATA[' . $product["SellerSKU"] . ']]></SellerSKU>' . "\n";
                    $xml.= '<QuantityOrdered>' . $product["QuantityOrdered"] . '</QuantityOrdered>' . "\n";
                    $xml.= '<ItemPriceAmount>' . $product["ItemPriceAmount"] . '</ItemPriceAmount>' . "\n";
                    $xml.= '<Title><![CDATA[' . $product["Title"] . ']]></Title>' . "\n";
					$xml.= '<Quantity>' . $product["Quantity"] . '</Quantity>' . "\n";
					$xml.= '<lastmod><![CDATA[' . getDateTime($product["lastmod"]) . ']]></lastmod>' . "\n";
                $xml.= '</AmazonProductsBundles>' . "\n";
            }

            $xmlList = '<amazonProductBundlesList>' . "\n";
            $xmlList.= '	<account_id>' . $post['account_id'] . '</account_id>' . "\n";
            $xmlList.= '	<accountsite_id>' . $post['accountsite_id'] . '</accountsite_id>' . "\n";
            $xmlList.= '</amazonProductBundlesList>' . "\n";
            echo $xml . $xmlList;
			
			//	update bundle price later then 2 hours
			if ($product["lastmod"] < (time() - 7200)) {
				$post_data = array();
				$post_data["API"] = "amazon";
				$post_data["APIRequest"] = "AmazonProductsTasks";
				$post_data["submitType"] = 'taskProductBundlePriceUpdate';
				$post_data["account_id"] = $post['account_id'];
				$post_data["accountsite_id"] = $post['accountsite_id'];
				soa2($post_data, __FILE__, __LINE__, 'xml');
			}
        }
    }

    /**
     * get amazon product as bundle
     */
    if ($post['action'] == 'getProductBundle') 
	{
		$data = array();
		$data['from'] = 'amazon_products';
		$data['select'] = '*';
		$data['where'] = "
			bundle = 1
            AND id_product = '" . $post['id_product']. "'
		";	
		$product = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 1, 'shop',  __FILE__, __LINE__);
		if (count($product) > 0) 
		{
				$xml = '<AmazonProductsBundles>' . "\n";
					$xml.= '<id_product>' . $product["id_product"] . '</id_product>' . "\n";
					$xml.= '<SKU><![CDATA[' . $product["SKU"] . ']]></SKU>' . "\n";
					$xml.= '<EAN><![CDATA[' . $product["EAN"] . ']]></EAN>' . "\n";
					$xml.= '<StandardPrice>' . $product["StandardPrice"] . '></StandardPrice>' . "\n";
					$xml.= '<SellerSKU><![CDATA[' . $product["SellerSKU"] . ']]></SellerSKU>' . "\n";
					$xml.= '<QuantityOrdered>' . $product["QuantityOrdered"] . '</QuantityOrdered>' . "\n";
					$xml.= '<ItemPriceAmount>' . $product["ItemPriceAmount"] . '</ItemPriceAmount>' . "\n";
					$xml.= '<Title><![CDATA[' . $product["Title"] . ']]></Title>' . "\n";
				$xml.= '</AmazonProductsBundles>' . "\n";
			echo $xml;
		} else {
			$xml = '<AmazonProductsBundles>'. "\n";
			$xml.= '	<error>not found</error>'. "\n";
			$xml.= '</AmazonProductsBundles>' . "\n";
			echo $xml;
		}
    }

    /**
     * add products for products bundles
     */
    if ($post['action'] == 'addProductBundle') 
	{
        $amazonAccountsSite = getAmazonAccountsites($post);

		$field = array(
			'table' => 'amazon_products',
			'lastInsertId' => 1
		);
        $data = array();
        $data["SKU"] = $post["SKU"];
		$data["EAN"] = $post["EAN"];
        $data["Title"] = $post["Title"];
        $data["bundle"] = 1;
        $data["accountsite_id"] = $amazonAccountsSite['id_accountsite'];
        $data["account_id"] = $amazonAccountsSite['account_id'];

        $data["firstmod"] = time();
        $data["firstmod_user"] = getAmazonSessionUserId();
        $data["lastmod"] = time();
        $data["lastmod_user"] = getAmazonSessionUserId();
		$amazonProductsBundlesId = SQLInsert($field, $data, 'shop', __FILE__, __LINE__);

        $xml = '<addAmazonProductBundle>' . "\n";
        $xml.= '	<amazon_products_id>' . $amazonProductsBundlesId . '</amazon_products_id>' . "\n";
        $xml.= '	<account_id>' . $post['account_id'] . '</account_id>' . "\n";
        $xml.= '	<accountsite_id>' . $post['accountsite_id'] . '</accountsite_id>' . "\n";
        $xml.= '</addAmazonProductBundle>' . "\n";
        echo $xml;
    }

    /**
     * edit products bundles
     */
    if ($post['action'] == 'editProductBundle') 
	{
        $data = array();
        $data["SKU"] = $post["SKU"];
		$data["EAN"] = $post["EAN"];
        $data["Title"] = $post["Title"];

        $data["lastmod"] = time();
        $data["lastmod_user"] = getAmazonSessionUserId();
        $addWhere = "
			id_product = " . $post["id_product"];
		$results = SQLUpdate('amazon_products', $data, $addWhere, 'shop', __FILE__, __LINE__);
    }

    /**
     * execute delete product bundle
     */
    if ($post['action'] == 'deleteProductBundle') 
	{
        $amazonProductBundeleQuery = "
            DELETE FROM amazon_products
            WHERE id_product = '" . $post["id_product"] . "'";
        $results = q($amazonProductBundeleQuery, $dbshop, __LINE__, __FILE__);

        $xml = '<deleteAmazonProductBundle>' . "\n";
        $xml.= '	<account_id>' . $post['account_id'] . '</account_id>' . "\n";
        $xml.= '	<accountsite_id>' . $post['accountsite_id'] . '</accountsite_id>' . "\n";
        $xml.= '</deleteAmazonProductBundle>' . "\n";
        echo $xml;
    }

    /*
    *--------------------------------------- Product Bundle Items ------------------------------------------------
    */
	
    /**
     * get amazon product bundle listing
     */
    if ($post['action'] == 'listProductBundleItems') 
	{
        $amazonProductsQuery = "
            SELECT *
            FROM amazon_products_bundles
            WHERE product_id = '" . $post['product_id'] . "'";
        if (isset($post['limit']) && $post['limit'] > 0) 
		{
            $amazonProductsQuery.= "
                LIMIT " . $post['limit'];
        }
        $amazonProductsResult = q($amazonProductsQuery, $dbshop, __FILE__, __LINE__);
        if (mysqli_num_rows($amazonProductsResult) > 0) 
		{
            $xml = '';
            while($product = mysqli_fetch_array($amazonProductsResult))
            {
                $xml.= '<AmazonProductsBundlesItem>';
                $xml.= '<id_bundle>' . $product["id_bundle"] . '</id_bundle>' . "\n";
                    $xml.= '<product_id>' . $product["product_id"] . '</product_id>' . "\n";
                    $xml.= '<SKU><![CDATA[' . $product["SKU"] . ']]></SKU>' . "\n";
                    $xml.= '<SellerSKU><![CDATA[' . $product["SellerSKU"] . ']]></SellerSKU>' . "\n";
                    $xml.= '<QuantityOrdered>' . $product["QuantityOrdered"] . '</QuantityOrdered>' . "\n";
                    $xml.= '<ItemPriceAmount>' . $product["ItemPriceAmount"] . '</ItemPriceAmount>' . "\n";
                $xml.= '</AmazonProductsBundlesItem>' . "\n";
            }
            echo $xml;
        }
    }

    /**
     * get product bundle item
     */
    if ($post['action'] == 'getProductBundleItem') 
	{
        $amazonProductsBundlesQuery = "
            SELECT *
            FROM amazon_products_bundles
            WHERE id_bundle = '" . $post['id_bundle'] . "'";
        $amazonProductsBundlesResult = q($amazonProductsBundlesQuery, $dbshop, __FILE__, __LINE__);
        $item = mysqli_fetch_assoc($amazonProductsBundlesResult);

        $xml = '<AmazonProductsBundlesItem>';
        $xml.= '<id_bundle>' . $item["id_bundle"] . '</id_bundle>' . "\n";
            $xml.= '<product_id>' . $item["product_id"] . '</product_id>' . "\n";
            $xml.= '<SKU><![CDATA[' . $item["SKU"] . ']]></SKU>' . "\n";
            $xml.= '<SellerSKU><![CDATA[' . $item["SellerSKU"] . ']]></SellerSKU>' . "\n";
            $xml.= '<QuantityOrdered>' . $item["QuantityOrdered"] . '</QuantityOrdered>' . "\n";
            $xml.= '<ItemPriceAmount>' . $item["ItemPriceAmount"] . '</ItemPriceAmount>' . "\n";
        $xml.= '</AmazonProductsBundlesItem>' . "\n";
        echo $xml;
    }

    /**
     * add products bundles
     */
    if ($post['action'] == 'addProductBundleItem') 
	{
        $price = (float)str_replace(",", ".", $post["ItemPriceAmount"]);
        $price = round($price, 2);

		$field = array(
			'table' => 'amazon_products_bundles'
		);
        $data = array();
        $data["product_id"] = $post["product_id"];
        $data["SKU"] = $post["SKU"];
        $data["SellerSKU"] = $post["SellerSKU"];
        $data["QuantityOrdered"] = $post["QuantityOrdered"];
        $data["ItemPriceAmount"] = $price;

        $data["firstmod"] = time();
        $data["firstmod_user"] = $_SESSION["id_user"];
        $data["lastmod"] = time();
        $data["lastmod_user"] = $_SESSION["id_user"];
        $results = SQLInsert($field, $data, 'shop', __FILE__, __LINE__);
        echo '
            <product_id>' . $post["product_id"] . '</product_id>
            <SKU>' . $post["SKU"] . '</SKU>
        ';
    }

    /**
     * edit product bundle
     */
    if ($post['action'] == 'editProductBundleItem') 
	{
        $price = (float)str_replace(",", ".", $post["ItemPriceAmount"]);
        $price = round($price, 2);

        $data = array();
        $data["SellerSKU"] = $post["SellerSKU"];
        $data["QuantityOrdered"] = $post["QuantityOrdered"];
        $data["ItemPriceAmount"] = $price;

        $data["lastmod"] = time();
        $data["lastmod_user"] = $_SESSION["id_user"];
        $addWhere = "WHERE id_bundle = " . $post["id_bundle"] . ";";
        $results = SQLUpdate('amazon_products_bundles', $data, $addWhere, 'shop', __FILE__, __LINE__);
    }

    /**
     * execute delete product bundle item
     */
    if ($post['action'] == 'deleteProductBundleItem') 
	{
        $res = q("
            DELETE FROM amazon_products_bundles
            WHERE id_bundle = '" . $post["id_bundle"] . "';", $dbshop, __LINE__, __FILE__);
    }