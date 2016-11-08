<?php

namespace Assemble\Models;

use Assemble\Models\Base\Person as BasePerson;

/**
 * Skeleton subclass for representing a row from the 'person' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class Person extends BasePerson {
    /**
     * Overrides the base setPassword() to create a hashed version.
     * @param string $v
     * @return $this|Person
     */
    public function setPassword($v) {
        $hp = password_hash($v, PASSWORD_DEFAULT);
        parent::setPassword($hp);
        return $this;
    }

    public function getDetailsArray(bool $showPrivate = false): array {
		$payload = [];
		$payload['username'] = $this->getUsername();
		$payload['id'] = $this->getId();
		$payload['name'] = $this->getName();
		$payload['picture'] = $this->getPicture();
		$payload['privilege'] = $this->getPrivilege();
		$payload['createdAt'] = $this->getCreatedAt()->getTimestamp();
		$payload['updatedAt'] = $this->getUpdatedAt()->getTimestamp();

		$publicCriteria = GroupQuery::create()
			->usePersonGroupQuery()
			->filterByHidden(false)
			->endUse();

		if($showPrivate)
			$publicCriteria = null;


		$arrGroup = [];
		if(!$showPrivate) {
			foreach ($this->getGroups($publicCriteria) as $group) {
				$arrGroup[] = [
					'id' => $group->getId(),
					'name' => $group->getName()
				];
			}
		} else {
			foreach ($this->getGroups($publicCriteria) as $group) {
				$arrGroup[] = [
					'id' => $group->getId(),
					'name' => $group->getName(),
					'hidden' => PersonGroupQuery::create()
						->filterByGroup($group)
						->filterByPerson($this)
						->findOne()
						->getHidden()
				];
			}
		}

		$payload['groups'] = $arrGroup;

		return $payload;
	}
}
