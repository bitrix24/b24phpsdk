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

namespace Bitrix24\SDK\Services\Biconnector\Connector\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Carbon\CarbonImmutable;

/**
 * Class ConnectorItemResult
 *
 * Field names correspond to the actual API response returned by biconnector.connector.get / biconnector.connector.list.
 *
 * @see https://apidocs.bitrix24.com/api-reference/biconnector/connector/biconnector-connector-fields.html
 *
 * @property-read int $id
 * @property-read string $title
 * @property-read string $logo
 * @property-read string|null $description
 * @property-read int|null $sort
 * @property-read string|null $urlCheck
 * @property-read string|null $urlData
 * @property-read string|null $urlTableList
 * @property-read string|null $urlTableDescription
 * @property-read array|null $settings
 * @property-read bool|null $supportMapping
 * @property-read CarbonImmutable $dateCreate
 */
class ConnectorItemResult extends AbstractItem
{
    #[\Override]
    public function __get($offset): mixed
    {
        return match ($offset) {
            'id', 'sort' => isset($this->data[$offset]) ? (int)$this->data[$offset] : null,
            'supportMapping' => isset($this->data[$offset]) ? (bool)$this->data[$offset] : null,
            'dateCreate' => isset($this->data[$offset])
                ? CarbonImmutable::parse($this->data[$offset])
                : null,
            default => $this->data[$offset] ?? null,
        };
    }
}
