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

namespace Bitrix24\SDK\Services\Catalog\ProductProperty\Result;

use Bitrix24\SDK\Core\Result\AddedItemBatchResult;

/**
 * Class AddedProductPropertyBatchResult
 *
 * @package Bitrix24\SDK\Services\Catalog\ProductProperty\Result
 */
class AddedProductPropertyBatchResult extends AddedItemBatchResult
{
    #[\Override]
    public function getId(): int
    {
        return (int)$this->getResponseData()->getResult()['productProperty']['id'];
    }
}
