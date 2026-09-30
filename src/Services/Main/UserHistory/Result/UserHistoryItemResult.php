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

namespace Bitrix24\SDK\Services\Main\UserHistory\Result;

use Bitrix24\SDK\Attributes\OpenApiEntity;
use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Bitrix24\SDK\Services\Main\UserHistory\Service\UserHistorySelectBuilder;
use Carbon\CarbonImmutable;

/**
 * @property-read int|null               $id
 * @property-read int|null               $userId
 * @property-read int|null               $eventType
 * @property-read CarbonImmutable|null   $dateInsert
 * @property-read string|null            $remoteAddr
 * @property-read string|null            $userAgent
 * @property-read string|null            $requestUri
 * @property-read int|null               $updatedById
 * @property-read string|null            $field
 */
#[OpenApiEntity('bitrix.main.historydto', selectBuilder: UserHistorySelectBuilder::class)]
class UserHistoryItemResult extends AbstractAnnotatedItem
{
}
