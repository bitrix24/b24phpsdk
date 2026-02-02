<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\SonetGroup\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class SonetGroupListItemResult
 *
 * Result item for socialnetwork.api.workgroup.list method.
 * This class represents the structure returned by the list method,
 * which differs from the get method (SonetGroupGetItemResult).
 *
 * @property-read int|null $id Group identifier
 * @property-read bool|null $active Whether the group is active (Y/N)
 * @property-read string|null $siteId Identifier of the site to which the group belongs
 * @property-read int|null $subjectId Identifier of the group's subject
 * @property-read string|null $name Group name
 * @property-read string|null $description Group description
 * @property-read string|null $keywords Group tags separated by commas
 * @property-read bool|null $closed Whether the group is archived (Y/N)
 * @property-read bool|null $visible Whether the group is visible in the group list (Y/N)
 * @property-read bool|null $opened Whether the group is open (Y/N)
 * @property-read string|null $dateCreate Date of group creation
 * @property-read string|null $dateUpdate Date of group update
 * @property-read string|null $dateActivity Date of last activity in the group
 * @property-read int|null $imageId Identifier of the group's user avatar in the b_file table
 * @property-read string|null $avatarType Type of the last set system avatar
 * @property-read int|null $ownerId Identifier of the group owner
 * @property-read string|null $initiatePerms Who has the right to invite users to the group (A/E/K)
 * @property-read int|null $numberOfMembers Number of group members
 * @property-read int|null $numberOfModerators Number of group moderators
 * @property-read bool|null $project Whether the group is a project (Y/N)
 * @property-read string|null $projectDateStart Project start date
 * @property-read string|null $projectDateFinish Project end date
 * @property-read string|null $searchIndex Index, keywords for searching the group
 * @property-read bool|null $landing Whether the group is a publication group (Y/N)
 * @property-read int|null $scrumMasterId Identifier of the scrum master
 * @property-read int|null $scrumSprintDuration Duration of the scrum sprint in seconds
 * @property-read string|null $scrumTaskResponsible Default assignee when creating tasks
 * @property-read string|null $type Type of group (group/project/scrum/collab)
 * @property-read string|null $spamPerms Who has the right to send messages to group members
 * @property-read string|null $subjectName Subject name
 * @property-read string|null $image Image URL
 * @property-read bool|null $isExtranet Whether the group is extranet
 */
class SonetGroupListItemResult extends AbstractItem
{
}
