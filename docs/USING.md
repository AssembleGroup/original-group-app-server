# Totally Original Group Application

## The API

### Entities

#### User
| Parameter | Type                 | Max size | Required | Notes                                                                        |
|-----------|----------------------|:--------:|:--------:|------------------------------------------------------------------------------|
| name      | string               | 120      | no       | Not required anymore                                                         |
| email     | string               | 120      | yes      | Does not perform any checks on the address                                   |
| username  | string               | 30       | yes      | Must be unique                                                               |
| password  | string               | 25       | yes      | The password will be encrypted, server-side                                  |
| picture   | string/raw image/url | -        | no       | [Supports many formats](http://image.intervention.io/api/make#content)       |

#### Group
| Parameter | Type                 | Max size | Required | Notes                                                                                  |
|-----------|----------------------|:--------:|:--------:|----------------------------------------------------------------------------------------|
| name      | string               |          | yes      | Does not have to be unique.                                                            |
| picture   | string/raw image/url | -        | no       | [Supports many formats](http://image.intervention.io/api/make#content)                 |
| closed    | bool                 | -        | no       | Sets whether or not new users can join.                                                |
| hidden    | bool                 | -        | no       | Sets whether or not this group is publicly viewable.                                   |
| position  | lat/long as string   | -        | no       | The longitude/latitude of the group's [general] position - might change to an address? |

### End Points
The final column shows whether or not this route has been implemented (to at least a partially working degree) as of the latest commit. The parameter list should indicate how much is working.

Where necessary, pagination must be added (or put another way, would have to be added in a real system).

As stated, everything is returned in JSON format.

#####As of v0.2, all parameters are LOWER CASE to fit JSON de facto standards.

#### Users/People
| Path           | METHOD    | Parameters                                                           | Notes                                                                                                                     | ✓ |
|----------------|-----------|----------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------|:-:|
| /              | GET       | -                                                                    | Returns standard response (version, 200 OK etc.)                                                                          | ✓ |
| /register      | POST      | See user table                                                       | You cannot register if you have logged in/submitted HTTP BASIC AUTH.                                                      | ✓ |
| /user          | POST      | -                                                                    | Alias of POST /register                                                                                                   | ✓ |
| /feed          | GET       | -                                                                    | Returns your feed                                                                                                         | ✓ |
| /user          | GET       | -                                                                    | Returns current User data, or error if not logged in.                                                                     | ✓ |
| /user          | PUT/PATCH | Changed fields from user table                                       | For editing your data - only submit the changed/altered fields.                                                           | ✓ |
| /user/feed     | GET       | -                                                                    | Returns your feed                                                                                                         | ✓ |
| /user/groups   | GET       | -                                                                    | Returns a list of your groups [groupID: groupName]                                                                        | ✓ |
| /user/group    | POST      | groupID *(int)*, hidden *(bool)*                                     | Add yourself to a group                                                                                                   | ✓ |
| /user/group    | DELETE    | groupID *(int)*                                                      | Remove yourself from a group                                                                                              | ✓ |
| /user/1        | GET       | -                                                                    | Returns User data for user #1                                                                                             | ✓ |
| /user/1        | PATCH/PUT | Changed fields from user table                                       | Updates user #1's details - changed fields only. For admin use (e.g. removing abusive profile pic etc.). May not be used. | ✓ |
| /user/1/groups | GET       | -                                                                    | Returns *PUBLIC* (i.e. not *HIDDEN*) groups of user #1.                                                                   | ✓ |
| /user/1/group  | POST      | groupID *(int)*, hidden *(bool)*                                     | Deprecated in favour of an invite /group/1/invite (which may not be implemented anyway).                                  | X |

#### Groups
| Path                   | METHOD    | Parameters                       | Notes                                                                                                                                                               | ✓ |
|------------------------|-----------|----------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------|:-:|
| /group                 | POST      | See group table                  | Creates a group with provided parameters.                                                                                                                           | ✓ |
| /group/25              | GET       | -                                | Gets group information for #25, NOT including users (may change).                                                                                                   | ✓ |
| /group/25              | PATCH/PUT | Changed fields from group table  | Updates group information for #25. Only available to staff or owners of groups.                                                                                     |   |
| /group/25              | DELETE    | -                                | Deletes group #25. Only available to staff or owners of groups.                                                                                                     |   |
| /group/25/users        | GET       | -                                | Retrieves all PUBLIC users of group #25                                                                                                                             |   |
| /group/25 /user/8      | DELETE    | -                                | Removes user #8 from group 25. For group owners and admins only.                                                                                                    |   |
| /group/25 /feed/page/2 | GET       | -                                | Retrieves the group feed, page 2. The */page/2* segment is optional. */group/25/feed* returns the first page of posts and is equivilent to */group/25/feed/page/1*. | ✓ |
| /group/25 /feed/post/5 | PATCH/PUT | Changed fields from group table  | Updates the changed fields (post text, title, etc.) of post #5, which has been posted in group #25's feed.                                                          |   |
| /group/25 /feed/post/5 | DELETE    | -                                | Deletes post #5, which has been posted in group #25's feed.                                                                                                         |   |

#### Other/Miscellaneous
| Path              | METHOD | Parameters | Notes                                                                                                                                                          | ✓ |
|-------------------|--------|------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------|:-:|
| /interests/page/2 | GET    | -          | Page 2 of list of interests. The page is optional and can be ommited (so it becomes */interests*, showing the first page - equivilent to */interests/page/1*). |   |
| /interest/5       | GET    | -          | List of groups that are associated with interest #5                                                                                                            |   |
| /logs             | GET    | -          | View the log files! Only available when DEBUG is turned on.                                                                                                    | ✓ |
