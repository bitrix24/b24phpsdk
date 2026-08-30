<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IMBot\Chat\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Carbon\CarbonImmutable;

/**
 * Single chat item returned by imbot.v2.Chat.* methods.
 *
 * @property-read int $id
 * @property-read string $dialogId
 * @property-read string $name
 * @property-read string $description
 * @property-read string $type
 * @property-read string $messageType
 * @property-read int $owner
 * @property-read string $color
 * @property-read string $avatar
 * @property-read bool $extranet
 * @property-read bool $containsCollaber
 * @property-read string $entityType
 * @property-read string $entityId
 * @property-read string $entityData1
 * @property-read string $entityData2
 * @property-read string $entityData3
 * @property-read ?int $diskFolderId
 * @property-read string $role
 * @property-read ?int $parentChatId
 * @property-read ?int $parentMessageId
 * @property-read bool $isNew
 * @property-read string $textFieldEnabled
 * @property-read ?string $backgroundId
 * @property-read ?CarbonImmutable $dateCreate
 * @property-read ?int $lastMessageId
 * @property-read ?int $markedId
 * @property-read int $messageCount
 * @property-read string $public
 * @property-read ?int $unreadId
 * @property-read int $userCounter
 */
class ChatItemResult extends AbstractAnnotatedItem
{
}
