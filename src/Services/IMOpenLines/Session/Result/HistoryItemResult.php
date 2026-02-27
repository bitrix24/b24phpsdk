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

namespace Bitrix24\SDK\Services\IMOpenLines\Session\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read int $chatId
 * @property-read string $canJoin
 * @property-read string $canVoteHead
 * @property-read int $sessionId
 * @property-read int $sessionVoteHead
 * @property-read string|null $sessionCommentHead
 * @property-read string $userId
 * @property-read array $message
 * @property-read array $usersMessage
 * @property-read array $users
 * @property-read array $openlines
 * @property-read array $userInGroup
 * @property-read array $woUserInGroup
 * @property-read array $chat
 * @property-read array $userBlockChat
 * @property-read array $userInChat
 * @property-read array $files
 */
class HistoryItemResult extends AbstractItem
{
}
