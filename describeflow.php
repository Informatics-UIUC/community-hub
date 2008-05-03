<?php

require('includes/include.php');
require('classes/clsCurl.php');
require('loaderinc.php');
require('classes/clsDescribeFlow.php');
require('classes/clsFlowsByTag.php');
require('classes/clsDBFlow.php');
require('classes/clsDBUsers.php');
require('classes/clsDBComments.php');
require('template.php');

$strURI = $_GET['URI'];
$strAction = $_GET['Action'];

if (strtoupper($strAction) == 'COMMENT') {
	PostComment();
}

function LoadFlow() {
	global $strURI, $strName, $strCreator, $strRights, $strDate, $strDescription, $arrTags;
	
	$arrFlow = LoadFlowByURI($strURI);
	
	$strName = $arrFlow['?name'];
	$strCreator = $arrFlow['?creator'];
	$strRights = $arrFlow['?rights'];
	$strDate = $arrFlow['?date'];
	$strDescription = $arrFlow['?desc'];

	LoadTags();
	
	//LoadFlowFromDB();
}

function LoadFlow2($strInURI) {
	//global $strURI, $strName, $strCreator, $strRights, $strDate, $strDescription, $arrTags;
	$strWebService = 'http://leo2vm06.ncsa.uiuc.edu:1714/services/repository/describe_flow.rdf?uri=';
	
	//$strResponse = LoadFromFile('describeflow.rdf');
	$strResponse = LoadFromWeb($strWebService . $strInURI);

	$strResponse = '<?xml version="1.0" ?>' . $strResponse;

	$objFlow = new DescribeFlow();
	$objFlow->Parse($strResponse);

	return $objFlow;
}

function LoadFlowFromDB() {
	global $strURI, $intFlowID, $intFlowViews;
	$objFlow = new DBFlows();
	
	$intFlowID = $objFlow->FindID($strURI);
	$objFlow->ID = $intFlowID;
	$objFlow->GetDetails();
	
	$intFlowViews = $objFlow->F_Views;
	
	if (!is_numeric($intFlowViews)) {
		$intFlowViews = 0;
	}
}

// Load Flow Metadata by URI Param, Return as Array of Metadata
function LoadFlowByURI($strInURI) {

	$db = ModelFactory::getDbStore('MySQL', 'localhost', 'meandreportal', 'meandreportal', 'D3M0.2008');

	$modelURI = 'http://leo1vm06.ncsa.uiuc.edu/dump.nt';
	$model = $db->getModel($modelURI);

	$strQ = 'PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
	PREFIX meandre: <http://www.meandre.org/ontology/>
	PREFIX dc: <http://purl.org/dc/elements/1.1/>
	SELECT DISTINCT ?name ?creator ?date ?desc ?rights 
	WHERE { 
		<' . $strInURI . '> ?p ?o . 
	 <' . $strInURI . '> rdf:type meandre:flow_component . 
	<' . $strInURI . '> meandre:name ?name .
	<' . $strInURI . '> dc:creator ?creator .
	<' . $strInURI . '> dc:date ?date . 
	<' . $strInURI . '> dc:description ?desc . 
	<' . $strInURI . '> dc:rights ?rights 
	}';

	$result = $model->sparqlQuery($strQ);

	// Result Found
	if (is_array($result)) {
		foreach ($result[0] as $strThisTag => $objThisVal) {
			$arrTemp[$strThisTag] = $objThisVal->getLabel();
		}
		return $arrTemp;
	}

}

