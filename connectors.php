<?php

require('includes/include.php');
require('classes/clsCurl.php');
require('loaderinc.php');
require('classes/clsDescribeFlow.php');
require('classes/clsFlowsByTag.php');
require('template.php');

require('classes/clsSparqlRS.php');

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
$objFlowsRS = new SparqlRecordset($result);

// Loop Through all Flows
while ($arrThisRow = $objFlowsRS->getRow()) { ?>
  <li><a href="connectors.php?URI=<?php echo $arrThisRow['?flow']; ?>"><?php echo $arrThisRow['?flow']; ?></a></li>
<?php }

}

function LoadFlowByURI($strInURI) {

	$db = ModelFactory::getDbStore('MySQL', 'localhost', 'meandreportal', 'meandreportal', 'D3M0.2008');

	$modelURI = 'http://leo1vm06.ncsa.uiuc.edu/dump.nt';
	$model = $db->getModel($modelURI);

	$strQ = 'PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
	PREFIX meandre: <http://www.meandre.org/ontology/>
	PREFIX dc: <http://purl.org/dc/elements/1.1/>
	SELECT DISTINCT ?connectors
	WHERE { 
		<' . $strInURI . '> ?p ?o . 
	 <' . $strInURI . '> rdf:type meandre:flow_component . 
	<' . $strInURI . '> meandre:connectors ?connectors
	}';

	$result = $model->sparqlQuery($strQ);
	$objConnectorsRS = new SparqlRecordSet($result);
	
	while ($arrThisRow = $objConnectorsRS->getRow()) {
		$strThisConnector = $arrThisRow['?connectors'];
?>
<li><?php echo $strThisConnector; ?></li>
<ul>
<?php
			FindConnectors($strThisConnector);
?>
</ul>
<?php
		}
		
}

function FindConnectors($strInURI) {

$db = ModelFactory::getDbStore('MySQL', 'localhost', 'meandreportal', 'meandreportal', 'D3M0.2008');

$modelURI = 'http://leo1vm06.ncsa.uiuc.edu/dump.nt';
$model = $db->getModel($modelURI);

$strQ = 'PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX meandre: <http://www.meandre.org/ontology/>
PREFIX dc: <http://purl.org/dc/elements/1.1/>
SELECT DISTINCT ?connectors
WHERE { 
	<' . $strInURI . '> ?p ?o . 
<' . $strInURI . '> rdf:type meandre:connector_set . 
<' . $strInURI . '> meandre:data_connector ?connectors
}';

$result = $model->sparqlQuery($strQ);
$objConnectorSetRS = new SparqlRecordSet($result);

while ($arrThisRow = $objConnectorSetRS->getRow()) {
	$strThisConnector = $arrThisRow['?connectors'];
?>
<li><?php echo $strThisConnector; ?></li>
<ul>
<?php
	LoadConnector($strThisConnector);
?>
</ul>
<?php
}

}

function LoadConnector($strInURI) {
$db = ModelFactory::getDbStore('MySQL', 'localhost', 'meandreportal', 'meandreportal', 'D3M0.2008');

$modelURI = 'http://leo1vm06.ncsa.uiuc.edu/dump.nt';
$model = $db->getModel($modelURI);

$strQ = 'PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX meandre: <http://www.meandre.org/ontology/>
PREFIX dc: <http://purl.org/dc/elements/1.1/>
SELECT DISTINCT ?datasource ?datatarget ?instancesource ?instancetarget
WHERE { 
	<' . $strInURI . '> ?p ?o . 
<' . $strInURI . '> rdf:type meandre:data_connector_configuration . 
<' . $strInURI . '> meandre:connector_instance_data_port_source ?datasource .
<' . $strInURI . '> meandre:connector_instance_data_port_target ?datatarget .
<' . $strInURI . '> meandre:connector_instance_source ?instancesource .
<' . $strInURI . '> meandre:connector_instance_target ?instancetarget
}';

$result = $model->sparqlQuery($strQ);
$objConnectorRS = new SparqlRecordSet($result);

while ($arrThisRow = $objConnectorRS->getRow()) {
	$strDataSource = $arrThisRow['?datasource'];
	$strDataTarget = $arrThisRow['?datatarget'];
	$strInstanceSource = $arrThisRow['?instancesource'];
	$strInstanceTarget = $arrThisRow['?instancetarget'];
?>
  <li>Data Source &lt;<?php echo $strDataSource; ?>&gt;</li>
  <ul>
<?php
	LoadDataPort($strDataSource);
	LoadComponentByOutputPort($strDataSource);
?>
  </ul>
<?php

?>
  <li>Data Target &lt;<?php echo $strDataTarget; ?>&gt;</li>
  <ul>
<?php
	LoadDataPort($strDataTarget);
	LoadComponentByInputPort($strDataTarget);
?>
  </ul>
<?php

?>
  <li>Instance Source &lt;<?php echo $strInstanceSource; ?>&gt;</li>
  <ul>
<?php
	LoadDataPort($strInstanceSource);
?>
  </ul>
<?php

?>
  <li>Instance Target &lt;<?php echo $strInstanceTarget; ?>&gt;</li>
  <ul>
<?php
	LoadDataPort($strInstanceTarget);
?>
  </ul>
<?php
}

}

