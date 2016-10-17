<?php
/**
 * User: Peter Clotworthy
 * Date: 12/10/16
 * Time: 02:53
 */

namespace Assemble\Controllers;


use Assemble\Models\Group;
use Assemble\Models\GroupQuery;
use Assemble\Models\Map\GroupTableMap;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class GroupController extends Controller {
	/**
	 * @param $id
	 * @return Group
	 */
	protected function fetchGroup($id) {
		return GroupQuery::create()->findOneById($id);
	}

	public function getGroup(RequestInterface $req, ResponseInterface $res, array $args): ResponseInterface {
		$group = $this->fetchGroup($args['groupID']);
		if($group == null)
			return $this->clientError($res, new Error(ErrorCodes::CLIENT_NONEXISTENT_ENTITY));

		$data = $group->toArray(GroupTableMap::TYPE_PHPNAME, true, [], true);

		return $this->render($res, $data);
	}

	public function createGroup(RequestInterface $req, ResponseInterface $res, array $args): ResponseInterface{
		$data = $req->getParsedBody();
		if(!isset($data['Name']))
			return $this->clientError($res, new Error(ErrorCodes::CLIENT_VAGUE_BAD_GROUP_CREATION, 'You must set a group name.'));
		$existingGroup = GroupQuery::create()->findOneByName($data['Name']);
		if($existingGroup != null)
			return $this->clientError($res, new Error(ErrorCodes::CLIENT_EXISTING_GROUP_NAME));

		// TODO: Location, type, position, picture, etc.

		$newGroup = new Group();
		$newGroup
			->setName($data['Name'])
			->save();

		return $this->render($res);
	}

	public function getGroupPeople(RequestInterface $req, ResponseInterface $res, array $args): ResponseInterface {
		return $res;
	}

	public static function hasPermission(ContainerInterface $ci) : bool {
		// TODO: Implement checkPermission() method.
		return true;
	}

	public function removePersonFromGroup(RequestInterface $req, ResponseInterface $res, array $args): ResponseInterface {
		return $res;
	}
}