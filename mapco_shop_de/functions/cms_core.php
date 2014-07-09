<?php
	//************************
	//*     CMS CORE FUNCTIONS
	//************************

/**
 *	Amazon Submit
 *
 */
function amazonSubmit($post, $host = null)
{
	$APP_USERAGENT = "Mapco API - Amazon/2.0 (Language=PHP/" . phpversion() . "; Platform=Debian 3.2.54-2 x86_64)";

	if ($host != null) {
		$host = $host;
	} else {
		$host = "fix the call"; // add the $MARKETPLACE_HOST on the right place
	}

	if (!empty($post['method'])) {
		$method= $post['method'];
	} else {
		$method = "POST";
	}

	// for a special type define /folder/
	if (!empty($post['type'])) {
		$uri = "/" . $post['type'];
	} else {
		$uri = "/";
	}

	// Clean up and sort
	$url = explode('&', $post['url']);

	foreach ($url as $key => $value)
	{
		$t = explode("=",$value);
		$params[$t[0]] = $t[1];
	}
	unset($url);

	ksort($params);

	foreach ($params as $param=>$value)
	{
		$param = str_replace("%7E", "~", rawurlencode($param));
		$value = str_replace("%7E", "~", rawurlencode($value));
		$canonicalized_query[] = $param . "=" . $value;
	}

	$canonicalized_query = implode("&", $canonicalized_query);

	// create the string to sign
	$string_to_sign = $method . "\n" . $host . "\n" . $uri . "\n" . $canonicalized_query;

	// calculate HMAC with SHA256 and base64-encoding
	$signature = base64_encode(hash_hmac("sha256", $string_to_sign, $post['SecretKey'], true));

	// encode the signature for the request
	$signature = str_replace("%7E", "~", rawurlencode($signature));

	// create request
	$requestUrl = "https://" . $host . $uri . "?" . $canonicalized_query . "&Signature=" . $signature;
	$ch = curl_init();

	//	create an acceptable User-Agent header
	curl_setopt($ch, CURLOPT_USERAGENT, $APP_USERAGENT);

	if (!empty($post['data']))
	{
		$feedHandle = fopen('php://temp', 'w');
		fwrite($feedHandle, $post['data']);
		rewind($feedHandle);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml; charset=iso-8859-1", "Content-MD5:".base64_encode(md5(stream_get_contents($feedHandle), true)) ));
		rewind($feedHandle);
		curl_setopt($ch, CURLOPT_POSTFIELDS, stream_get_contents($feedHandle));
	} else {
		curl_setopt($ch, CURLOPT_USERAGENT, $APP_USERAGENT);
	}

	curl_setopt($ch, CURLOPT_URL, $requestUrl);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	curl_close($ch);

	//	if empty response, returns a xml string
	//	or returns a clean response only
	if ($response == null)
	{
		return '<Error>no response after submit</Error>';
	} else {
		return $response;
	}
}

/**
 * Debug view
 *
 * @param $dump
 * @param bool $exit
 */
function pr($dump, $exit = false)
{
	echo '<pre>';
		var_dump($dump);
		if ($exit == true)
		exit;
	echo '</pre>' . "\n";
}

/**
 * Returns a html icon string
 *
 * @param $ico
 * @return string
 */
function getIcon($ico)
{
	return '<i class="fa '. $ico . '"></i>';
}

/**
 * Returns $_POST data by value
 *
 * @param $value
 * @return mixed
 */
function getPost($value)
{
	if (isset($_POST[$value]) && $_POST[$value] != null) {
		return $_POST[$value];
	}
	return false;
}

/**
 * Returns a session user id
 *
 * @return mixed
 */
function getSessionUserId()
{
	if (isset($_SESSION["id_user"]) && $_SESSION["id_user"] != null) {
		return $_SESSION["id_user"];
	}
}

/**
 * Returns an username
 *
 * @param $user
 * @return string
 */
function getUserName($user)
{
	if($user != null) {
		return ' von ' . $user['name'];
	}
}

