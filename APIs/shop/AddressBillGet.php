<?php

/***
 *	@author: rlange@mapco.de
 *	Address Bill Service for Shop
 *	- get the address bill
 *
*******************************************************************************/

include("../functions/cms_core.php");

//	keep post submit
$post = $_POST;

	/**
	 *	Amazon Products List
	 *
	 */
	if ($post['action'] == 'listAddressBill') {
		
		//	get user data by user id
		$data = array();
		$data['from'] = 'shop_bill_adr_save';
		$data['select'] = '*';
		$data['where'] = "
			user_id = '" . $post['user_id'] . "'
		";
		$users = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 0, 'shop',  __FILE__, __LINE__);
		if (count($users) > 0) {
			$xml = '';
			foreach($users as $user)
			{
				if ($user['standard'] == 1) {
					$xml.= getXmlAddressBill($user, 'widgetFieldAddressBillStandard');
				}
				if ($user['standard_ship_adr'] == 1) {
					$xml.= getXmlAddressBill($user, 'widgetFieldAddressBillShipping');
				}				
				if ($user['standard'] != 1 && $user['standard_ship_adr'] != 1) {
					$xml.= getXmlAddressBill($user, 'widgetFieldAddressBill');
				}
			}			
		}
		echo $xml;
	}
	
	
	
	
	
	function getXmlAddressBill($user, $envelope)
	{
		$xml = '<user_id><![CDATA[' . $user["user_id"] . ']]></user_id>' . "\n";
		$xml.= '<shop_id><![CDATA[' . $user["shop_id"] . ']]></shop_id>' . "\n";
		$xml.= '<company><![CDATA[' . $user["company"] . ']]></company>' . "\n";
		$xml.= '<firstname><![CDATA[' . $user["firstname"] . ']]></firstname>' . "\n";
		$xml.= '<lastname><![CDATA[' . $user["lastname"] . ']]></lastname>' . "\n";
		$xml.= '<street><![CDATA[' . $user["street"] . ']]></street>' . "\n";
		$xml.= '<number><![CDATA[' . $user["number"] . ']]></number>' . "\n";
		$xml.= '<zip><![CDATA[' . $user["zip"] . ']]></zip>' . "\n";
		$xml.= '<city><![CDATA[' . $user["city"] . ']]></city>' . "\n";
		$xml.= '<country><![CDATA[' . $user["country"] . ']]></country>' . "\n";
		$xml.= '<standard><![CDATA[' . $user["standard"] . ']]></standard>' . "\n";
		$xml.= '<standard_ship_adr><![CDATA[' . $user["standard_ship_adr"] . ']]></standard_ship_adr>' . "\n";
		$xml.= '<active><![CDATA[' . $user["active"] . ']]></active>' . "\n";
		$xml.= '<active_ship_adr><![CDATA[' . $user["active_ship_adr"] . ']]></active_ship_adr>' . "\n";
		return '<' . $envelope . '>' . $xml . '</' . $envelope . '>' . "\n";
	}