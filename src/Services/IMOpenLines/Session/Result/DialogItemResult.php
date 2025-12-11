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
use Carbon\CarbonImmutable;

/**
 * @property-read string|null $avatar
 * @property-read string $color
 * @property-read CarbonImmutable $dateCreate
 * @property-read string|null $dialogId
 * @property-read string $entityData1
 * @property-read string $entityData2
 * @property-read string $entityData3
 * @property-read string $entityId
 * @property-read string $entityType
 * @property-read bool $extranet
 * @property-read int $id
 * @property-read array $managerList
 * @property-read string $messageType
 * @property-read string $name
 * @property-read int $owner
 * @property-read string $type
 */
class DialogItemResult extends AbstractItem
{
    public function __get($offset)
    {
        return match ($offset) {
            'avatar' => $this->data[$offset] !== '' ? $this->data[$offset] : null,
            'color', 'entityData1', 'entityData2', 'entityData3', 'entityId', 'entityType', 'messageType', 'name', 'type' => (string)$this->data[$offset],
            'dateCreate' => CarbonImmutable::createFromFormat(DATE_ATOM, $this->data['date_create']),
            'dialogId' => $this->data['dialog_id'],
            'extranet' => (bool)$this->data[$offset],
            'id', 'owner' => (int)$this->data[$offset],
            'managerList' => $this->data['manager_list'] ?? [],
            default => $this->data[$offset] ?? null,
        };
    }
}