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

namespace Bitrix24\SDK\Services\Biconnector\Dataset\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Carbon\CarbonImmutable;

/**
 * Class DatasetItemResult
 *
 * Field names correspond to the actual API response returned by biconnector.dataset.get / biconnector.dataset.list.
 *
 * @see https://apidocs.bitrix24.com/api-reference/biconnector/dataset/biconnector-dataset-fields.html
 *
 * @property-read int $id
 * @property-read int|null $sourceId
 * @property-read string|null $name
 * @property-read string|null $type
 * @property-read string|null $description
 * @property-read string|null $externalName
 * @property-read string|null $externalCode
 * @property-read int|null $externalId
 * @property-read CarbonImmutable $dateCreate
 * @property-read CarbonImmutable $dateUpdate
 * @property-read int $createdById
 * @property-read int $updatedById
 * @property-read array|null $fields
 */
class DatasetItemResult extends AbstractItem
{
    #[\Override]
    public function __get($offset): mixed
    {
        return match ($offset) {
            'id', 'createdById', 'updatedById' => isset($this->data[$offset]) ? (int)$this->data[$offset] : null,
            'sourceId', 'externalId' => isset($this->data[$offset]) ? (int)$this->data[$offset] : null,
            'dateCreate', 'dateUpdate' => isset($this->data[$offset])
                ? CarbonImmutable::parse($this->data[$offset])
                : null,
            default => $this->data[$offset] ?? null,
        };
    }
}

