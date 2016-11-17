<?php
/**
 * User: Peter Clotworthy
 * Date: 16/11/16
 * Time: 23:36
 */

namespace Assemble\tests\units;

require_once __DIR__ . '/../src/Assemble/Config/Propel/generated-conf/config.php';

use Assemble\Controllers\BaseController;
use Assemble\Controllers\Controller;
use Assemble\Controllers\Error;
use Assemble\Controllers\ErrorCodes;
use Assemble\Middleware\AssembleAuthenticator;
use Faker\Factory;
use Faker\Generator;
use Interop\Container\ContainerInterface;
use Intervention\Image\ImageManager;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Container;
use Slim\Http\Body;
use Slim\Http\Request;
use Slim\Http\RequestBody;
use Slim\Http\Response;
use Slim\Middleware\HttpBasicAuthentication;



abstract class AssembleTest extends \atoum\test {

	/** @var Generator */
	protected $faker = null;

	public function beforeTestMethod($method) {
		parent::beforeTestMethod($method);
		$this->faker = Factory::create();
	}

	protected function mockContainer(array $vals = []) : ContainerInterface {
		$this->mockGenerator()->generate(LoggerInterface::class, '\Mock');
		$this->mockGenerator()->generate(ImageManager::class, '\Mock');

		return new Container(array_merge([
			'logger' => new \Mock\LoggerInterface(),
			'imager' => new \Mock\ImageManager(),
			'user' => -1,
			'assemble' => [
				'debug' => true,
				'public_dir' => __DIR__ . "/../Public",
				'feed_posts_per_page' => 15,
				'version' => 'alpha-0.4',
			],

		], $vals));
	}

	protected function mockSlim(callable $controller, ContainerInterface $ci, $body = null, $basic = false,
	                            string $method = 'GET', string $uri = '', string $query = ''): Response{
		$envArray = [
			'REQUEST_METHOD' => $method,
			'REQUEST_URI' => $uri,
			'QUERY_STRING' => $query,
			'SERVER_NAME' => 'assemble.dev',
		];

		if(is_array($basic)){
			$envArray['AUTH_BASIC'] = "Basic";
			$envArray['PHP_AUTH_USER'] = $basic[0];
			$envArray['PHP_AUTH_PW'] = $basic[1];
		}

		$env = \Slim\Http\Environment::mock($envArray);

		$request = Request::createFromEnvironment($env);
		if($body == null)
			$body = new RequestBody();
		$request = $request->withBody($body)->withHeader('Content-Type', 'application/json');
		$response = new Response();

		$auth = new HttpBasicAuthentication([
			'path' => ['/'],
			'passthrough' => ['/register', '/test'],
			'authenticator' => new AssembleAuthenticator($ci['user']),
			'secure' => false,
			'error' => function (RequestInterface $req, Response $res, array $args) use ($ci) {
				return (new BaseController($ci))->clientError($res, new Error(ErrorCodes::CLIENT_VAGUE_BAD_LOGIN));
			},
		]);

		$response = $auth($request, $response, $controller);
		return $response;
	}

	public function standardReply(array $json){
		$this
			->phpArray($json)
				->hasKey('version', 'Version stamp missing!')
				->hasKey('api', 'API stamp missing!')
				->hasKey('status', 'Status missing from reply!')
				->hasKey('time', 'Time missing from reply!')
					->integer($json['time'])
						->isLessThanOrEqualTo(time(), 'The server\'s clock suggests you performed this request 
							before it received it')
				->string($json['api'])
					->isEqualTo('Assemble');
		return $this;
	}

	protected function mockRequest(callable $action, ContainerInterface $ci, $desiredStatus = 200,
	                               RequestBody $body = null, $basic = false, string $errorMsg = 'The server did not return the valid HTTP code.'){
		$this
			->given($callback = $action)
			->given($response = $this->mockSlim($callback, $ci, $body, $basic))
				->dump($basic)
				->integer($response->getStatusCode())
					->isEqualTo($desiredStatus, $errorMsg);
		$body = $response->getBody();

		// Must rewind the body!
		$body->rewind();

		$json = json_decode($body->getContents(), true);

		$this->standardReply($json);

		return $json;
	}
}