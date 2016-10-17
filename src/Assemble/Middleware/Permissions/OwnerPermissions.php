<?php
/**
 * User: Peter Clotworthy
 * Date: 17/10/16
 * Time: 21:19
 */

namespace Assemble\Middleware\Permissions;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class OwnerPermissions extends Permissions {

	protected function checkPermission(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface {
		// TODO: Implement checkPermission() method.
		return $next($request, $response);
	}
}