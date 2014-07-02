<?php

session_start();
include("../config.php");

$submitTypes = array('orders');

if ($_POST['submitType'] == 'update') {

	$post_data = array();
	$post_data['API'] = "amazon";
	$post_data['APIRequest'] = "AmazonOrderItemsUpdate";
	$post_data['MessageType'] = "OrderReport";
	$post_data['action'] = "ListOrderItems";
	$post_data['id_account'] = $_POST['id_account'];
	$post_data['limit'] = $_POST['limit'];
	echo soa2($post_data, __FILE__, __LINE__, 'xml');
}