<?php
/**
 * User: Peter Clotworthy
 * Date: 17/10/16
 * Time: 21:07
 */

namespace Assemble\Middleware\Permissions;


use Assemble\Models\Person;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RegisteredPermissions extends Permissions {
	protected $loggedInMsg = 'You must be logged in to perform this.';

	protected function checkPermission(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface {
		$user = $this->ci->user;
		if($user !== -1 && is_a($user, Person::class))
			return $next($request, $response);
		return $this->failed($request, $response, $this->loggedInMsg);
	}
}