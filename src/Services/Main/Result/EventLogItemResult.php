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

use Bitrix24\SDK\Core\Result\AbstractItem;
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
class EventLogItemResult extends AbstractItem
{
    /**
     * @param int|string $offset
     *
     * @return int|CarbonImmutable|mixed|null
     */
    #[\Override]
    public function __get($offset)
    {
        return match ($offset) {
            'id' => (int)$this->data[$offset],
            'userId', 'guestId' => ($this->data[$offset] !== null && $this->data[$offset] !== '')
                ? (int)$this->data[$offset]
                : null,
            'timestampX' => ($this->data[$offset] !== '' && $this->data[$offset] !== null)
                ? CarbonImmutable::createFromFormat(DATE_ATOM, $this->data[$offset])
                : null,
            'remoteAddr' => ($this->data[$offset] !== '' && $this->data[$offset] !== null)
                ? Multi::factory($this->data[$offset])
                : null,
            default => $this->data[$offset] ?? null,
        };
    }
}
