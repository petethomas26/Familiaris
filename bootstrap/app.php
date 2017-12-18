<?php

use Respect\Validation\Validator as v;
use App\Install;
use App\Models\Log;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use App\Mail\Mailer;

use App\View\Factory;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

session_start();

// Bring in all dependencies
require __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('Europe/London');


//Load the dotenv file containing config details (development or production)
$mode = file_get_contents(__DIR__ . '/mode.php');

$dotenv = (new \Dotenv\Dotenv(__DIR__, 'config/'.$mode.'.php'))->load();


// Create a Slim instance with settings
$app = new \Slim\App([
	'settings' => [
		'displayErrorDetails' => getenv("ERROR_DISPLAY"),
		'db' => [
			'driver'    => getenv("DB_DRIVER"),
			'host'      => getenv("DB_HOST"),
			'port'      => getenv("DB_PORT"),
			'database'  => getenv("DB_DATABASE"),
			'username'  => getenv("DB_USERNAME"),
			'password'  => getenv("DB_PASSWORD"),
			'charset'   => getenv("DB_CHARSET"),
			'collation' => getenv("DB_COLLATION"),
			'prefix'    => getenv("DB_PREFIX") 
		],
		'baseUrl' => getenv('BASE_URL')
	]
]);

// Get the container
$container = $app->getContainer();

// Get capsule (Laravel mechanism for making Eloquent available)
$capsule = new \Illuminate\Database\Capsule\Manager;
// Make connection to database
$capsule->addConnection($container['settings']['db']);

// Make available globally so that we can use it with Models
$capsule->setAsGlobal();
// Boot Eloquent
$capsule->bootEloquent();


$container['mode'] = function($container) use($mode)  {
	return true;
};

$container['db'] = function($container) use($capsule) {
	return $capsule;
};

// Adding installation
$container['install'] = function($container) {
	return new \App\Install\Install;
};

// Adding authentication
$container['auth'] = function($container) {
	return new \App\Auth\Auth;
};

$container['flash'] = function($container) {
	return new \Slim\Flash\Messages;
};


$container['view'] = function($container) use ($mode){
	// Create a Twig instance, say where we keep our views and give options
	$view = new \Slim\Views\Twig(__DIR__ . '/../resources/views', [
		'cache' => false,
		
	]);

	//Add extension to enable generation of urls to different routes within views
	$view->addExtension(new \Slim\Views\TwigExtension(
			$container->router,
			$container->request->getUri() //pass current uri
	));

	//$view->addExtension(new \Twig_Extension_Debug);

	$view->addExtension(new \App\Debug\DebugExtension);

	// Need to ensure that member table exists
	$view->getEnvironment()->addGlobal('install', [
		'check' => $container->install->check($container),
	]);

	//When setting nav bar view, need auth. This mechanism avoids
	//multiple db accesses (i.e. data is pulled from db only once)
	$view->getEnvironment()->addGlobal('auth', [
		'check' => $container->auth->check(),
		'member' => $container->auth->member()
	]);

	//Incorporate flash messages into views
	$view->getEnvironment()->addGlobal('flash', $container->flash);

	$view->getEnvironment()->addGlobal('session', $_SESSION);

	$view->getEnvironment()->addGlobal('mode', $mode);

	// Alternative to Twig's base_url() - a global constant for use with HTTPS rather than HTTP
	$view->getEnvironment()->addGlobal('base_url', $container['settings']['baseUrl']);

	return $view;
};

$container['validator'] = function($container) {
	return new App\Validation\Validator;
};

$container['mailer'] = function($container) {
	$mailer = new PHPMailer(true);  // 'true' enables exceptions
	$mailer->isSMTP();
	$mailer->SMTPAuth = true;       // Enables SMPT authentication ; set false for localhost

	$mailer->SMTPDebug = 3; 		// Not in a production environment
	$mailer->Debugoutput = 'html'; 	// Not in a production environment
	
	$mailer->Host = 'smtp.familiaris.uk'; //'smtp.gmail.com'; 					
	$mailer->Username = 'admin@familiaris.uk'; //'pete.thomas.26@gmail.com'; 
	$mailer->Password = '$NewportPagnellAdmin$'; //'$WestDerby26$';  
	
	$mailer->SMTPSecure = 'tls'; 	//'tls' previously 'ssl'  ;   set blank for localhost
	$mailer->Port = 587;  			// previously 465  - 25 for local host
	$mailer->CharSet= 'utf-8';                          
	
	$mailer->isHTML(true);

	$mailer->SMTPOptions = array(
      'ssl' => array(
          'verify_peer' => false,
          'verify_peer_name' => false,
          'allow_self_signed' => false
      )
    );

    //return new Mailer($container['view'], $mailer); //New emailer

	return $mailer;  // Old emailer
};

// Custom error handlers

$container['notFoundHandler'] = function($container) {
    return function($request, $response) use ($container)
    {
        return $container['view']->render($response->withStatus(404), 'Error\404.twig');
    };    
};

