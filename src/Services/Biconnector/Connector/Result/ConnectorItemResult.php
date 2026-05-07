<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <titarx@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Biconnector\Connector\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class ConnectorItemResult
 *
 * @property-read int $id
 * @property-read string $name
 * @property-read string $code
 * @property-read string|null $description
 * @property-read string|null $pictureUrl
 * @property-read array|null $settings
 * @property-read bool $isEnabled
 */
class ConnectorItemResult extends AbstractItem
{
    #[\Override]
    public function __get($offset): mixed
    {
        return match ($offset) {
            'isEnabled' => isset($this->data[$offset]) ? (bool)$this->data[$offset] : null,
            'id'        => isset($this->data[$offset]) ? (int)$this->data[$offset] : null,
            default     => $this->data[$offset] ?? null,
        };
    }
}
