# Totally Original Group Application
#### (The backend server part)

Serves the JSON data for the groups application to digest.


### API Routes
The final column shows whether or not this route has been implemented (to at least a partially working degree) as of the latest commit. The parameter list should indicate how much is working.

Where necessary, pagination must be added (or put another way, would have to be added in a real system).

As stated, everything is returned in JSON format.


#### Users/People
| Path           | METHOD    | Parameters                                                | Notes                                                                                                                     | ✓ |
|----------------|-----------|-----------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------|---|
| /              | GET       | -                                                         | Returns standard response (version, 200 OK etc.)                                                                          | ✓ |
| /register      | POST      | Username *(string)*, Password *(string)*, Name *(string)* | You cannot register if you have logged in/submitted HTTP BASIC AUTH. TODO: Currently ignores other fields (Avatar, etc.). | ✓ |
| /user          | POST      | -                                                         | Same as /register                                                                                                         | ✓ |
| /user          | GET       | -                                                         | Returns current User data, or error if not logged in.                                                                     | ✓ |
| /user          | PUT/PATCH | Changed fields                                            | For editing your data - only submit the changed/altered fields.                                                           |   |
| /user/feed     | GET       | -                                                         | Returns your feed                                                                                                         |   |
| /user/groups   | GET       | -                                                         | Returns a list of your groups [groupID: groupName]                                                                        | ✓ |
| /user/group    | POST      | GroupID *(int)*, Hidden *(bool)*                          | Add yourself to a group                                                                                                   |   |
| /user/group    | DELETE    | GroupID *(int)*                                           | Remove yourself from a group                                                                                              |   |
| /user/1        | GET       | -                                                         | Returns User data for user #1                                                                                             | ✓ |
| /user/1        | PATCH/PUT | Changed fields                                            | Updates user #1's details - changed fields only. For admin use (e.g. removing abusive profile pic etc.). May not be used. |   |
| /user/1/groups | GET       | -                                                         | Returns *PUBLIC* (i.e. not *HIDDEN*) groups of user #1.                                                                   | ✓ |
| /user/1/group  | POST      | GroupID *(int)*, Hidden *(bool)*                          | Add a specific user to a group. For invites? May not be used.                                                             |   |

#### Groups
| Path                   | METHOD    | Parameters      | Notes                                                                                                                                                               | ✓ |
|------------------------|-----------|-----------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------|---|
| /group                 | POST      | Name *(string)* | Creates a group with provided parameters. TODO: Ignores other fields (e.g. Group picture, etc.).                                                                    | ✓ |
| /group/25              | GET       | -               | Gets group information for #25, NOT including users (may change).                                                                                                   | ✓ |
| /group/25              | PATCH/PUT | Changed fields  | Updates group information for #25. Only available to staff or owners of groups.                                                                                     |   |
| /group/25              | DELETE    | -               | Deletes group #25. Only available too staff or owners of groups.                                                                                                    |   |
| /group/25/users        | GET       | -               | Retrieves all PUBLIC users of group #25                                                                                                                             |   |
| /group/25 /user/8      | DELETE    | -               | Removes user #8 from group 25. For group owners and admins only.                                                                                                    |   |
| /group/25 /feed/page/2 | GET       | -               | Retrieves the group feed, page 2. The */page/2* segment is optional. */group/25/feed* returns the first page of posts and is equivilent to */group/25/feed/page/1*. |   |
| /group/25 /feed/post/5 | PATCH/PUT | Changed fields  | Updates the changed fields (post text, title, etc.) of post #5, which has been posted in group #25's feed.                                                          |   |
| /group/25 /feed/post/5 | DELETE    | -               | Deletes post #5, which has been posted in group #25's feed.                                                                                                         |   |

#### Other/Miscellaneous
| Path              | METHOD | Parameters | Notes                                                                                                                                                          | ✓ |
|-------------------|--------|------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------|---|
| /interests/page/2 | GET    | -          | Page 2 of list of interests. The page is optional and can be ommited (so it becomes */interests*, showing the first page - equivilent to */interests/page/1*). |   |
| /interest/5       | GET    | -          | List of groups that are associated with interest #5                                                                                                            |   |
