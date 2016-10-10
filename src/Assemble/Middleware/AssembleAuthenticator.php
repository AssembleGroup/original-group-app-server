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
	public function __invoke(array $arguments) : bool {
		$pq = new PersonQuery();
		$user = $pq->findOneByUsername($arguments['user']);

		if($user != null) {
			// TODO: hash pass and compare
			// if($user->getPassword() == magic_hash_function($arguments['password']))
			return true;
		}
		return false;
	}
}