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
 * Class SonetGroupGetItemResult
 *
 * @property-read int|null $ID Group identifier
 * @property-read bool|null $ACTIVE Whether the group is active (Y/N)
 * @property-read string|null $SITE_ID Identifier of the site to which the group belongs
 * @property-read int|null $SUBJECT_ID Identifier of the group's subject
 * @property-read string|null $NAME Group name
 * @property-read string|null $DESCRIPTION Group description
 * @property-read string|null $KEYWORDS Group tags separated by commas
 * @property-read bool|null $CLOSED Whether the group is archived (Y/N)
 * @property-read bool|null $VISIBLE Whether the group is visible in the group list (Y/N)
 * @property-read bool|null $OPENED Whether the group is open (Y/N)
 * @property-read string|null $DATE_CREATE Date of group creation
 * @property-read string|null $DATE_UPDATE Date of group update
 * @property-read string|null $DATE_ACTIVITY Date of last activity in the group
 * @property-read int|null $IMAGE_ID Identifier of the group's user avatar in the b_file table
 * @property-read string|null $AVATAR_TYPE Type of the last set system avatar
 * @property-read int|null $OWNER_ID Identifier of the group owner
 * @property-read string|null $INITIATE_PERMS Who has the right to invite users to the group (A/E/K)
 * @property-read int|null $NUMBER_OF_MEMBERS Number of group members
 * @property-read int|null $NUMBER_OF_MODERATORS Number of group moderators
 * @property-read bool|null $PROJECT Whether the group is a project (Y/N)
 * @property-read string|null $PROJECT_DATE_START Project start date
 * @property-read string|null $PROJECT_DATE_FINISH Project end date
 * @property-read string|null $SEARCH_INDEX Index, keywords for searching the group
 * @property-read bool|null $LANDING Whether the group is a publication group (Y/N)
 * @property-read int|null $SCRUM_MASTER_ID Identifier of the scrum master
 * @property-read int|null $SCRUM_SPRINT_DURATION Duration of the scrum sprint in seconds
 * @property-read string|null $SCRUM_TASK_RESPONSIBLE Default assignee when creating tasks
 * @property-read string|null $TYPE Type of group (group/project/scrum/collab)
 * @property-read array|null $MEMBERS Identifiers of group members
 * @property-read int|null $CHAT_ID Identifier of the group chat
 * @property-read string|null $DIALOG_ID Identifier of the group dialog
 * @property-read array|null $ORDINARY_MEMBERS Array of identifiers of group users who are not owners or moderators
 * @property-read array|null $INVITED_MEMBERS Array of identifiers of portal users who were invited to the group but have not yet accepted
 * @property-read array|null $MODERATOR_MEMBERS Array of identifiers of group members with the role of moderator
 * @property-read array|null $SITE_IDS List of identifiers of sites to which the group belongs
 * @property-read string|null $AVATAR URL of the group's compressed user avatar
 * @property-read array|null $AVATAR_TYPES Object containing group avatars
 * @property-read array|null $AVATAR_DATA Information about the group's avatar
 * @property-read array|null $OWNER_DATA Information about the group owner
 * @property-read array|null $SUBJECT_DATA Information about the group's subject
 * @property-read array|null $TAGS Group tags in array format
 * @property-read array|null $ACTIONS Data about available operations for the current user on the group
 * @property-read array|null $USER_DATA Information about the current user regarding the group
 * @property-read array|null $DEPARTMENTS Array of identifiers of departments added to the group
 * @property-read bool|null $IS_PIN Whether the group is pinned by the current user
 * @property-read string|null $PRIVACY_CODE Privacy level of the group (open/closed/secret)
 * @property-read array|null $LIST_OF_MEMBERS Array with information about group users
 * @property-read array|null $FEATURES Array with information about the group's features
 * @property-read array|null $LIST_OF_MEMBERS_AWAITING_INVITE Information about users awaiting confirmation to join the group
 * @property-read array|null $GROUP_MEMBERS_LIST Information about users associated with the group
 * @property-read array|null $COUNTERS Counters for pending invitations and requests
 * @property-read int|null $EFFICIENCY Group efficiency
 * @property-read array|null $ADDITIONAL_DATA Additional data for the current user
 * @property-read string|null $SUBJECT_NAME Subject name
 * @property-read string|null $IMAGE Image URL
 * @property-read bool|null $IS_EXTRANET Whether the group is extranet
 * @property-read string|null $GROUP_ID Group identifier (for user groups list)
 * @property-read string|null $GROUP_NAME Group name (for user groups list)
 * @property-read string|null $ROLE User's role in the group (for user groups list)
 */
class SonetGroupGetItemResult extends AbstractItem
{
}
