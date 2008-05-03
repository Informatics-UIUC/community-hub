<?php

/*
****************************************************************************
IMPORTANT NOTE: COMPONENT IS CURRENTLY SPELLED INCORRECTLY IN THE WEBSERVICE RESULTS
****************************************************************************
*/

require('classes/clsCurl.php');
require('classes/clsDescribeFlow.php');
require('loaderinc.php');
require('template.php');

$strTagVal = '';
$strWebService = 'http://leo2vm06.ncsa.uiuc.edu:1714/services/repository/list_flows.xml?q=';

function ListFlows() {
	global $strWebService, $strTagVal;
	//$strResponse = LoadFromFile('listflows.xml');
	$strResponse = LoadFromWeb($strWebService . $strTagVal);

	$strResponse = '<?xml version="1.0" ?>' . $strResponse;

	$objDom = new DOMDocument();
	$objDom->loadXML($strResponse);

	$objFlows = $objDom->getElementsByTagName('meandre_flow_componet');
	foreach ($objFlows as $objThisFlow) {
		$objThisURI = $objThisFlow->getElementsByTagName('meandre_uri')->item(0);
		$strThisURI = $objThisURI->nodeValue;
		
		$objThisFlow = LoadFlow($strThisURI);
		
?>
	<li><a href="describeflow.php?URI=<?php echo urlencode($strThisURI); ?>"><?php echo htmlspecialchars($objThisFlow->strName); ?></a></li>
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

$strPageTitle = 'List Flows [' . $strTagVal . ']';

WriteHead();
?>

<?php require('header.php'); ?>

<ul>
<?php ListFlows(); ?>
</ul>

<?php require('footer.php'); ?>

<?php WriteFoot(); ?>