<?php

namespace Assemble\Models;

use Assemble\Models\Base\Group as BaseGroup;

/**
 * Skeleton subclass for representing a row from the 'group' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class Group extends BaseGroup {
	public function isViewable($user = null): bool {
		if(!$this->isHidden()) {
			// The group isn't hidden - it's viewable.
			return true;
		}
		if($user === -1 || !is_a($user, Person::class) ) {
			// The group is hidden and the user isn't logged in - it's not viewable.
			return false;
		}
		else {
			// OK, the group is hidden, but the user is logged in; let's check if they are allowed to see it.
			$isUserInGroup = PersonGroupQuery::create()->filterByPerson($user);
			if($this->getPeople($isUserInGroup) == null){
				return false;
			}
		}
		return true;
	}
}
