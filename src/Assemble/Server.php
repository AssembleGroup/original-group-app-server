<?php
/**
 * Created by PhpStorm.
 * User: sacredskull
 * Date: 08/10/16
 * Time: 00:17
 */

namespace Assemble;


use Assemble\Controllers\Router;
use Interop\Container\ContainerInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Propel\Runtime\Propel;

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
		$app = new \Slim\App(["settings" => require __DIR__ . "/Config/Slim/slim.php"]);
		$container = $app->getContainer();

		$container['assemble'] = [
			'debug' => static::$DEBUG,
		];

		$container['logger'] = (function (ContainerInterface $c) {
			$loggerSettings = $c['settings']['logger'];
			$logger = new Logger($loggerSettings['name']);
			//$logger->pushProcessor(new UidProcessor());
			$logger->pushHandler(new RotatingFileHandler($loggerSettings['path'], 2, $c['assemble']['debug']? Logger::DEBUG : Logger::INFO));
			return $logger;
		})($container);

		Propel::getServiceContainer()->setLogger($container['settings']['logger']["name"], $container->logger);
		return $app;
	}

	public function __invoke() {
		Router::pave($this->app);
		return $this->app->run();
	}

	public static function getPublicDir() : string {
		return __DIR__ . "/../../Public";
	}
}