function LoadTags() {
	global $result;

	$db = ModelFactory::getDbStore('MySQL', DBServer, DBName, DBUser, DBPassword);

	$modelURI = 'http://leo1vm06.ncsa.uiuc.edu/dump.nt';
	$model = $db->getModel($modelURI);

	$strQ = 'prefix meandre:  <http://www.meandre.org/ontology/> 
	prefix xsd:     <http://www.w3.org/2001/XMLSchema#> 
	prefix dc:      <http://purl.org/dc/elements/1.1/> 
	prefix rdfs:    <http://www.w3.org/2000/01/rdf-schema#> 
	prefix rdf:     <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
	select ?uri ?tag
	where {
		?uri rdf:type meandre:flow_component .
	    ?uri meandre:tag ?tag
	}';

	$result = $model->sparqlQuery($strQ);
}

function ListTags() {
	global $strURI;
	$arrTags = GetTagsByFlow($strURI);
	for ($intX = 0; $intX < sizeof($arrTags); $intX++) {
		$strThisTag = $arrTags[$intX];
?><a href="tags.php?Tags[]=<?php echo urlencode($strThisTag); ?>"><?php echo htmlspecialchars($strThisTag); ?></a><?php
		if ($intX + 1 < sizeof($arrTags)) {
			echo ', ';
		}
	}
}

function SearchFlowsByTags() {
	global $strURI;
	$arrTags = GetTagsByFlow($strURI);
	$arrFlowWeights = array();
	foreach ($arrTags as $strThisTag) {
		foreach (GetFlowsByTag($strThisTag) as $strThisFlowURI) {
			if ($strThisFlowURI == $strURI) {
				continue;
			}
			if (array_key_exists($strThisFlowURI, $arrFlowWeights)) {
				$arrFlowWeights[$strThisFlowURI]++;
				continue;
			}
			
			$arrFlowWeights[$strThisFlowURI] = 1;
			$arrFlows[$strThisFlowURI] = LoadFlowByURI($strThisFlowURI);
		}
	}
	
	arsort($arrFlowWeights);
	foreach($arrFlowWeights as $strThisURI => $intThisWeight) {
		$arrThisFlow = $arrFlows[$strThisURI];
?>
	<div style="width: 200px; float: left; text-align: center;"><a href="describeflow.php?URI=<?php echo urlencode($strThisURI); ?>"><img src="images/icon.gif" border="0"/><br/><?php echo $arrThisFlow['?name']; ?></a></div>
<?php
	}	
}

// Find All Tags by URI Param, Return as Array of Tags
function GetTagsByFlow($strInURI) {
	global $result;
	
	foreach ($result as $arrThisRow) {
		$strThisURI = $arrThisRow['?uri']->getLabel();
		$strThisTag = $arrThisRow['?tag']->getLabel();
		
		if (strcasecmp($strThisURI, $strInURI) == 0) {
			$arrTags[] = $strThisTag;
		}
	}
	return $arrTags;
}

// Find All Flows Containing Tag Param, Return as Array of Flow URIs
function GetFlowsByTag($strInTag) {
	global $result;
	
	foreach ($result as $arrThisRow) {
		$strThisURI = $arrThisRow['?uri']->getLabel();
		$strThisTag = $arrThisRow['?tag']->getLabel();
		
		if (strcasecmp($strThisTag, $strInTag) == 0) {
			$arrFlows[] = $strThisURI;
		}
	}
	return $arrFlows;
}

function PostComment() {
	global $strURI, $intFlowID, $intRatingVal, $strNameVal, $strEmailVal, $strCommentVal, $blnErr;
	$intFlowID = $_POST['FlowID'];
	$intRatingVal = $_POST['Rating'];
	$strNameVal = $_POST['Name'];
	$strEmailVal = $_POST['Email'];
	$strCommentVal = $_POST['Comment'];
	
	if (empty($strNameVal) or empty($strEmailVal) or IsValidEmail($strEmailVal) == false or empty($strCommentVal)) {
		$blnErr = true;
		return false;
	}

	$intUserID = FindCreateUser();
	
	if (!is_numeric($intFlowID) or $intFlowID == 0) {
		$intFlowID = CreateFlowID();
	}
	
	$objComments = new DBComments();
	$objComments->C_FlowID = $intFlowID;
	$objComments->C_UserID = $intUserID;
	$objComments->C_Comment = $strCommentVal;
	$objComments->Insert();
	
	header('location: describeflow.php?URI=' . urlencode($strURI));
}

