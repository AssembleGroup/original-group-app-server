<?php
/**
 * Created by PhpStorm.
 * User: sacredskull
 * Date: 08/10/16
 * Time: 00:15
 */

namespace Assemble\Middleware;


use Assemble\Models\PersonQuery;
use Interop\Container\ContainerInterface;
use Slim\Middleware\HttpBasicAuthentication\AuthenticatorInterface;

class AssembleAuthenticator implements AuthenticatorInterface {
    private $user;
    public function __construct($user){
        $this->user = $user;
    }

	public function __invoke(array $args) : bool {
		if(empty($args['user'])) {
			// We want the authentication to be optional, so if nothing is provided, we accept it.
			// The logged in user ($ci->user) is still -1/null.
			return true;
		}
		$user = PersonQuery::create()->findOneByUsername($args['user']);

		if($user != null) {
			if(password_verify($args['password'], $user->getPassword())) {
			    $this->user = $user;
                return true;
            }
		}
		return false;
	}
}