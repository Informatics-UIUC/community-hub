<?php

require('includes/include.php');
require('classes/clsCurl.php');
require('loaderinc.php');
require('classes/clsDescribeFlow.php');
require('classes/clsFlowsByTag.php');
require('template.php');

$strURI = $_GET['URI'];

function ListFlows() {

$db = ModelFactory::getDbStore('MySQL', 'localhost', 'meandreportal', 'meandreportal', 'D3M0.2008');

$modelURI = 'http://leo1vm06.ncsa.uiuc.edu/dump.nt';
$model = $db->getModel($modelURI);

$strQ = 'PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX meandre: <http://www.meandre.org/ontology/>
PREFIX dc: <http://purl.org/dc/elements/1.1/>
SELECT DISTINCT ?flow ?instances ?connectors
WHERE { 
	?flow ?p ?o . 
?flow rdf:type meandre:flow_component . 
?flow meandre:components_instances ?instances .
?flow meandre:connectors ?connectors
}';

$result = $model->sparqlQuery($strQ);
echo $model->sparqlQuery($strQ, 'HTML');
// Loop Through all Flows

foreach ($result as $thisrow) { ?>
  <li><a href="instances.php?URI=<?php echo $thisrow['?flow']->getLabel(); ?>"><?php echo $thisrow['?flow']->getLabel(); ?></a></li>
<?php }

}

// Load Flow Metadata by URI Param, Return as Array of Metadata
function LoadFlowByURI($strInURI) {

	$db = ModelFactory::getDbStore('MySQL', 'localhost', 'meandreportal', 'meandreportal', 'D3M0.2008');

	$modelURI = 'http://leo1vm06.ncsa.uiuc.edu/dump.nt';
	$model = $db->getModel($modelURI);

	$strQ = 'PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
	PREFIX meandre: <http://www.meandre.org/ontology/>
	PREFIX dc: <http://purl.org/dc/elements/1.1/>
	SELECT DISTINCT ?instances
	WHERE { 
		<' . $strInURI . '> ?p ?o . 
	 <' . $strInURI . '> rdf:type meandre:flow_component . 
	<' . $strInURI . '> meandre:components_instances ?instances
	}';

	$result = $model->sparqlQuery($strQ);

	foreach ($result as $thisrow) {
		$objInstance = $thisrow['?instances'];
		if ($objInstance) {
			$strThisInstance = $objInstance->getLabel();
?>
<li><?php echo $strThisInstance; ?></li>
<ul>
<?php
			FindInstances($strThisInstance);
?>
</ul>
<?php
		}
		
	}

}

function FindInstances($strInURI) {

$db = ModelFactory::getDbStore('MySQL', 'localhost', 'meandreportal', 'meandreportal', 'D3M0.2008');

$modelURI = 'http://leo1vm06.ncsa.uiuc.edu/dump.nt';
$model = $db->getModel($modelURI);

$strQ = 'PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX meandre: <http://www.meandre.org/ontology/>
PREFIX dc: <http://purl.org/dc/elements/1.1/>
SELECT DISTINCT ?instances
WHERE { 
	<' . $strInURI . '> ?p ?o . 
<' . $strInURI . '> rdf:type meandre:instance_set . 
<' . $strInURI . '> meandre:executable_component_instance ?instances
}';

$result = $model->sparqlQuery($strQ);

// Loop Through all Instances
foreach ($result as $thisrow) {
	$strThisInstance = $thisrow['?instances']->getLabel();
?>
<li><?php echo $strThisInstance; ?></li>
<ul>
<?php
	LoadInstance($strThisInstance);
?>
</ul>
<?php
}

}

function LoadInstance($strInURI) {
$db = ModelFactory::getDbStore('MySQL', 'localhost', 'meandreportal', 'meandreportal', 'D3M0.2008');

$modelURI = 'http://leo1vm06.ncsa.uiuc.edu/dump.nt';
$model = $db->getModel($modelURI);

$strQ = 'PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX meandre: <http://www.meandre.org/ontology/>
PREFIX dc: <http://purl.org/dc/elements/1.1/>
SELECT DISTINCT ?desc ?name ?resource ?property_set
WHERE { 
	<' . $strInURI . '> ?p ?o . 
<' . $strInURI . '> rdf:type meandre:instance_configuration . 
<' . $strInURI . '> dc:description ?desc .
<' . $strInURI . '> meandre:instance_name ?name .
<' . $strInURI . '> meandre:instance_resource ?resource .
<' . $strInURI . '> meandre:property_set ?property_set
}';

$result = $model->sparqlQuery($strQ);

for ($intX = 0; $intX < sizeof($result); $intX++) {
	$thisrow = $result[$intX];
	if ($intX == 0) {
		if ($thisrow['?name'])
			$strThisName = $thisrow['?name']->getLabel();
		if ($thisrow['?desc'])
			$strThisDesc = $thisrow['?desc']->getLabel();
			
?>
<li><?php echo $strThisName . '<br>' . $strThisDesc; ?></li>
<?php
	}
?>
<ul>
<?php
	LoadProperty($thisrow['?property_set']->getLabel());
?>
</ul>
<?php
}
}

function LoadProperty($strInURI) {
$db = ModelFactory::getDbStore('MySQL', 'localhost', 'meandreportal', 'meandreportal', 'D3M0.2008');

$modelURI = 'http://leo1vm06.ncsa.uiuc.edu/dump.nt';
$model = $db->getModel($modelURI);

$strQ = 'PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX meandre: <http://www.meandre.org/ontology/>
PREFIX dc: <http://purl.org/dc/elements/1.1/>
SELECT DISTINCT ?key ?value
WHERE { 
	<' . $strInURI . '> ?p ?o . 
<' . $strInURI . '> rdf:type meandre:property . 
<' . $strInURI . '> meandre:key ?key .
<' . $strInURI . '> meandre:value ?value
}';

$result = $model->sparqlQuery($strQ);

foreach ($result as $thisrow) {
	if ($thisrow['?key'])
		$strThisKey = $thisrow['?key']->getLabel();
	if ($thisrow['?value'])
		$strThisVal = $thisrow['?value']->getLabel();
?>
<li><?php echo $strThisKey . ' = ' . $strThisVal; ?></li>
<?php
}

}

$strPageTitle = '';

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

<ul>
<?php ListFlows(); ?>
</ul>

<ul>
<?php if (strlen($strURI) > 0 ) { LoadFlowByURI($strURI); } ?>
</ul>

<?php require('footer.php'); ?>

<?php WriteHead(); ?>