<?php
/**
 * API Version Configuration
 *
 * Version level configuration settings that apply to all versions of the mobile access service api.
 *
 * @category Version Configuration
 * @package Config
 * @subpackage Version
 * @author Seth Arnott
 * @version 1.0
 */


// define the site user upload directory
if (!defined('DIR_USER_UPLOADS'))
{
	define('DIR_USER_UPLOADS', DOCUMENT_ROOT . '/users/uploads/');
}

// get a list of valid and available api versions
$valid_api_versions = include('supported_versions.php');

// set our api version define if this is a valid version
if (!empty($valid_api_versions[$_REQUEST['api_version']]))
{
	define('API_VERSION', $_REQUEST['api_version']);
}
else
{
	define('API_VERSION', '0.2');
}

// define our api subpath
define('API_SUBPATH', '/inc/');

// create a define for our mas api root directory for the given version
define('API_ROOT_DIR', DOCUMENT_ROOT . '/api/mas' . API_SUBPATH . API_VERSION);

// define the path to our core services directory
define('CORE_ROOT_DIR', DOCUMENT_ROOT . '/api/core');



// included autoloader class
//require_once '../inc/autoloader/MASClassAutoloader.php';

// register namespaces for dynamic class loading
// $loader = new MAS\ClassAutoloader\MASClassAutoloader;
// $loader->register();
// $loader->loadSiteNamespaces();




?>