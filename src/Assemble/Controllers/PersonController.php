<?php
/**
 * User: Peter Clotworthy
 * Date: 12/10/16
 * Time: 02:04
 */

namespace Assemble\Controllers;


use Assemble\Models\Base\GroupQuery;
use Assemble\Models\Map\PersonTableMap;
use Assemble\Models\Person;
use Assemble\Models\PersonQuery;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class PersonController extends Controller {
	/**
	 * @param $id
	 * @return \Assemble\Models\Person|null
	 */
	protected function getPersonInfo(Person $person, bool $showPrivate = false): array{
		$payload = $person->toArray();
		unset($payload['Password']);

		$publicCriteria = GroupQuery::create()
			->usePersonGroupQuery()
				->filterByHidden(false)
			->endUse();

		if($showPrivate){
			$publicCriteria = null;
		}

		$arrGroup = [];
		$i = 1;
		foreach ($person->getGroups($publicCriteria) as $group) {
			$arrGroup[$i++] = [$group->getId(), $group->getName()];
		}

		$payload['Groups'] = $arrGroup;

		return $payload;
	}

	protected function fetchPerson($id) : Person{
		if(is_a($id, Person::class))
			return $id;
		return PersonQuery::create()->findOneById($id);
	}

	public function getSpecificPerson(RequestInterface $req, ResponseInterface $res, array $args){
		$person = $this->fetchPerson($args['personID']);
		return $this->render($res, $this->getPersonInfo($person));
	}

	public function getCurrentPerson(RequestInterface $req, ResponseInterface $res, array $args){
		$person = $this->ci->get('user');

		if($person == null)
			return $this->clientError($res, new Error(ErrorCodes::SERVER_UNKNOWN_ERROR, 'You do not appear to be logged in.'));

		return $this->render($res, $this->getPersonInfo($person, true));
	}

	public function getPersonGroups(RequestInterface $req, ResponseInterface $res, array $args): ResponseInterface {
		$self = false;
		$user = null;

		if(!isset($args['personID'])) {
			$self = true;
			$user = $this->ci->get('user');
		} else {
			$user = PersonQuery::create()->findOneById($args['personID']);
		}


		$userGroups = null;

		if($self || (is_a($this->ci->get('user'), Person::class) && $user->equals($this->ci->get('user')))){
			$userGroups = $user->getGroups();
		} else {
			// Hot damn I like this snippet
			$notHiddenCriteria = GroupQuery::create()
				->usePersonGroupQuery()
				->filterByHidden(false)
				->endUse();

			$userGroups = $user->getGroups($notHiddenCriteria);
		}

		$groups = [];
		$i = 1;

		foreach ($userGroups as $group) {
			$group[$i++] = [$group->getId(), $group->getName()];
		}

		return $this->render($res, [ 'Groups' => $groups ]);
	}

	public function createPerson(RequestInterface $req, ResponseInterface $res, array $args) : ResponseInterface {
		// TODO: User avatar
		$data = $req->getParsedBody();
		if(empty($data['Username']))
			return $this->clientError($res, new Error(ErrorCodes::CLIENT_VAGUE_BAD_REGISTRATION, 'Missing field \'Username\''));

		$existing = PersonQuery::create()->findOneByUsername($data['Username']);

		if($existing != null)
			return $this->clientError($res, new Error(ErrorCodes::CLIENT_EXISTING_USERNAME));
		if(empty($data['Name']))
			return $this->clientError($res, new Error(ErrorCodes::CLIENT_VAGUE_BAD_REGISTRATION, 'Missing field \'Name\''));
		if(empty($data['Password']) || strlen($data['Password']) < 5)
			return $this->clientError($res, new Error(ErrorCodes::CLIENT_VAGUE_BAD_REGISTRATION, 'Missing field \'Password\''));

		// OK; data seems fairly alright
		$hp = password_hash($data['Password'], PASSWORD_BCRYPT);
		$new_user = new Person();

		$new_user
			->setUsername($data['Username'])
			->setName($data['Name'])
			->setPassword($hp)
			->save();

		return $this->render($res, ['Username' => $data['Username'], 'Name' => $data['Name']]);
	}

	public static function hasPermission(ContainerInterface $ci) : bool {
		// TODO: Implement checkPermission() method.
//		d($ci->user);
		if(!isset($ci->user) || $ci->user == null)
			return false;
		return true;
	}
}