<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Task\Commentitem\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Carbon\CarbonImmutable;

/**
 * Class CommentitemItemResult
 *
 * @property-read int $ID
 * @property-read int $TASK_ID
 * @property-read int $AUTHOR_ID
 * @property-read string $AUTHOR_NAME
 * @property-read string $AUTHOR_EMAIL
 * @property-read CarbonImmutable|null $POST_DATE
 * @property-read string $POST_MESSAGE
 * @property-read array|null $ATTACHED_OBJECTS
 * @property-read bool|null $POST_MESSAGE_HTML
 */
class CommentitemItemResult extends AbstractItem
{
}
