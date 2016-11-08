<?php
/**
 * User: Peter Clotworthy
 * Date: 13/10/16
 * Time: 05:50
 */

namespace Assemble\Middleware\Permissions;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class GuestPermissions
 * @package Assemble\Middleware\Permissions
 * Must not be logged in.
 */
class GuestPermissions extends Permissions  {
	protected function checkPermission(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface {
		$user = $this->ci->user;
		if($user === -1)
			return $next($request, $response);
		return $this->failed($request, $response);
	}
}