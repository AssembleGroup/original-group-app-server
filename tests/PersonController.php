<?php
/**
 * User: Peter Clotworthy
 * Date: 17/11/16
 * Time: 02:29
 */

namespace Assemble\tests\units\Controllers;


use Assemble\Models\Base\PersonQuery;
use Assemble\Models\Person;
use Assemble\tests\units\AssembleTest;
use Slim\Http\RequestBody;

class PersonController extends AssembleTest {
	protected function createPerson(){
		$user = new Person();
		return $user
			->setUsername($this->faker->userName)
			->setPassword($this->faker->password)
			->setEmail($this->faker->email)
			->setName($this->faker->name)
			->save();
	}

	public function testEmptyRegister(){
		$this->given($ci = $this->mockContainer());
		$json = $this->mockRequest(function($req, $res, $arr = []) use ($ci){
			$base = new \Assemble\Controllers\PersonController($ci);
			return $base->createPerson($req, $res, $arr);
		}, $ci, 422);

		$this
			->standardReply($json)
			->string($json['status'])
				->contains('fail', 'The server seemed to accept an empty registration.');
	}

	public function testValidRegister(){
		$this->given($ci = $this->mockContainer());
		$body = new RequestBody();

		$userData = [
			'username' => $this->faker->userName,
			'password' => $this->faker->password,
			'email' => $this->faker->email,
			'name' => $this->faker->name,
			'picture' => $this->faker->imageUrl()
		];

		$dataJson = json_encode($userData, JSON_PRETTY_PRINT);

		$body->write($dataJson);

		$json = $this->mockRequest(function($req, $res, $arr = []) use ($ci) {
			$base = new \Assemble\Controllers\PersonController($ci);
			return $base->createPerson($req, $res, $arr);
		}, $ci, 200, $body);

		$this
			->dump($dataJson)
			->standardReply($json)
			->phpArray($json)
				->string($json['status'])
					->isEqualTo('success', 'Could not register!');

		$this
			->dump($json)
			->phpArray($json)
				->hasKey('data')
					->isNotEmpty('Registration seemed successful, but the data key was empty.');

		PersonQuery::create()
			->findOneByUsername($userData['username'])
			->delete();
	}
}