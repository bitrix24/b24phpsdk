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

namespace Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read int    $id
 * @property-read int    $propertyId
 * @property-read string $moduleId
 * @property-read string $featureId
 * @property-read bool   $isEnabled
 */
class ProductPropertyFeatureItemResult extends AbstractItem
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
                if ($this->data[$offset] !== '' && $this->data[$offset] !== null) {
                    return (int)$this->data[$offset];
                }

                return null;
            case 'isEnabled':
                return $this->data[$offset] === 'Y';
            default:
                return $this->data[$offset] ?? null;
        }
    }
}
