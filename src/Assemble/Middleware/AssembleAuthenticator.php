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
    private $cont;
    public function __construct(ContainerInterface $cont){
        $this->cont = $cont;
    }

	public function __invoke(array $args) : bool {
		if(empty($args['user'])) {
			// We want the authentication to be optional, so if nothing is provided, we accept it.
			// The logged in user ($ci->user) is still -1/null.
			return true;
		}
		$user = PersonQuery::create()->findOneByUsername($args['user']);
        //sd($user);

		if($user != null) {
			if(password_verify($args['password'], $user->getPassword())) {
			    $this->cont['user'] = $user;
                return true;
            }
		}
		return false;
	}
}