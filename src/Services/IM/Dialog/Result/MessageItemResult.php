<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Dialog\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read int $id
 * @property-read int|null $chatId
 * @property-read int $chat_id
 * @property-read int|null $authorId
 * @property-read int $author_id
 * @property-read string $date
 * @property-read string $text
 * @property-read bool|null $isSystem
 * @property-read bool $unread
 * @property-read string|null $uuid
 * @property-read array|null $forward
 * @property-read array|null $replaces
 * @property-read array $params
 * @property-read bool|null $viewedByOthers
 * @property-read bool|null $viewed
 * @property-read string|null $disappearing_date
 */
class MessageItemResult extends AbstractItem
{
}
