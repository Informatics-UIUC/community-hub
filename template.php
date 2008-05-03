<?php function WriteHead() {
global $strPageHead, $strPageTitle;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php echo $strPageHead; ?>
<title><?php echo $strPageTitle; ?> : Meandre</title>
<link rel="stylesheet" type="text/css" href="styles.css"/>
</head>

<body>
<?php

}

function WriteFoot() { ?>
</body>

</html>
<?php } ?>