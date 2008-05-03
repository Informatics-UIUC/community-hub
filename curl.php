<?php

$strURLVal = $_GET['URL'];
$strAuthVal = $_GET['Auth'];

$ch = curl_init();   
curl_setopt($ch, CURLOPT_URL, $strURLVal);

if (strtoupper($strAuthVal) != 'FALSE') {
	curl_setopt($ch, CURLOPT_USERPWD, 'admin:admin');
}

$strTemp = curl_exec($ch);
curl_close($ch);

echo $strTemp;

?>