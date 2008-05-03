<?php

require('includes/include.php');
require('template.php');

require('classes/clsSparqlRS.php');

LoadTags();

$arrTagsVal = $_GET['Tags'];
if (AreTagsSelected() == true) {
	ParseTagsVal();
}
if (AreTagsSelected() == true) {
	LoadTagWeightsFiltered();
}
else {
	LoadTagWeights();
}

function AreTagsSelected() {
	global $arrTagsVal;
	if (!empty($arrTagsVal)) {
		return true;
	}
	else {
		return false;
	}
}


// Loop Selected Tags from Querystring and Remove Nulls
function ParseTagsVal() {
	global $arrTagsVal;
	foreach($arrTagsVal as $strThisKey => $strThisTag) {
		if (empty($strThisTag) or strlen($strThisTag) < 1) {
			unset($arrTagsVal[$strThisKey]);
		}
	}
}

// Load all Flow Tags into Array, Keep Duplicates to Assign Weights
function LoadTags() {
	global $objTagsRS;

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
	$objTagsRS = new SparqlRecordSet($result);
}


// Loop Through Flow Tags Array, Assign Weights Based on Occurances
function LoadTagWeights() {
	global $objTagsRS, $arrTagWeights;
	
	$arrTagWeights = array();
	while ($arrThisRow = $objTagsRS->getRow()) {
		$strThisTag = $arrThisRow['?tag'];
		if (array_key_exists($strThisTag, $arrTagWeights)) {
			$arrTagWeights[$strThisTag]++;
		}
		else {
			$arrTagWeights[$strThisTag] = 1;
		}
	}
}


// 
function LoadTagWeightsFiltered() {
	global $arrTagsVal, $arrTagWeights;
	
	$arrTags = array();
	$arrTagWeights = array();
	$arrFlowURIs = array();
	
	// Loop Through Selected Tags
	foreach ($arrTagsVal as $strThisKey => $strThisTag) {
		
		// Find Flows that Match Each Tag
		foreach (GetFlowsByTag($strThisTag) as $strThisFlowURI) {
			// Ignore Duplicate Flows
			if (in_array($strThisFlowURI, $arrFlowURIs)) {
				continue;
			}
			
			// Find Tags By Each Flow and Filter Flows that Dont Have ALL the Selected Tags
			$arrThisFlowTags = GetTagsByFlow($strThisFlowURI);
			foreach ($arrTagsVal as $strThisTag) {
				if (!in_array($strThisTag, $arrThisFlowTags)) {
					continue 2;
				}
			}
			
			// Flow Processed, Add to List of Flows
			$arrFlowURIs[] = $strThisFlowURI;
			
			// Loop Through Processed Flows Tags to Increment Tag Weights
			foreach ($arrThisFlowTags as $strThisFlowTag) {
				// Tag not Counted, Set Count at 1
				if (!in_array($strThisFlowTag, $arrTags)) {
					$arrTags[] = $strThisFlowTag;
					$arrTagWeights[$strThisFlowTag] = 1;
				}
				// Increment Tag Count
				else {
					$arrTagWeights[$strThisFlowTag]++;
				}
			}			
		}
	}
}


// Write Tag Cloud
function ListTags() {
	global $arrTagWeights, $arrTagsVal;
	
	// Sizes are Font-Size Percentages
	$intMinSize = 100;
	$intMaxSize = 250;
	
	// Find Largest and Smallest Tag Weights
	$intMaxCount = max(array_values($arrTagWeights));
	$intMinCount = min(array_values($arrTagWeights));
	
	// Calculate Weight Spread and Use to Normalize Font-Size Increments
	$intSpread = $intMaxCount - $intMinCount;
	if ($intSpread == 0) {
		$intSpread = 1;
	}
	
	$intStep = ($intMaxSize - $intMinSize) / $intSpread;
	
	// Serialize Selected Tag String for Appending in Tag Link
	if (is_array($arrTagsVal)) {
		$strTagsSerial = implode('&Tags[]=', $arrTagsVal);
	}
	
	// Loop Through Tag Weights Array to Write Tag Cloud
	foreach ($arrTagWeights as $strThisTag => $intThisCount) {
		// Ignore Outputting Already Selected Tags
		if (is_array($arrTagsVal)) {
			if (in_array($strThisTag, $arrTagsVal)) {
				continue;
			}
		}
		
		// Determine this Font-Size % According to Normalized Increments
		$intSize = $intMinSize + (($intThisCount - $intMinCount) * $intStep);
		
		// Ignore Orphaned Tags, May Not be an Issue
		if ($intThisCount > 0) {
?>
	<li><a href="tags.php?Tags[]=<?php echo $strTagsSerial; ?>&Tags[]=<?php echo urlencode($strThisTag); ?>" style="font-size: <?php echo $intSize; ?>%;"><?php echo htmlspecialchars($strThisTag); ?></a></li>
<?php
		}
	}
}

