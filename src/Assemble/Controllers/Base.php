<?php
/**
 * User: Peter Clotworthy
 * Date: 10/10/16
 * Time: 01:22
 */

namespace Assemble\Controllers;


use Assemble\Models\Person;
use Assemble\Models\PersonQuery;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Base extends Controller {
	// GET '/'
	public function getBase(RequestInterface $req, ResponseInterface $res, array $args){
		return $this->render($res);
	}

	public function postRegister(RequestInterface $req, ResponseInterface $res, array $args){
		$data = $req->getParsedBody();
		// TODO: do something with the parsed data (native PHP array), then return status codes and what not.
        $existing = PersonQuery::create()->findOneByUsername($data['username']);

        if($existing != null)
            return $this->clientError($res, new Error(ErrorCodes::CLIENT_EXISTING_USERNAME));
		if(empty($data['username']))
			return $this->clientError($res, new Error(ErrorCodes::CLIENT_VAGUE_BAD_REGISTRATION, 'Missing field \'Username\''));
        if(empty($data['name']))
            return $this->clientError($res, new Error(ErrorCodes::CLIENT_VAGUE_BAD_REGISTRATION, 'Missing field \'Name\''));
        if(empty($data['password']) || strlen($data['password']) < 5)
            return $this->clientError($res, new Error(ErrorCodes::CLIENT_VAGUE_BAD_REGISTRATION, 'Missing field \'Password\''));

		// OK; data seems fairly alright
		$hp = password_hash($data['password'], PASSWORD_BCRYPT);
		$new_user = new Person();

		$new_user
			->setUsername($data['username'])
			->setName($data['name'])
			->setPassword($hp)
			->save();

		$this->render($res, ['username' => $data['username'], 'name' => $data['name']]);
	}
}