function LoadDataPort($strInURI) {
$db = ModelFactory::getDbStore('MySQL', 'localhost', 'meandreportal', 'meandreportal', 'D3M0.2008');

$modelURI = 'http://leo1vm06.ncsa.uiuc.edu/dump.nt';
$model = $db->getModel($modelURI);

$strQ = 'PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX meandre: <http://www.meandre.org/ontology/>
PREFIX dc: <http://purl.org/dc/elements/1.1/>
SELECT DISTINCT ?name ?desc
WHERE { 
	<' . $strInURI . '> ?p ?o . 
<' . $strInURI . '> rdf:type meandre:data_port . 
<' . $strInURI . '> dc:description ?desc .
<' . $strInURI . '> meandre:name ?name
}';

$result = $model->sparqlQuery($strQ);
$objDataPortsRS = new SparqlRecordSet($result);

while ($arrThisRow = $objDataPortsRS->getRow()) {
	$strDesc = $arrThisRow['?desc'];
	$strName = $arrThisRow['?name'];
?>
  <li><?php echo $strName . '<br>' . $strDesc; ?></li>
<?php
}

}

function LoadComponentByInputPort($strInURI) {
$db = ModelFactory::getDbStore('MySQL', 'localhost', 'meandreportal', 'meandreportal', 'D3M0.2008');

$modelURI = 'http://leo1vm06.ncsa.uiuc.edu/dump.nt';
$model = $db->getModel($modelURI);

$strQ = 'PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX meandre: <http://www.meandre.org/ontology/>
PREFIX dc: <http://purl.org/dc/elements/1.1/>
SELECT DISTINCT ?component ?name ?resourcelocation
WHERE { 
	?component ?p ?o . 
	?component rdf:type meandre:executable_component . 
	?component meandre:input_data_port <' . $strInURI . '> .
	?component meandre:name ?name .
	?component meandre:resource_location ?resourcelocation
}';

$result = $model->sparqlQuery($strQ);
echo $model->sparqlQuery($strQ, 'HTML');
}

function LoadComponentByOutputPort($strInURI) {
$db = ModelFactory::getDbStore('MySQL', 'localhost', 'meandreportal', 'meandreportal', 'D3M0.2008');

$modelURI = 'http://leo1vm06.ncsa.uiuc.edu/dump.nt';
$model = $db->getModel($modelURI);

$strQ = 'PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX meandre: <http://www.meandre.org/ontology/>
PREFIX dc: <http://purl.org/dc/elements/1.1/>
SELECT DISTINCT ?component ?name ?resourcelocation
WHERE { 
	?component ?p ?o . 
	?component rdf:type meandre:executable_component . 
	?component meandre:output_data_port <' . $strInURI . '> .
	?component meandre:name ?name .
	?component meandre:resource_location ?resourcelocation
}';

$result = $model->sparqlQuery($strQ);
echo $model->sparqlQuery($strQ, 'HTML');
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