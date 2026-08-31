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

namespace Bitrix24\SDK\Services\Catalog\PriceTypeGroup\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read int  $id
 * @property-read int  $catalogGroupId
 * @property-read int  $groupId
 * @property-read bool $access
 */
class PriceTypeGroupItemResult extends AbstractItem
{
    /**
     * @param int|string $offset
     *
     * @return bool|mixed|null
     */
    public function __get($offset)
    {
        if ($offset === 'access') {
            return $this->data[$offset] === 'Y';
        }

        return $this->data[$offset] ?? null;
    }
}
