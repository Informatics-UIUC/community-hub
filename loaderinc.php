<?php

function LoadFromWeb($strInURL) {
	$objProxy = new CurlProxy();
	$objProxy->URL = $strInURL;
	$strResponse = $objProxy->Load();

	if (strlen($strResponse) < 1) {
		echo 'Error Connecting to Web Service: ' . $objProxy->CurlErrno . ' ' . $objProxy->CurlError;
		return false;
	}
	
	return $strResponse;
}

function LoadFromFile($strInFilename) {
	$fh = fopen($strInFilename, 'r');
	$strResponse = fread($fh, filesize($strInFilename));
	fclose($fh);
	return $strResponse;
}

function SaveToFile($strInFilename, $strInData) {
		$strFilename = $strInFilename;
		$fh = fopen($strFilename , 'w');
		fwrite($fh, $strInData);
		fclose($fh);
}
?>