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

namespace Bitrix24\SDK\Services\Biconnector\Source\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Carbon\CarbonImmutable;

/**
 * Class SourceItemResult
 *
 * Field names correspond to the actual API response returned by biconnector.source.get / biconnector.source.list.
 *
 * @see https://apidocs.bitrix24.com/api-reference/biconnector/source/biconnector-source-fields.html
 *
 * @property-read int $id
 * @property-read string $title
 * @property-read string|null $type
 * @property-read string|null $code
 * @property-read string|null $description
 * @property-read bool|null $active
 * @property-read CarbonImmutable $dateCreate
 * @property-read CarbonImmutable $dateUpdate
 * @property-read int $createdById
 * @property-read int $updatedById
 * @property-read int $connectorId
 * @property-read array|null $settings
 */
class SourceItemResult extends AbstractItem
{
    #[\Override]
    public function __get($offset): mixed
    {
        return match ($offset) {
            'id', 'createdById', 'updatedById', 'connectorId' => isset($this->data[$offset]) ? (int)$this->data[$offset] : null,
            'active' => isset($this->data[$offset]) ? (bool)$this->data[$offset] : null,
            'dateCreate', 'dateUpdate' => isset($this->data[$offset])
                ? CarbonImmutable::parse($this->data[$offset])
                : null,
            default => $this->data[$offset] ?? null,
        };
    }
}
