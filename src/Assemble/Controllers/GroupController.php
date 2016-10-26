<?php
/**
 * User: Peter Clotworthy
 * Date: 12/10/16
 * Time: 02:53
 */

namespace Assemble\Controllers;

use Assemble\Models\Base\PostQuery;
use Assemble\Models\Group;
use Assemble\Models\GroupQuery;
use Assemble\Models\Interest;
use Assemble\Models\InterestQuery;
use Assemble\Models\Map\GroupTableMap;
use Assemble\Models\Map\InterestTableMap;
use Assemble\Models\Post;
use Exception;
use Intervention\Image\Constraint;
use Intervention\Image\Exception\NotReadableException;
use Propel\Runtime\Propel;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class GroupController extends Controller {
    /**
     * Returns a JSON array of a specificed group.
     * @param RequestInterface $req
     * @param Response $res
     * @param array $args
     * @return ResponseInterface
     */
    public function getGroup(RequestInterface $req, Response $res, array $args): ResponseInterface {
		$group = GroupQuery::create()->findOneById($args['groupID']);
		if($group == null || !$group->isViewable($this->ci->user))
			return $this->clientError($res, new Error(ErrorCodes::CLIENT_NONEXISTENT_ENTITY));

		$data = $group->toArray(GroupTableMap::TYPE_PHPNAME, true, [], true);
		unset($data['CreatedAt']);
		unset($data['UpdatedAt']);
		$data = array_change_key_case($data);
        $data['createdAt'] = $group->getCreatedAt()->getTimestamp();
        $data['updatedAt'] = $group->getUpdatedAt()->getTimestamp();

        foreach($group->getPeople() as $person){
            $data['members'][] = [
	            'id' => $person->getId(),
            	'username' => $person->getUsername(),
	            'name' => $person->getName(),
	            'avatar' => $person->getPicture()
            ];
        }

		return $this->successRender($res, $data);
	}

    /**
     * Returns a JSON array of posts specific to one group.
     * @param RequestInterface $req
     * @param Response $res
     * @param array $args
     * @return ResponseInterface
     */
    public function getGroupFeed(RequestInterface $req, Response $res, array $args): ResponseInterface {
		$group = GroupQuery::create()->findOneById($args['groupID']);

		if($group == null || !$group->isViewable($this->ci->user))
			return $this->clientError($res, new Error(ErrorCodes::CLIENT_NONEXISTENT_ENTITY));

		$posts = PostQuery::create()->filterByGroup($group)->lastCreatedFirst()->paginate($args['page'] ?? 1, $this->ci['assemble']['feed_posts_per_page']);
		$feed = [];

		foreach ($posts as $post){
			$author = $post->getPerson();
			$feed[] = [
				'id' => $post->getId(),
				'title' => $post->getTitle(),
				'body' => $post->getBody(),
				'createdAt' => $post->getCreatedAt()->getTimestamp(),
				'updatedAt' => $post->getUpdatedAt()->getTimestamp(),
				'author' => [
					'id' => $author->getId(),
					'username' => $author->getUsername(),
					'name' => $author->getName(),
					'avatar' => $author->getPicture()
				]
			];
		}

		return $this->successRender($res, ['group' => $group->getId(), 'feed' => $feed ]);
	}

    public function postToGroupFeed(Request $req, Response $res, array $args) : ResponseInterface {
        $data = $req->getParsedBody();

        if(!isset($data['title']))
            return $this->clientError($res, new Error(ErrorCodes::CLIENT_VAGUE_BAD_POST_CREATION, 'You must set a post title.'));
        if(!isset($data['body']))
            return $this->clientError($res, new Error(ErrorCodes::CLIENT_VAGUE_BAD_POST_CREATION, 'You must set a post body.'));

        $group = GroupQuery::create()->findOneById($args['groupID']);
        if($group == null)
            return $this->clientError($res, new Error(ErrorCodes::CLIENT_NONEXISTENT_ENTITY, 'A group with that ID could not be found.'));

        $post = new Post();
        $post
            ->setPerson($this->ci['user'])
            ->setGroup($group)
            ->setTitle($data['title'])
            ->setBody($data['body'])
            ->save();

        return $this->successRender($res, ['post' => ['id' => $post->getId()]]);
    }

    /**
     * Using a data array, checks for the existence of named keys and assigns them to an existing
     * group (in memory). It does NOT perform save().
     * @throws Exception
     * @param Group $group
     * @param array $data
     * @return Group
     */
    protected function modifyGroup(Group $group, array $data) : Group {
	    if(isset($data['name']))
            $group->setName($data['name']);
        if(isset($data['position']) && is_numeric($data['position'][0]) && is_numeric($data['position'][1]))
            $group->setPosition([$data['position'][0], $data['position'][1]]);
        if(isset($data['hidden']))
            $group->setHidden(boolval($data['hidden']));

        if(isset($data['closed']))
            $group->setClosed(boolval($data['closed']));

        if(isset($data['picture'])){
	        try {
		        $original = $group->getPicture();
		        $img = $this->imager->make($data['picture']);
		        $img->fit(300, 300, function(Constraint $constraint) {
			        $constraint->upsize();
		        });

		        $relativePath = DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'groups' . DIRECTORY_SEPARATOR . uniqid($group->getId() . '_') . '.jpg';
		        $fullPath = $this->ci['assemble']['public_dir'] . $relativePath;
		        $img->save($fullPath);
		        $group->setPicture($relativePath);

		        // Now remove the old picture. Ignore errors on a non-existent file, we don't mind.
		        if(strpos($original, 'default') == false) {
			        @unlink($this->ci['assemble']['public_dir'] . $original);
		        }
	        } catch (NotReadableException $exception){
		        throw new ImageException();
	        }
        }

        if(isset($data['interests'])) {
            $interests = [];
            $con = Propel::getWriteConnection(InterestTableMap::DATABASE_NAME);
            $con->beginTransaction();
            try {
                if (is_array($data['interests'])) {
                    foreach ($data['interests'] as $interest) {
                        $existing = InterestQuery::create()->findOneByName($interest);
                        if ($existing == null) {
                            $existing = new Interest();
                            $existing
                                ->setName($interest)
                                ->save();
                        }
                        $interests[] = $interest;
                    }

                } else if (is_string($data['interests'])) {
                    $existing = InterestQuery::create()->findOneByName($data['interests']);
                    if ($existing == null) {
                        $existing = new Interest();
                        $existing
                            ->setName($data['interests'])
                            ->save();
                    }
                    $interests[] = $existing;
                }

                foreach($interests as $interest){
                    $group->addInterest($interest);
                }
            } catch(Exception $e) {
                $con->rollBack();
            }
            $con->commit();
        }

	    return $group;
    }

    /**
     * Creates a new group.
     * @param Request $req
     * @param Response $res
     * @param array $args
     * @return ResponseInterface
     */
    public function createGroup(Request $req, Response $res, array $args): ResponseInterface {
		$data = $req->getParsedBody();
		if(!isset($data['name']))
			return $this->clientError($res, new Error(ErrorCodes::CLIENT_VAGUE_BAD_GROUP_CREATION, 'You must set a group name.'));
		//$existingGroup = GroupQuery::create()->findOneByName($data['name']);
		// Duplicate names are probably going to be fine - especially if you can reveal the existence of a hidden group.

		$newGroup = new Group();

        $this->modifyGroup($newGroup, $data)
			->setName($data['name'])
            ->addPerson($this->user)
			->save();

		return $this->successRender($res);
	}

    /**
     * Changes an existing group.
     * @param Request $req
     * @param Response $res
     * @param array $args
     * @return ResponseInterface
     */
    public function changeGroup(Request $req, Response $res, array $args): ResponseInterface {
		$data = $req->getParsedBody();
		$group = GroupQuery::create()->findOneById($args['groupID']);

		if($group == null || !$group->isViewable($this->ci->user))
			return $this->clientError($res, new Error(ErrorCodes::CLIENT_NONEXISTENT_ENTITY));

        $this->modifyGroup($group, $data)
            ->save();

        return $this->successRender($res);
	}

	public function removePersonFromGroup(RequestInterface $req, Response $res, array $args): ResponseInterface {
		return $res;
	}
}