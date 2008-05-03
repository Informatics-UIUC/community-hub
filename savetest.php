<?php
SaveToCache('dfkjadskfjadfj');

function SaveToCache($strIn) {
		$strFilename = 'cache/' . md5($strIn);
		$fh = fopen($strFilename , 'w');
		fwrite($fh, 'stuff'n);
		fclose($fh);
	}

?>