$container['errorHandler'] = function($container) {
	return function($request, $response, $exception) use ($container) {
		return $container['view']->render($response->withStatus(503), 'Error\503.twig');			
	};
};

// With which to sanitize input data

$container['purifier'] = function($container) {
	$config = HTMLPurifier_Config::createDefault();
	$purifier = new HTMLPurifier($config);
	return $purifier;
};

// Random number library
$container['randomLib'] = function($container) {
	$factory = new RandomLib\Factory;
	$generator = $factory->getMediumStrengthGenerator();
	return $generator;
};

/*************************************************
* Required in order to interface paginator 
* into our code without using more Laravel code
* ***********************************************/

LengthAwarePaginator::viewFactoryResolver(function() {
	return new Factory;
});

LengthAwarePaginator::defaultView('pagination/pagination.twig');

Paginator::currentPathResolver(function() {
	return isset($_SERVER['REQUEST_URI']) ? strtok($_SERVER['REQUEST_URI'], '?') : '/';
});

Paginator::currentPageResolver(function() {
	return isset($_GET['page']) ? $_GET['page'] : 1;
});


/*************************************
* Controllers
* ***********************************/
$container['HomeController'] = function($container) {
	return new \App\Controllers\HomeController($container);
};

$container['AuthController'] = function($container) {
	return new \App\Controllers\Auth\AuthController($container);
};

$container['InstallController'] = function($container) {
	return new \App\Controllers\Auth\InstallController($container);
};

$container['PasswordController'] = function($container) {
	return new \App\Controllers\Auth\PasswordController($container);
};

$container['EmailController'] = function($container) {
	return new \App\Controllers\Auth\EmailController($container);
};


$container['AboutController'] = function($container) {
	return new \App\Controllers\AboutController($container);
};


$container['MembershipController'] = function($container) {
	return new \App\Controllers\Membership\MembershipController($container);
};

$container['KnowledgebaseController'] = function($container) {
	return new \App\Controllers\Knowledgebase\KnowledgebaseController($container);
};

$container['NoticeController'] = function($container) {
	return new \App\Controllers\Notice\NoticeController($container);
};

$container['AdminController'] = function($container) {
	return new \App\Controllers\Administration\AdminController($container);
};

$container['GuideController'] = function($container) {
	return new \App\Controllers\GuideController($container);
};

$container['DocController'] = function($container) {
	return new \App\Controllers\Doc\DocController($container);
};


/********************************************
* Models
* *******************************************/
$container['Member'] = function($container) {
	return new \App\Models\Member();
};

$container['Invitation'] = function($container) {
	return new \App\Models\Invitation;
};

$container['Log'] = function($container) {
	return new \App\Models\Log;
};

$container['Notice'] = function($container) {
	return new \App\Models\Notice;
};

$container['Opinion'] = function($container) {
	return new \App\Models\Opinion;
};

$container['Vote'] = function($container) {
	return new \App\Models\Vote;
};

$container['favourite'] = function($container) {
	return new \App\Models\Favourite;
};

/*********************************************
* Adding cross site request forgery guard
**********************************************/
$container['csrf'] = function($container) {
	$csrf = new \Slim\Csrf\Guard;
	$csrf->setFailureCallable(function ($request, $response, $next) use ($container) {
		   return $container['view']->render($response->withStatus(400), 'Error/400.twig');
		});
	return $csrf;
};

/*********************************
* Middleware
* ********************************/
$app->add(new \App\Middleware\ValidationErrorsMiddleware($container));
$app->add(new \App\Middleware\OldInputMiddleware($container));
$app->add(new \App\Middleware\CsrfViewMiddleware($container));

/********************************************
* Enables new validation rules to be added
********************************************/
v::with('App\\Validation\\Rules\\');

/***************************************
* Turn on CSRF
* *************************************/
$app->add($container->csrf); 

/*********************************
* Routes
* *******************************/
//require __DIR__ . '/../app/routes.php';
require __DIR__ . '/../app/Routes/home.php';

require __DIR__ . '/../app/Routes/about.php';

require __DIR__ . '/../app/Routes/Administration/administration.php';
require __DIR__ . '/../app/Routes/Auth/auth.php';
require __DIR__ . '/../app/Routes/Knowledgebase/knowledgebase.php';
require __DIR__ . '/../app/Routes/Membership/membership.php';

require __DIR__ . '/../app/Routes/Notice/notice.php';

require __DIR__ . '/../app/Routes/guide.php';
require __DIR__ . '/../app/Routes/Doc/doc.php';

/********************************************
* Check database
* Ensure that contact is made with database
* Check that Log table exists
* *******************************************/
$host = $container['settings']['db']['host'];
$database = $container['settings']['db']['database'];
$port = $container['settings']['db']['port'];
$charset = $container['settings']['db']['charset'];
$username = $container['settings']['db']['username'];
$password = $container['settings']['db']['password'];
try {
	// try to connect to database
	$conn = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create the Log table
	if (! $container->db->schema()->hasTable('log')) {
		Log::createTable($container);
	};
	
} catch (PDOException $e) {
	
	
};

