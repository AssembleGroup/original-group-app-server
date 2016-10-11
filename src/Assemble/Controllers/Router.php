<?php
/**
 * Created by PhpStorm.
 * User: sacredskull
 * Date: 08/10/16
 * Time: 00:28
 */

namespace Assemble\Controllers;


use Assemble\Middleware\AssembleAuthenticator;
use Assemble\Middleware\PermissionLevel;
use Assemble\Middleware\Permissions;
use Assemble\Server;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Middleware\HttpBasicAuthentication;

class Router {
	public static function pave(\Slim\App $app){
		$app->get('/', Base::class . ':getBase')->add(new Permissions(PermissionLevel::GUEST()));
		$app->post('/register', Base::class . ':postRegister')->add(new Permissions(PermissionLevel::GUEST()));


		$app->add(new HttpBasicAuthentication([
			'passthrough' => ['/register', '/'],
			'authenticator' => new AssembleAuthenticator(),
			'secure' => !Server::$DEBUG,
            'error' => function(RequestInterface $req, ResponseInterface $res, array $args) use ($app){
			    return (new Base($app->getContainer()))->clientError($res, new Error(ErrorCodes::CLIENT_VAGUE_BAD_LOGIN));
            },
		]));
	}
}