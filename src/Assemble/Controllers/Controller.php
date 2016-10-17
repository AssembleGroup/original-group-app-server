<?php
/**
 * User: Peter Clotworthy
 * Date: 10/10/16
 * Time: 01:16
 */

namespace Assemble\Controllers;


use Interop\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;



abstract class Controller implements Permissible {
	protected $ci;
	/**
	 * @var \Monolog\Logger
	 */
	protected $logger;
	/**
	 * @var \Slim\Router
	 */
	protected $router;

	public function __construct(ContainerInterface $ci) {
		$this->ci = $ci;
		$this->logger = $ci->logger;
		$this->router = $ci->router;
	}

	public function clientError(ResponseInterface $res, Error $error){
	    if($error == null)
	        $error = new Error();
		return $this->render($res, [ 'error' => [ 'type' => 'client', 'code' => $error->code, 'message' => $error->message ]], 400);
	}

	public function serverError(ResponseInterface $res, Error $error){
		if($error == null)
			$error = new Error();
		return $this->render($res, [ 'error' => [ 'type' => 'server', 'code' => $error->code, 'message' => $error->message ]], 500);
	}

	public function render(ResponseInterface $res, $args = null, int $statusCode = 200): ResponseInterface {
		$data = [
			'api' => 'Assemble',
			'version' => "alpha-0.1",
			'time' => time(),
		];
		if(is_array($args)) {
			$data = array_merge($data, $args);
		} else if($args != null){
			array_push($data, $args);
		}

		return $res->withJson($data, $statusCode);
	}
}