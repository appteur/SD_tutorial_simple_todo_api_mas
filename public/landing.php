<?php

 /*
	- Namespaces -
	Service: The object that will be handling the request and returning the response.
	Auth: 	 In this case Auth is simply being used to generate a dummy token in case of an error.
*/
use \MAS\service\service as Service;
use \AMRCore\Auth\auth as Auth;



/** 
 * API Access Point for all API requests.
 *
 * This is the main interface for the Mobile Access Service API.
 *	Incoming requests should include the following parameters

 *	- app_id: 	 The app_id is compared with allowed applications set in the Global configuration file.
 *	- token:  	 The token is compared with with the user token created in the Auth class
 *  - namespace: The namespace within the requested api
 *	- class:	 The class object that will process the request
 *	- method: 	 The method in the class to be run. If none is provided the method run will correspond with the class name. A 'handle' parameter can also be send in the post parameters. If a 'handle' is provided, that method will be run.

 	Note that the namespace, class and method will be populated via mod rewrite and .htaccess rules.
 *
 * @category AccessServices
 *
 * @package MobileAccessService
 *
 * @author Seth Arnott <me@setharnott.com>
 *
 * @version 0.1
*/
class api_landing
{
	/**
     *	Initializes class
	 *
     *	Calls setupConfiguration() and setupDebugModes()
     */
	function __construct()
	{
		$this->setupConfiguration();
		$this->setupDebugModes();
	}

	/**
		Includes configuration files for 3 areas: Environment/Server level, API Module level & API Version level. (Expand)

		- Main Site Configuration - Global

			Require our server configuration file. This is the configuration file that is outside the document root. Think of this as the Global server configuration file. It defines a DOCUMENT_ROOT constant that is corrected to point to the right place if this is hosted on shared hosting with multiple site files inside the document root. It also has the database access credentials and any other configuration variables that need to be set on a server wide scale. We also define an 'IS_DEBUG' constant so if we want to run some code only in the test environment it will allow that ability.
			Config Location: The config file lives in a 'config' directory that is a sibling of our document root directory. 
				If all your site files are located in /Users/MyUser/MySiteRoot, then the config directory would be located: /Users/MyUser/config

		- API Module Configuration - Per Module (e.g. core has it's own, mas has it's own, etc)

		 	Include the configuration file for all versions of the mobile access service (mas) api. Since we start with version 0.1, this config file will apply to this version, when we decide to version the api to 0.2, this configuration will also apply to that version and all future versions of the api. 
		 	Config Location: api/mas/inc/config/

	 	- API Version Configuration -

			Include the configuration file specific to the requested version of the api. 
			If different configuration is needed for a specific version of the api, values can be overridden here for a given version, or additional configuration can be defined.
			Config Location: api/mas/inc/<API VERSION>/config/
	*/
	function setupConfiguration()
	{
		// Main Site Configuration
		include_once $_SERVER['DOCUMENT_ROOT'] .'/../config/config_cpc.php';

		// API Module Configuration
		require_once '../inc/config/global.php';

		// API Version Configuration
		require_once '../inc/' . $_REQUEST['api_version'] . '/config/global.php';
	}


	/**
		- DEBUGGING - 

		Setup a define to toggle root level debugging.
		There are times when we want to log all our request or response data. By defining a constant we can easily toggle logging on or off.

		Note that a better solution would be to create our own logging class that was fully implemented on our test server, but had empty implementations on a live environment.
	*/
	function setupDebugModes()
	{
		define('ROOT_DEBUG_ENABLED', false);
	}


	/**
		- Request Handling -

		Performs request validation, creates a service object based on the requested api version and assigns request handling to the service object. If the request is processed successfully the service object will return a response, which will be passed back to the requesting application. If the request fails or is invalid an exception will be thrown and an error response will be returned instead.
	*/
	function handleApiRequest()
	{
		try 
		{
			// get the app id and token if one was passed in
			$appId = isset($_POST['app_id']) ? $_POST['app_id'] : NULL;
			$token = isset($_POST['token'])  ? $_POST['token']  : NULL;

			// get namespace, class, method if one was passed in
			$namespace 	= isset($_REQUEST['namespace']) ? $_REQUEST['namespace'] : NULL;
			$class 		= isset($_REQUEST['class']) 	? $_REQUEST['class']	 : NULL;
			$method		= isset($_REQUEST['method'])	? $_REQUEST['method']	 : NULL;

			// create service object that will handle this request
			$serviceObj = new Service();

			// get our list of endpoints that are valid and pass to our service object
			$access = include API_ROOT_DIR . '/config/access_whitelist.php';
			$serviceObj->setAccessWhitelist($access);

			// run check to see if the requesting app, token and requested endpoint & method are valid
			$serviceObj->validate($appId, $token, $namespace, $class);

			// try to handle the api request
			$response = $serviceObj->load($namespace, $class, $method, $_POST);

			if (is_array($response))
			{
				// convert response to json and echo it
				$response = json_encode($response);
				header('Content-Type: application/json');
				echo $response;
			}
			else
			{
				// something unexpected happened, handle here
			}
		} 
		catch (Exception $exception)
		{
			// our request failed so set our status to false
			$response['status'] = false;

			// we do not return a valid token on failed requests
			$response['token']	= Auth::generateDummyToken(array('uid' => 0)); 

			// set our error message and code to return
		    $response['payload']['error'] = array(
		                        				'message' => $exception->getMessage(),
		                        				'code'	  => $exception->getCode()
		                        			);

		    // encode our response as json and send it
		    $response = json_encode($response);
		    header('Content-Type: application/json');
			echo $response;
		}
	}

}







// create a landing object and handle the request
$landingObj = new api_landing();
$landingObj->handleApiRequest();






?>