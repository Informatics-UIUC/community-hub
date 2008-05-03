<?php

require('classes/clsCurl.php');
require('classes/clsFlowsByTag.php');
require('classes/clsDescribeFlow.php');
require('loaderinc.php');
require('template.php');


$strTagVal = $_GET['Tag'];
if (strlen($strTagVal) < 1) {
	$strTagVal = '';
}

function SearchFlowsByTag() {
	global $strTagVal;
	
	$strWebService = 'http://leo2vm06.ncsa.uiuc.edu:1714/services/repository/flows_by_tag.xml?q=';
	
	//$strResponse = LoadFromFile('cache\flowsbytag.xml');
	$strResponse = LoadFromWeb($strWebService . $strTagVal);
	
	$strResponse = '<?xml version="1.0" ?>' . $strResponse;

	$objFlows = new FlowsByTag();
	$objFlows->Parse($strResponse);

	if (!$objFlows->arrURIs) {
?>
	No flows found matching this tag.
<?php
		return false;
	}
	
	foreach ($objFlows->arrURIs as $strThisURI) {
		$objThisFlow = LoadFlow($strThisURI);
		
?>
  <div style="width: 200px; float: left; text-align: center;"><a href="describeflow.php?URI=<?php echo urlencode($strThisFlowURI); ?>"><img src="images/thumb.gif" border="0"/><br/><?php echo $objThisFlow->strName; ?></a></div>
<?php
	}
}

function LoadFlow($strInURI) {
	//global $strURI, $strName, $strCreator, $strRights, $strDate, $strDescription, $arrTags;
	$strWebService = 'http://leo2vm06.ncsa.uiuc.edu:1714/services/repository/describe_flow.rdf?uri=';
	
	//$strResponse = LoadFromFile('describeflow.rdf');
	$strResponse = LoadFromWeb($strWebService . $strInURI);

	$strResponse = '<?xml version="1.0" ?>' . $strResponse;

	$objFlow = new DescribeFlow();
	$objFlow->Parse($strResponse);
	
	/*
	$strName = $objFlow->strName;
	$strCreator = $objFlow->strCreator;
	$strRights = $objFlow->strRights;
	$strDate = $objFlow->strDate;
	$strDescription = $objFlow->strDescription;
	$arrTags = $objFlow->arrTags;
	*/
	return $objFlow;
}

$strPageTitle = 'Search Flows By Tag [' . $strTagVal . ']';
WriteHead();
?>

<?php require('header.php'); ?>

<form method="get" action="flowsbytag.php">
<strong>Keyword:</strong> <input type="text" name="Tag" value="<?php echo htmlspecialchars($strTagVal); ?>"/><input type="submit" value="Search"/>
</form>

<ul>
<?php SearchFlowsByTag(); ?>
</ul>

<?php require('footer.php'); ?>

<?php WriteFoot(); ?>