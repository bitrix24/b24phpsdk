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

namespace Bitrix24\SDK\Services\Catalog\Extra\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;
use MoneyPHP\Percentage\Percentage;

/**
 * @property-read int        $id
 * @property-read string     $name
 * @property-read Percentage $percentage
 */
class ExtraItemResult extends AbstractItem
{
    /**
     * @param int|string $offset
     */
    public function __get($offset): mixed
    {
        if ($offset === 'percentage') {
            return new Percentage((string)$this->data[$offset]);
        }

        return parent::__get($offset);
    }
}
