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

namespace Bitrix24\SDK\Services\Main\Result;

use Bitrix24\SDK\Attributes\OpenApiEntity;
use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Bitrix24\SDK\Services\Main\Service\EventLogSelectBuilder;
use Carbon\CarbonImmutable;
use Darsyn\IP\Version\Multi;

/**
 * @property-read int                  $id
 * @property-read CarbonImmutable|null $timestampX
 * @property-read string|null          $severity
 * @property-read string|null          $auditTypeId
 * @property-read string|null          $moduleId
 * @property-read string|null          $itemId
 * @property-read Multi|null            $remoteAddr
 * @property-read string|null          $userAgent
 * @property-read string|null          $requestUri
 * @property-read string|null          $siteId
 * @property-read int|null             $userId
 * @property-read int|null             $guestId
 * @property-read string|null          $description
 */
#[OpenApiEntity(
    entityKey:     'bitrix.main.eventlogdto',
    selectBuilder: EventLogSelectBuilder::class,
)]
class EventLogItemResult extends AbstractAnnotatedItem
{
}