function FindCreateUser() {
	global $strNameVal, $strEmailVal;
	$objUsers = new DBUsers();
	$intUserID = $objUsers->FindUserID($strEmailVal);
	
	if ($intUserID == 0) {
		$objUsers->U_Name = $strNameVal;
		$objUsers->U_Email = $strEmailVal;
		$intUserID = $objUsers->Insert();
	}
	
	return $intUserID;
}

function CreateFlowID() {
	global $strURI;
	
	$objDBFlow = new DBFlows();
	$objDBFlow->F_URI = $strURI;
	$intFlowID = $objDBFlow->Insert();
	
	return $intFlowID;
}

function ListComments() {
	global $intFlowID;
	
	if ($intFlowID == 0) {
		return false;
	}
	
	$objComments = new DBComments();
	$objComments->GetListByFlowID($intFlowID);
	
	$objPager = new RecordPager($objComments->objResult);
	
	if ($objPager->intRowCount == 0) {
		return false;
	}
	
	$blnAltRow = false;
	
	while ($arrThisRow = $objPager->GetRow()) {
?>
<p>
<?php echo htmlspecialchars($arrThisRow['U_Name']);?><br/>
<?php echo htmlspecialchars($arrThisRow['C_Comment']); ?>
</p>
<?php
		if (!$blnAltRow) { $blnAltRow = true; } else { $blnAltRow = false; }
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
<div style="width: 45%; float: left; text-align: center; margin-top: 30px;"><form method="get" action="tags.php"><strong>Find a Flow:</strong> <input type="text" name="Tags[]"/><input type="submit" value="Go"/></form></div>
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
        <p><strong><?php echo $strName; ?></strong><br/>
        <?php echo $strCreator; ?></p>
        
        <p><?php echo $strDescription; ?></p>
        
        <p>
        <strong>Created On:</strong> <?php echo date('F j, Y', strtotime($strDate)); ?><br/>
		<strong>Views:</strong> <?php echo $intFlowViews; ?>
        </p>
        <p><strong>Keywords:</strong> <?php ListTags(); ?></p>
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

<p><strong>Related Flows</strong></p>

<?php SearchFlowsByTags(); ?>

<div style="border-bottom: solid 8px #CDCDCD; width: 100%; height: 1px; margin-bottom: 30px; margin-top: 30px;">&nbsp;</div>

<p><strong>Comments</strong></p>

<?php ListComments(); ?>

<p>
<form method="post" action="describeflow.php?URI=<?php echo urlencode($strURI); ?>&Action=Comment">
<input type="hidden" name="FlowID" value="<?php echo $intFlowID; ?>"/>
<table>
  <tr>
    <td><strong>Rating:</strong></td>
	<td></td>
  </tr>
  <tr>
    <td><label for="Name"><strong>Name:</strong></label></td>
	<td><input type="text" name="Name" id="Name" value="<?php echo htmlspecialchars($strNameVal); ?>" maxlength="50"/></td>
  </tr>
  <tr>
    <td><label for="Email"><strong>Email:</strong></label></td>
	<td><input type="text" name="Email" id="Email" value="<?php echo htmlspecialchars($strEmailVal); ?>" maxlength="50"/></td>
  </tr>
  <tr>
    <td><label for="Comment"><strong>Comment:</strong></label></td>
	<td><textarea name="Comment" id="Comment" cols="30" rows="3"><?php echo htmlspecialchars($strCommentVal); ?></textarea></td>
  </tr>
  <tr>
    <td colspan="2" align="right"><input type="submit" value="Post"/></td>
  </tr>
</table>
</form>
</p>


<?php require('footer.php'); ?>

<?php WriteHead(); ?>