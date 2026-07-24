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

namespace Bitrix24\SDK\Services\Catalog\ProductPropertyEnum\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read int $id
 * @property-read int $propertyId
 * @property-read string $value
 * @property-read string $xmlId
 * @property-read bool|null $def
 * @property-read int|null $sort
 */
class ProductPropertyEnumItemResult extends AbstractItem
{
    /**
     * @param int|string $offset
     *
     * @return bool|int|mixed|null
     */
    public function __get($offset)
    {
        switch ($offset) {
            case 'id':
            case 'propertyId':
                return (int)$this->data[$offset];
            case 'def':
                if ($this->data[$offset] !== null) {
                    return $this->data[$offset] === 'Y';
                }

                return null;
            case 'sort':
                if ($this->data[$offset] !== null) {
                    return (int)$this->data[$offset];
                }

                return null;
        }

        return $this->data[$offset] ?? null;
    }
}
