<?php
/**
 * User: Peter Clotworthy
 * Date: 10/10/16
 * Time: 01:16
 */

namespace Assemble\Controllers;


use Assemble\Models\Person;
use Interop\Container\ContainerInterface;
use Intervention\Image\ImageManager;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;


abstract class Controller {
	protected $ci;
	public static $sCI;
	/**
	 * @var \Monolog\Logger
	 */
	protected $logger;
	/**
	 * @var \Slim\Router
	 */
	protected $router;
	/**
	 * @var Person
	 */
    protected $user;
	/**
	 * @var ImageManager
	 */
	protected $imager;

	public function __construct(ContainerInterface $ci) {
		$this->ci = $ci;
		$this->logger = $ci->logger;
		$this->router = $ci->router;
        $this->user = $ci->user;
		$this->imager = $ci->get('imager');
	}

	public function clientError(Response $res, Error $error, array $args = []) {
	    if($error == null)
	        $error = new Error();
        $this->logger->info("[Client Error] #$error->code: $error->message - HTTP code $error->httpCode");
        return $this->render($res, ['status' => 'fail' , 'data' => array_merge($args, ['error' =>[ 'type' => 'client', 'code' => $error->code, 'message' => $error->message ]])], $error->httpCode);
	}

	public static function generalClientError(Response $res, Error $error, array $args = []) {
		if($error == null)
			$error = new Error();

        self::$sCI->logger->info("[Client Error] #$error->code: $error->message - HTTP code $error->httpCode");

		$data = [
			'api' => 'Assemble',
			'version' => static::$sCI['assemble']['version'] ?? 'unknown',
			'time' => time(),
			'status' =>  'fail',
			'data' => array_merge($args, ['error' => ['code' => $error->code, 'message' => $error->message]]),
		];
		if(is_array($args)) {
			$data = array_merge($data, $args);
		} else if($args != null){
			array_push($data, $args);
		}

		return $res->withJson($data, $error->httpCode, static::$sCI['assemble']['debug'] ? JSON_PRETTY_PRINT : null);
	}

	public function serverError(Response $res, Error $error, array $args = []){
		if($error == null)
			$error = new Error();
        $this->logger->error("[ERROR] #$error->code: $error->message - HTTP code $error->httpCode");
		return $this->render($res, ['status' => 'fail', 'data' => array_merge($args, ['error' => [ 'type' => 'server', 'code' => $error->code, 'message' => $error->message ]])], $error->httpCode);
	}

	public function successRender(Response $res, $args = [], int $statusCode = 200): ResponseInterface {
		return $this->render($res, ['status' => 'success', 'data' => $args], $statusCode);
	}

	public function render(Response $res, $args = [], int $statusCode = 200): ResponseInterface {
		$data = [
			'api' => 'Assemble',
			'version' => $this->ci['assemble']['version'],
			'time' => time(),
			'status' => 'success',
		];

		if(is_array($args)) {
			$data = array_merge($data, $args);
		} else if($args != null){
			array_push($data, $args);
		}

		return $res->withJson($data, $statusCode, $this->ci['assemble']['debug'] ? JSON_PRETTY_PRINT : null);
	}

}