<?php

$modelURI = 'http://leo1vm06.ncsa.uiuc.edu/dump.nt';

// Load Store into Memory Model
$model = ModelFactory::getDefaultModel();
$model->load('dump.nt');

// Cache Memory Model Store into Database Store
$db = ModelFactory::getDbStore('MySQL', DBServer, DBName, DBUser, DBPassword);

// Setup tables if Necessary
if (!$db->isSetup('MySQL')) {
	$db->createTables('MySQL');
}

// Flush Cached DB Model
if ($db->modelExists($modelURI)) {
	$db->delete($modelURI);
}

// Store this Model to DB
$db->putModel($model, $modelURI);
?>