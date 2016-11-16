<?php
/**
 * User: Peter Clotworthy
 * Date: 12/10/16
 * Time: 02:04
 */

namespace Assemble\Controllers;


use Assemble\Models\Base\GroupQuery;
use Assemble\Models\Base\PersonGroupQuery;
use Assemble\Models\Base\PostQuery;
use Assemble\Models\Map\PersonTableMap;
use Assemble\Models\Person;
use Assemble\Models\PersonGroup;
use Assemble\Models\PersonQuery;
use Intervention\Image\Constraint;
use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\ImageManager;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class PersonController extends Controller {

    /**
     * Returns a JSON array of a specified user - but only public information. If the logged in user matches the
     * specified user, the function redirects to getCurrentPerson().
     * @see \Assemble\Controllers\PersonController::getCurrentPerson()
     * @param RequestInterface $req
     * @param Response $res
     * @param array $args
     * @return ResponseInterface
     */
    public function getSpecificPerson(RequestInterface $req, Response $res, array $args){
		$person = PersonQuery::create()->findOneById($args['personID']);
        if($person == null)
            return $this->clientError($res, new Error(ErrorCodes::CLIENT_NONEXISTENT_ENTITY, 'A user with that ID could not be found.'));
        if($person->equals($this->ci->get('user')))
            return $this->getCurrentPerson($req, $res, $args);
        return $this->successRender($res, $person->getDetailsArray());
	}

    /**
     * Returns public & private information about the currently logged in user.
     * @param RequestInterface $req
     * @param Response $res
     * @param array $args
     * @return ResponseInterface
     */
    public function getCurrentPerson(RequestInterface $req, Response $res, array $args){
		$person = $this->ci->get('user');

		if($person == null)
			return $this->clientError($res, new Error(ErrorCodes::CLIENT_NONEXISTENT_ENTITY, 'You do not appear to be logged in.'));

		return $this->successRender($res, $person->getDetailsArray(true));
	}

    /**
     * Gets a user's details. If you're looking at the public view of your own account (/user/{yourID}/groups),
     * you only see what everyone else sees. To view hidden groups, you need to use /user/groups.
     * @param RequestInterface $req
     * @param Response $res
     * @param array $args
     * @return ResponseInterface
     */
    public function getPersonGroups(RequestInterface $req, Response $res, array $args): ResponseInterface {
		$self = false;
		$user = null;
	    $publicView = false;

		if(!isset($args['personID'])) {
			$self = true;
			$user = $this->ci->get('user');
		} else {
			$user = PersonQuery::create()->findOneById($args['personID']);
			if($this->user !== -1 && $user->getId() == $this->user->getId()){
				$publicView = true;
			}
		}

		$userGroups = [];

		if($self || (is_a($this->ci->get('user'), Person::class) && $user->equals($this->ci->get('user'))) && !$publicView){
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
		foreach ($userGroups as $group) {
			$groups[] = [
				$group->getId(),
				$group->getName(),
			];
		}

		return $this->successRender($res, [ 'Groups' => $groups ]);
	}

    /**
     * Adds a person to a group, including whether or not this should be publicly viewable.
     * @param Request $req
     * @param Response $res
     * @param array $args
     * @return ResponseInterface
     */
    public function addGroupToPerson(Request $req, Response $res, array $args) : ResponseInterface {
		$data = $req->getParsedBody();
		$private = false;

	    $group = null;

	    if(isset($data['groupID'])){
		    $group = GroupQuery::create()->findOneById($data['groupID']);
	    } else if(isset($args['groupID'])) {
		    $group = GroupQuery::create()->findOneById($args['groupID']);
	    }

	    if($group == null)
	        return $this->clientError($res, new Error(ErrorCodes::CLIENT_BAD_REQUEST, 'The group to join must be included in the request.'));
	    if(isset($data['hidden']))
	        $private = (bool)$data['hidden'];


	    if($group == null)
			return $this->clientError($res, new Error(ErrorCodes::CLIENT_NONEXISTENT_ENTITY,
				'A group could not be located with that ID.'));

	    $personGroup = new PersonGroup();
		$personGroup
			->setGroup($group)
			->setPerson($this->user)
			->setHidden($private)
			->save();

		$group
			->addPersonGroup($personGroup)
			->save();

	    $this->logger->info("[INFO][GROUP] Successfully added the registered user to a group ([{$group->getId()}, {$group->getName()}]: {$this->user->getName()})");
	    return $this->successRender($res);
	}

    /**
     * Performs the standard set of modifications for the Person object, but does NOT commit changes to the database.
     * @param Person $person
     * @param array $data
     * @return Person
     * @throws ImageException
     */
	protected function modifyPerson(Person $person, array $data) : Person {
	    if($person->isNew()){
            if(isset($data['username']))
                $person->setUsername($data['username']);
            if(isset($data['email']))
                $person->setEmail($data['email']);
        }

        if(isset($data['name']))
            $person->setName($data['name']);
        if(isset($data['password']))
            $person->setPassword($data['password']);

        if(isset($data['picture'])) {
	        try {
	        	$original = $person->getPicture();
		        $img = $this->imager->make($data['picture']);
		        $img->fit(300, 300, function(Constraint $constraint) {
			        $constraint->upsize();
		        });

		        $relativePath = DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR . uniqid($person->getId() . '_') . '.jpg';
				$fullPath = $this->ci['assemble']['public_dir'] . $relativePath;
		        $img->save($fullPath);
		        $person->setPicture($relativePath);

		        // Now remove the old picture. Ignore errors on a non-existent file, we don't mind.
		        if(strpos($original, 'default') == false) {
			        @unlink($this->ci['assemble']['public_dir'] . $original);
		        }
	        } catch (NotReadableException $exception){
				throw new ImageException();
	        }
        }

        return $person;
    }

	public function createPerson(Request $req, Response $res, array $args) : ResponseInterface {
		// TODO: User avatar
		$data = $req->getParsedBody();
		if(empty($data['username']))
			return $this->clientError($res, new Error(ErrorCodes::CLIENT_VAGUE_BAD_REGISTRATION,
                'Missing field \'username\''));

		$existing = PersonQuery::create()->findOneByUsername($data['username']);

		if($existing != null)
			return $this->clientError($res, new Error(ErrorCodes::CLIENT_EXISTING_USERNAME));
		if(empty($data['password']) || strlen($data['password']) < 5)
			return $this->clientError($res, new Error(ErrorCodes::CLIENT_VAGUE_BAD_REGISTRATION,
                'Missing field \'password\''));
        if(empty($data['email']))
            return $this->clientError($res, new Error(ErrorCodes::CLIENT_VAGUE_BAD_REGISTRATION,
                'You must provide an email address.'));

        $existing = PersonQuery::create()->findOneByEmail($data['email']);
        if($existing != null)
            return $this->clientError($res, new Error(ErrorCodes::CLIENT_VAGUE_BAD_REGISTRATION,
                'An account with this email address has already been registered.'));

        $new_user = new Person();
		try {
			$this->modifyPerson($new_user, $data)
				->save();

			$this->logger->info("[INFO][USER] Successfully registered/created a user ({$new_user->getId()}, {$new_user->getName()})");
			return $this->successRender($res, ['username' => $data['username'], 'name' => $data['name']]);
		} catch(ImageException $exception) {
			$new_user->save();
			return $this->clientError($res, new Error(ErrorCodes::CLIENT_BAD_IMAGE));
		}
	}

	public function changePerson(Request $req, Response $res, array $args) : ResponseInterface {
		$data = $req->getParsedBody();

		$user = null;
		if(isset($args['userID'])){
			$user = PersonQuery::create()->findOneById($args['userID']);
			if($user == null)
				return $this->clientError($res, new Error(ErrorCodes::CLIENT_NONEXISTENT_ENTITY));
		} else {
			$user = $this->user;
		}
		try {
			$this->modifyPerson($user, $data)
				->save();

			$this->logger->info("[INFO][USER] Successfully modified a user's data ({$user->getId()}, {$user->getName()})");
			return $this->successRender($res);
		} catch(ImageException $exception) {
			$user->save();
			return $this->clientError($res, new Error(ErrorCodes::CLIENT_BAD_IMAGE));
		}
	}

	public function getPersonalFeed(RequestInterface $req, Response $res, array $args) : ResponseInterface {
		/** @var Person $user */
		$user = $this->ci->get('user');
		$personalFeed = PostQuery::create()
			->useGroupQuery()
				->usePersonGroupQuery()
					->filterByPerson($user)
				->endUse()
			->endUse()
			->find();

		$feed = [];

		foreach ($personalFeed as $post) {
			$author = $post->getPerson();
			$feed[] = [
				'id' => $post->getId(),
				'title' => $post->getTitle(),
				'body' => $post->getBody(),
				'time' => $post->getCreatedAt()->getTimestamp(),
				'group' => [
					'id' => $post->getGroupid(),
					'name' => $post->getGroup()->getName()
				],
				'author' => [
					'id' => $author->getId(),
					'username' => $author->getUsername(),
					'name' => $author->getName(),
					'avatar' => $author->getPicture()
				],
			];
		}

		return $this->successRender($res, ['feed' => $feed ]);
	}

	public function leaveGroup(Request $req, Response $res, array $args) : ResponseInterface {
		$data = $req->getParsedBody();

		$user = $this->user;
		if(isset($args['userID'])){
			$user = PersonQuery::create()->findOneById($args['userID']);
			if($user == null)
				return $this->clientError($res, new Error(ErrorCodes::CLIENT_NONEXISTENT_ENTITY,
					'A user with that ID could not be located.'));
		}

		$group = null;

		if(isset($args['groupID'])){
			$group = GroupQuery::create()->findOneById($args['groupID']);
		} else if(isset($data['groupID'])){
			$group = GroupQuery::create()->findOneById($data['groupID']);
		}

		if($group == null)
			return $this->clientError($res, new Error(ErrorCodes::CLIENT_NONEXISTENT_ENTITY,
				'A group with that ID could not be located.'));

		$userInGroup = PersonGroupQuery::create()->filterByGroup($group)->filterByPerson($user)->findOne();

		if($userInGroup == null)
			return $this->clientError($res, new Error(ErrorCodes::CLIENT_NONEXISTENT_ENTITY,
				'This user is not a member of that group.'));
		$group
			->removePerson($user)
			->save();

		$this->logger->info("[INFO][GROUP] Successfully removed a user from a group ([{$group->getId()}, {$group->getName()}] - [{$user->getId()}, {$user->getName()}])");
		return $this->successRender($res);
	}
}