<?php

require('classes/clsCurl.php');
require('loaderinc.php');
require('classes/clsDescribeFlow.php');
require('template.php');

$strURI = 'http://dita.ncsa.uiuc.edu/meandre/e2k/flows/flow/simple-ga';



function LoadFlow() {
	global $strURI, $strName, $strCreator, $strRights, $strDate, $strDescription, $arrTags;
	$strWebService = 'http://leo2vm06.ncsa.uiuc.edu:1714/services/repository/describe_flow.rdf?uri=';
	
	//$strResponse = LoadFromFile('cache/describeflow.rdf');
	$strResponse = LoadFromWeb($strWebService . $strURI);

	$strResponse = '<?xml version="1.0" ?>' . $strResponse;

	$objFlow = new DescribeFlow();
	$objFlow->Parse($strResponse);
	
	$strName = $objFlow->strName;
	$strCreator = $objFlow->strCreator;
	$strRights = $objFlow->strRights;
	$strDate = $objFlow->strDate;
	$strDescription = $objFlow->strDescription;
	$arrTags = $objFlow->arrTags;
	
}

function ListTags() {
	global $arrTags;
	
	for ($intX = 0; $intX < sizeof($arrTags); $intX++) {
		$strThisTag = $arrTags[$intX];
?><a href="tags.php?Tags[]=<?php echo urlencode($strThisTag); ?>"><?php echo htmlspecialchars($strThisTag); ?></a><?php
		if ($intX + 1 < sizeof($arrTags)) {
			echo ', ';
		}
	}
}
LoadFlow();

$strPageTitle = 'Describe Flow: ' . htmlspecialchars($strName);

//TEMPORARILY HARD-CODING IN AN IMAGE UNTIL WE HAVE THAT CAPABILITY BUILT INTO THE SYSTEM
$strImage = 'images/fakeflow.jpg';

WriteHead();
?>

<?php require('header.php'); ?>

<div style="width: 100%;">
<div style="width: 55%; float: left;"><img src="images/leftbracket.gif"><a href="tags.php"><img src="images/keywordcloud.gif" alt="Keyword Cloud" border="0"></a><img src="images/bubblestats.gif"><img src="images/connections.gif"><img src="images/statgenerator.gif"><img src="images/rightbracket.gif"></div>
<div style="width: 45%; float: left; text-align: center; margin-top: 30px;"><form method="get" action="tag.php"><strong>Find a Flow:</strong> <input type="text" name="Tags[]"/><input type="submit" value="Go"/></form></div>
<div style="clear:both;">&nbsp;</div>
</div>

<table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f5f5f5; margin-top: 15px; margin-bottom: 25px;">
<tr>
	<td colspan="2" style="border-top: solid 8px #CDCDCD; width: 100%; height: 30px;">
    &nbsp;
    </td>
</tr>
<tr>
	<td width="80%" style="padding-left: 10px; padding-right: 10px;">
    <div ><img src="<? echo $strImage ?>" style="border: 1px solid gray"/>
    </div>
    </td>
    <td width="20%" style="padding-right: 10px; vertical-align: top;">
    <div id="featured_txt">
        <div style="color: #a2a2a2; font-weight: bold;">FEATURED FLOW:</div>
        <p><strong><?php echo $strName; ?></strong><br/>
        <?php echo $strCreator; ?></p>
        
        <p><?php echo $strDescription; ?></p>
        
        <p>
        Created On: <?php echo date('F j, Y', strtotime($strDate)); ?>
        </p>
        <p>Keywords: <?php ListTags(); ?></p>
        <br/><br/><br/><br/>
        <em>Rights: <?php echo $strRights; ?></em>
        </p>
        
        	
	</div>
    </td>
</tr>
<tr>
	<td colspan="2"  style="border-bottom: solid 8px #CDCDCD; width: 100%; height: 30px;">
    &nbsp;
    </td>
</tr>
</table>

<?php require('footer.php'); ?>

<?php WriteHead(); ?>