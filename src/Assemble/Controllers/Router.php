<?php
/**
 * Created by PhpStorm.
 * User: sacredskull
 * Date: 08/10/16
 * Time: 00:28
 */

namespace Assemble\Controllers;


use Assemble\Middleware\AssembleAuthenticator;
use Assemble\Middleware\Permissions\GuestPermissions;
use Assemble\Middleware\Permissions\OwnerPermissions;
use Assemble\Middleware\Permissions\RegisteredPermissions;
use Assemble\Server;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Middleware\HttpBasicAuthentication;

class Router {
	public static function pave(\Slim\App $app){


		$app->get('/', BaseController::class . ':getBase');
		$app->post('/register', PersonController::class . ':createPerson')->add(GuestPermissions::class);

		$app->group('/user', function() use ($app) {
			$app->get('', PersonController::class . ':getCurrentPerson')->add(RegisteredPermissions::class);
			$app->post('', PersonController::class . ':createPerson')->add(GuestPermissions::class);
			$app->map(['PATCH', 'PUT'],'', PersonController::class . ':changePerson')->add(OwnerPermissions::class);

			$app->get('/feed', PersonController::class . ':getPersonalFeed')->add(RegisteredPermissions::class);
			$app->get('/groups', PersonController::class . ':getPersonGroups')->add(OwnerPermissions::class);

			$app->group('/group', function() use ($app){
				$app->post('', PersonController::class . ':addGroupToPerson')->add(OwnerPermissions::class);
				$app->delete('', PersonController::class . ':removeGroupFromPerson')->add(OwnerPermissions::class);
			});

			$app->group('/{personID:\d{1,6}}', function() use($app) {
				$app->get('', PersonController::class . ':getSpecificPerson');
				$app->map(['PATCH', 'PUT'],'', PersonController::class . ':changePerson')->add(OwnerPermissions::class);

				$app->get('/groups', PersonController::class . ':getPersonGroups');

				$app->group('/group', function() use ($app){
					$app->post('', PersonController::class . ':addGroupToPerson')->add(OwnerPermissions::class);
//					$app->delete('', PersonController::class . ':removeGroupFromPerson')->add(OwnerPermissions::class);
				});
			});
		});

		$app->group('/interest', function() use ($app) {
			$app->group('/{interestID:\d{1,6}}', function () use($app){
				$app->get('[/page/{page:\d{1,6}}]', GroupController::class . ':getGroupsByInterest');
			});
		});

		$app->group('/group', function() use ($app) {
			$app->post('', GroupController::class . ':createGroup')->add(RegisteredPermissions::class);

			$app->group('/{groupID:\d{1,6}}', function () use($app){
				$app->get('', GroupController::class . ':getGroup');
				$app->map(['PATCH', 'PUT'],'', GroupController::class . ':changeGroup');

				$app->get('/users', GroupController::class . ':getGroupPeople');

				$app->group('/feed', function() use ($app) {
					$app->get('[/page/{page:\d{1,6}}]', GroupController::class . ':getGroupFeed');
					$app->post('', GroupController::class . ':postToGroupFeed');
					$app->map(['PATCH', 'PUT'], '/{postID:\d{1,6}}', GroupController::class . ':changeFeedPost');
					$app->delete('/post/{postID:\d{1,6}}', GroupController::class . ':deleteFeedPost');
				});

			});

			$app->delete('/user/{personID:\d{1,6}}', GroupController::class . ':removePersonFromGroup');
		});
		$app->add(new HttpBasicAuthentication([
			'path' => ['/'],
			'passthrough' => ['/register', '/test'],
			'authenticator' => new AssembleAuthenticator($app->getContainer()),
			'secure' => !Server::$DEBUG,
			'error' => function (RequestInterface $req, ResponseInterface $res, array $args) use ($app) {
				return (new BaseController($app->getContainer()))->clientError($res, new Error(ErrorCodes::CLIENT_VAGUE_BAD_LOGIN));
			},
		]));
	}
}