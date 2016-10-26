<?php
/**
 * User: Peter Clotworthy
 * Date: 17/10/16
 * Time: 21:19
 */

namespace Assemble\Middleware\Permissions;


use Assemble\Models\Map\PersonTableMap;
use Assemble\Models\Person;
use Assemble\Models\PersonQuery;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class OwnerPermissions
 * @package Assemble\Middleware\Permissions
 * Implies a registered user, who has write permissions on a resource (could be the owner or an admin).
 */
class OwnerPermissions extends Permissions {

	protected function checkPermission(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface {
		// TODO: Implement checkPermission() method.
		/** @var Person $user */
		$user = $this->ci->get('user');
		// Not logged in, FAIL.
		if($user === -1 || $user == null)
			return $this->failed($request, $response, 'Please login and try again.');
		// Logged in, permissions for own stuff, PASS.
		if($request->getAttribute('userID') == null)
			return $next($request, $response);

		// Admins can modify users, but not other admins (at least for now).
		if($user->getPrivilege() == PersonTableMap::COL_PRIVILEGE_ADMIN) {
			$other = PersonQuery::create()->findOneById($request->getAttribute('userID'));
			if($other->getPrivilege() == PersonTableMap::COL_PRIVILEGE_ADMIN)
				return $this->failed($request, $response, 'You do not have permission to perform this.');
			return $next($request, $response);
		}
		if(strcmp($user->getId(), $request->getAttribute('userID')) != 0)
			return $this->failed($request, $response, 'You must be logged in to perform this.');
		return $next($request, $response);
	}
}