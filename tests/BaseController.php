<?php
/**
 * User: Peter Clotworthy
 * Date: 17/11/16
 * Time: 00:10
 */

namespace Assemble\tests\units\Controllers;

use Assemble\Models\Person;
use Assemble\tests\units\AssembleTest;
use Slim\Container;
use Faker\Factory;

class BaseController extends AssembleTest {
	public function testBaseAnon(){
		$ci = $this->mockContainer();
		$this->mockRequest(function($req, $res, $arr = []) use ($ci){
			$base = new \Assemble\Controllers\BaseController($ci);
			return $base->getBase($req, $res, $arr);
		}, $ci);
	}

	public function testBaseBadAuth(){
		$ci = $this->mockContainer();
		$json = $this->mockRequest(function($req, $res, $arr = []) use ($ci){
			$base = new \Assemble\Controllers\BaseController($ci);
			return $base->getBase($req, $res, $arr);
		}, $ci, 401, null, [$this->faker->userName, $this->faker->password]);

		$this
			->standardReply($json)
				->string($json['status'])
					->isNotEqualTo('success', 'The server accepted bad credentials (randomly generated).');
	}

	public function testGoodAuth(){
		$ci = $this->mockContainer();
		$userData = [
			'username' => $this->faker->userName,
			'password' => $this->faker->password,
			'email' => $this->faker->email,
			'name' => $this->faker->name,
			'picture' => $this->faker->imageUrl()
		];

		$user = new Person();
		$user
			->setUsername($userData['username'])
			->setPassword($userData['password'])
			->setEmail($userData['email'])
			->setName($userData['name'])
			->save();

		$json = $this->mockRequest(function($req, $res, $arr = []) use ($ci) {
			$base = new \Assemble\Controllers\BaseController($ci);
			return $base->getBase($req, $res, $arr);
		}, $ci, 200, null, [$userData['username'], $userData['password']], 'Could not login with valid details!');

		$this
			->dump($json)
			->standardReply($json);

		$user->delete();
	}
}