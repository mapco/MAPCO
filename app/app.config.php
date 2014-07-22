<?php

//connect to databases
if (!isset($dbweb))
{
	$db = array();
	$db['host'] = 'localhost';
	$db['user'] = 'sql-www-web';
	$db['password'] = 'B2n7hsGkj4dvbT';
	$db['name'] = 'www-web';
	$dbweb = mysqli_connect($db['host'], $db['user'], $db['password'], $db['name']);
	q("SET NAMES utf8", $dbweb, __FILE__, __LINE__);
}

if (!isset($dbshop))
{
	$db = array();
	$db['host'] = 'localhost';
	$db['user'] = 'sql-mpsystems';
	$db['password'] = '/2&7hsdkj4dvbT*';
	$db['name'] = 'www-shop';
	$dbshop = mysqli_connect($db['host'], $db['user'], $db['password'], $db['name']);
	q("SET NAMES utf8", $dbshop, __FILE__, __LINE__);
}

