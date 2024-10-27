<?php
// file: index.php

define("DEFAULT_CONTROLLER", "user");

define("DEFAULT_ACTION", "index");

/**
* Main router (single entry-point for all requests)
*/
function run() {
	try {   
		$controllerName = isset($_GET["controller"]) ? $_GET["controller"] : DEFAULT_CONTROLLER;
		$action = isset($_GET["action"]) ? $_GET["action"] : DEFAULT_ACTION;

		$controller = loadController($controllerName);
		if (method_exists($controller, $action)) {
			$controller->$action();
		} else {
			show404();
		}
	} catch (Exception $ex) {
		die("An exception occurred: " . $ex->getMessage());
	}
}

/**
* Load the required controller file and create the controller instance
*/
function loadController($controllerName) {
	$controllerClassName = getControllerClassName($controllerName);

	require_once("./controller/".$controllerClassName.".php");
	return new $controllerClassName();
}

/**
* Obtain the class name for a controller name in the URL
*/
function getControllerClassName($controllerName) {
	return strToUpper(substr($controllerName, 0, 1)).substr($controllerName, 1)."Controller";
}

/**
 * Function to show a 404 error page
 */
function show404() {
	header("HTTP/1.0 404 Not Found");
	echo "<h1>Error 404 - Página no encontrada</h1>";
	echo "<p>Lo sentimos, la página que estás buscando no existe.</p>";
	exit();
}

//run!
run();

?>
