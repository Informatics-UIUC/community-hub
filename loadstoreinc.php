<?php

function CacheStore($strInStoreURI) {
	// Load RDF Store
	$strStore = LoadFromWeb($strInStoreURI);
	
	// Cache to File
	SaveToFile('./cache/store.tmp', $strStore);
	
	// Load Store into Memory Model
	$model = ModelFactory::getDefaultModel();
	$model->load('./cache/store.tmp');
	
	// Cache Memory Model Store into Database Store
	$db = ModelFactory::getDbStore('MySQL', DBServer, DBName, DBUser, DBPassword);
	
	// Setup tables if Necessary
	if (!$db->isSetup('MySQL')) {
		$db->createTables('MySQL');
	}
	
	// Flush Cached DB Model
	if ($db->modelExists($strInStoreURI)) {
		$db->delete($strInStoreURI);
	}
	
	// Store this Model to DB
	$db->putModel($model, $strInStoreURI);

}



?>