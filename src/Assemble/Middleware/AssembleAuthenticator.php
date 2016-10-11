<?php
/**
 * Created by PhpStorm.
 * User: sacredskull
 * Date: 08/10/16
 * Time: 00:15
 */

namespace Assemble\Middleware;


use Assemble\Models\PersonQuery;
use Slim\Middleware\HttpBasicAuthentication\AuthenticatorInterface;

class AssembleAuthenticator implements AuthenticatorInterface {
	public function __invoke(array $args) : bool {
		if(empty($args['username']))
			return false;
		$user = PersonQuery::create()->findOneByUsername($args['username']);

		if($user != null) {
			if(password_verify($args['password'], $user->getPassword()))
				return true;
		}
		return false;
	}
}