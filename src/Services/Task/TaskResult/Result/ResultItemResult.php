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

namespace Bitrix24\SDK\Services\Task\TaskResult\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Carbon\CarbonImmutable;

/**
 * Class ResultItemResult
 *
 * @property-read int $id
 * @property-read int $taskId
 * @property-read int $commentId
 * @property-read int $createdBy
 * @property-read CarbonImmutable $createdAt
 * @property-read CarbonImmutable $updatedAt
 * @property-read int $status
 * @property-read string $text
 * @property-read string|null $formattedText
 * @property-read array|null $files
 */
class ResultItemResult extends AbstractItem
{
}
