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
 * Class UserGroupItemResult
 *
 * @property-read string|null $GROUP_ID Group identifier
 * @property-read string|null $GROUP_NAME Group name
 * @property-read string|null $ROLE User's role in the group (A=owner, E=moderator, K=member)
 * @property-read string|null $GROUP_IMAGE_ID ID of the group's image
 * @property-read string|null $GROUP_IMAGE URL of the group's image
 */
class UserGroupItemResult extends AbstractItem
{
}
