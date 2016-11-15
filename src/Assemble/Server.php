<?php
/**
 * Created by PhpStorm.
 * User: sacredskull
 * Date: 08/10/16
 * Time: 00:17
 */

namespace Assemble;


use Assemble\Controllers\Controller;
use Assemble\Controllers\Error;
use Assemble\Controllers\ErrorCodes;
use Assemble\Controllers\GroupController;
use Assemble\Controllers\Router;
use Assemble\Middleware\Permissions\Permissions;
use Assemble\Models\Base\GroupQuery;
use Assemble\Models\Base\PersonQuery;
use Assemble\Models\Group;
use Assemble\Models\Post;
use Interop\Container\ContainerInterface;
use Intervention\Image\ImageManager;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Propel\Runtime\Propel;
use Kint;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Response;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/Config/Propel/generated-conf/config.php';

class Server {
	public static $DEBUG = true;
	private $app = null;

	public function __construct() {
		if (static::$DEBUG == true) {
			ini_set('display_errors', 'On');
			error_reporting(E_ALL);
		} else {
			ini_set('display_errors', 'Off');
			error_reporting(0);
		}

		$this->app = $this->init();
	}

	private function init() : \Slim\App {
        Kint::enabled(self::$DEBUG);
		$app = new \Slim\App(["settings" => require __DIR__ . "/Config/Slim/slim.php"]);
		$container = $app->getContainer();

		$container['assemble'] = [
			'debug' => static::$DEBUG,
			'public_dir' => __DIR__ . "/../../Public",
			'feed_posts_per_page' => 15,
			'version' => 'alpha-0.3',
		];

		$container[Permissions::$containerFolder] = $container->protect(function($ci, $request, $response, $msg = null) use ($container): ResponseInterface  {
			if($msg == null)
				$msg = 'You do not have the appropriate permissions to perform this.';
			if(Controller::$sCI == null)
				Controller::$sCI = $container;
			return Controller::generalClientError($response, new Error(ErrorCodes::CLIENT_VAGUE_BAD_LOGIN, $msg));
			//return Controller::generalClientError($response, new Error(ErrorCodes::CLIENT_VAGUE_BAD_LOGIN, $msg), ['error' => $msg]);
		});

		$container['logger'] = (function (ContainerInterface $c) {
			$loggerSettings = $c['settings']['logger'];
			$logger = new Logger($loggerSettings['name']);
			//$logger->pushProcessor(new UidProcessor());
			$logger->pushHandler(new RotatingFileHandler($loggerSettings['path'], 2, $c['assemble']['debug']? Logger::DEBUG : Logger::INFO));
			return $logger;
		})($container);

		$container['imager'] = function (ContainerInterface $c) {
			return new ImageManager(array('driver' => 'gd'));
		};

		$container['user'] = -1;

        Propel::getServiceContainer()->setLogger($container['settings']['logger']["name"], $container->logger);

		return $app;
	}

	public function __invoke() {
		Router::pave($this->app, self::$DEBUG);
		return $this->app->run();
	}

	public static function getPublicDir() : string {
		return __DIR__ . "/../../Public";
	}
}