/**
 * Returns a duration by start and end time
 *
 * @param $data
 * @return string
 */
function getWorktimeResult($data)
{
	$duration = getDuration($data);

	if ($duration < 30) {
		$class = 'good';
	} elseif($duration < 120) {
		$class='neutral';
	} else {
		$class='bad';
	}
	return '<td class="center ' . $class . '">' . covertSecondTo($duration) . '</td>';
}

/**
 * @param $data
 * @return date
 */
function getDuration($data)
{
	($data["EndTime"] == 0) ? $endtime = getTime() : $endtime = $data["EndTime"];
	return ($endtime - $data["StartTime"]);
}

/**
 * search and replace into a string
 *
 * @param $string
 * @return mixed|string
 */
function getStrIreplace($string)
{
	return str_ireplace("]]>", "]/]>", $string);
}

/**
 * @param $status
 * @return string
 */
function getActiveStatus($status)
{
	($status == 1) ? $status = '<span class="msg-success">Aktive</span>' : $status = '<span class="msg-error">Inaktive</span>';
	return $status;
}

/**
 *
 * @param $manual
 * @return string
 */
function getManuelStatus($manual)
{
	($manual == 1) ? $html = '<span class="msg-success">Yes</span>' : $html = '<span class="msg-error">No</span>';
	return $html;
}

/**
 *
 * @param $manual
 * @return string
 */
function getUploadStatus($upload)
{
	($upload == 0) ? $upload = '<span class="msg-success">Yes</span>' : $upload = '<span class="msg-error">No</span>';
	return $upload;
}

/**
 * Returns a import status
 * @param $manual
 * @return string
 */
function getImportStatus($value)
{
	($value > 0) ? $value = '<span class="label label-success">Yes</span>' : $value = '<span class="label label-danger">No</span>';
	return $value;
}

/**
 *------------------------------------------------ Date, time--------------------------------------------------------------
**/

/**
 * Returns date timestamp
 *
 * @return date integer
 */
function getTime()
{
	return time();
}

/**
 * Returns a date
 *
 * @param $date
 * @return bool|string
 */
function getDateTime($date)
{
	if ($date != null) {
		return '<span>' . date("d.m.Y", $date) . '</span>' . date("H:i:s", $date);
	}
}

/**
 * @param $data
 * @return string
 */
function covertSecondTo($data)
{
	return gmdate("H:i:s", $data) . 's';
}

/**
 * @param $data
 * @return string
 */
function stringToTime($string)
{
	$string = strtotime($string);
	return '<span>' . date("d.m.Y", $string) . '</span>' . date("H:i:s", $string);
}

/**
 * @param $date
 * @return bool|string
 */
function getDateToday($date)
{
	if ($date != 0) {
		$checkTime1 = date("m y", $date);
		$checkTime2 = date("m y", getTime());
		$lastlon1 = date("d m y", getTime());
		$lastlon2 = date("d m y", $date);
		$yday1 = (date("d", getTime()) - 1);
		$yday2 = date("d", $date);
		if ($lastlon1 == $lastlon2) {
			$html = '<span><strong>Heute</strong></span>' . date("H:i", $date);
		} elseif ($yday2 == $yday1 AND $checkTime1 == $checkTime2) {
			$html = '<span><strong>Gestern</strong></span>' . date("H:i", $date);
		} else {
			$html = getDateTime($date);
		}
	} else {
		$html = '<span>noch keins</span>';
	}
	return $html;
}

/**
 * Returns a short day name in german
 * (Date out only weekdays)
 *
 * @param integer $time
 * @return string
 */
function DateOutDay($time)
{
	if (!empty($time)) {
		$tag = date(D, $time);
		$wochentage_array = array(
			"Mon" => "Mo",
			"Tue" => "Di",
			"Wed" => "Mi",
			"Thu" => "Do",
			"Fri" => "Fr",
			"Sat" => "Sa",
			"Sun" => "So"
		);
		$wochentag = $wochentage_array["$tag"];
		return $wochentag . ', ' . date("d.m.y", $time);
	}
}
