<?php
/**
 * Created by PhpStorm.
 * User: sacredskull
 * Date: 08/10/16
 * Time: 00:58
 */

namespace Assemble\Middleware\Permissions;

use Assemble\Controllers\Permissible;
use Interop\Container\ContainerInterface;
use MyCLabs\Enum\Enum;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Response;

abstract class Permissions {
	public static $containerFolder = 'PermissionMiddlewareDeniedHandler';
	protected $ci;
	protected $deniedHandler;

	public function __construct(ContainerInterface $ci) {
		$this->ci = $ci;
		if(isset($ci[self::$containerFolder]))
			$this->deniedHandler = $ci[self::$containerFolder];
		else
			$this->deniedHandler = null;
	}

	public function __invoke(ServerRequestInterface $request, Response $response, callable $next): ResponseInterface {
		// TODO: All the permission things
		return $this->checkPermission($request, $response, $next);
	}

	protected abstract function checkPermission(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface;

	protected function failed(ServerRequestInterface $req, ResponseInterface $res) : ResponseInterface {
		if($this->deniedHandler !== null && is_callable($this->deniedHandler))
			return call_user_func($this->deniedHandler, $this->ci, $req, $res);
		// Just return the (presumably) blank response object
		return $res;
	}
}