// Write Selected Tags to Page, Allow for Easy Removal of Selected Tags Filter
function ListSelectedTags() {
	global $arrTagsVal;

	foreach($arrTagsVal as $strThisKey => $strThisTag) {
	
		// Generate Tag Serial to Make Link to Remove Selected Tag from Filter
		$arrTemp = $arrTagsVal;
		unset($arrTemp[array_search($strThisTag, $arrTemp)]);
		$strTagsSerial = implode('&Tags[]=', $arrTemp);
		
?>
<?php echo htmlspecialchars($strThisTag); ?><a href="tags.php?Tags[]=<?php echo $strTagsSerial; ?>">[x]</a>
<?php
	}
}


// Write Flows Matching All Selected Tags
function SearchFlowsByTags() {
	global $arrTagsVal;
	static $arrFlowURIs = array();
	
	// Loop through selected tags
	foreach ($arrTagsVal as $strThisKey => $strThisTag) {
	
		// Find flows with this selected tag
		foreach (GetFlowsByTag($strThisTag) as $strThisFlowURI) {
		
			// Skip if flow is already in this index
			if (in_array($strThisFlowURI, $arrFlowURIs)) {
				continue;
			}
			
			// Make sure this flow matches all selected tags
			$arrThisFlowTags = GetTagsByFlow($strThisFlowURI);
			foreach ($arrTagsVal as $strThisTag) {
				if (!in_array($strThisTag, $arrThisFlowTags)) {
					continue 2;
				}
			}
			
			// Add flow to index
			$arrFlowURIs[] = $strThisFlowURI;
			$arrThisFlow = LoadFlowByURI($strThisFlowURI);
			
?>
	<div style="width: 200px; float: left; text-align: center;"><a href="describeflow.php?URI=<?php echo urlencode($strThisFlowURI); ?>"><img src="images/icon.gif" border="0"/><br/><?php echo $arrThisFlow['?name']; ?></a></div>
<?php
		}
	}
}

// Find All Flows Containing Tag Param, Return as Array of Flow URIs
function GetFlowsByTag($strInTag) {
	global $objTagsRS;
	
	$objTagsRS->MoveFirst();
	while ($arrThisRow = $objTagsRS->getRow()) {
		$strThisURI = $arrThisRow['?uri'];
		$strThisTag = $arrThisRow['?tag'];
		
		if (strcasecmp($strThisTag, $strInTag) == 0) {
			$arrFlows[] = $strThisURI;
		}
	}
	return $arrFlows;
}

// Find All Tags by URI Param, Return as Array of Tags
function GetTagsByFlow($strInURI) {
	global $objTagsRS;
	
	$objTagsRS->MoveFirst();
	while ($arrThisRow = $objTagsRS->getRow()) {
		$strThisURI = $arrThisRow['?uri'];
		$strThisTag = $arrThisRow['?tag'];
		
		if (strcasecmp($strThisURI, $strInURI) == 0) {
			$arrTags[] = $strThisTag;
		}
	}
	return $arrTags;
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
	
	$objFlowsRS = new SparqlRecordSet($result);

	// Result Found
	if ($objFlowsRS->RowCount() != 0) {
		return $objFlowsRS->getRow();
	}
}

$strPageTitle = 'Keyword Cloud';

WriteHead();
?>

<?php require('header.php'); ?>

<p align="right">
<form method="get" action="tags.php">
<table cellspacing="0" cellpadding="0" border="0">
  <tr>
    <td colspan="2" align="left"><strong>New Search:</strong></td>
  </tr>
  <tr>
    <td><input type="text" name="Tags[]"/></td>
    <td><input type="submit" value="Go"/></td>
  </tr>
</table>
</form>
</p>

<?php if (AreTagsSelected() == true) { ?>

<h2>Related Keywords</h2>

<?php } else { ?>

<h2>Keywords</h2>

<?php } ?>

<div id="inline-list">
<ul>
<?php ListTags(); ?>
</ul>
</div>

<?php if (sizeof($arrTagsVal) > 0) { ?>
<p><strong>Keywords:</strong> <?php ListSelectedTags(); ?></p>

<div style="width: 100%; height: 8px; background-color: #CDCDCD; margin-bottom: 30px;">&nbsp;</div>

<?php SearchFlowsByTags(); ?>

<div style="clear: both; height: 1px;">&nbsp;</div>

<?php } ?>

<?php require('footer.php'); ?>

<?php WriteFoot(); ?>
