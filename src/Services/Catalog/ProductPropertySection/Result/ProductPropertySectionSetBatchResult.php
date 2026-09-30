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

namespace Bitrix24\SDK\Services\Catalog\ProductPropertySection\Result;

use Bitrix24\SDK\Core\Response\DTO\ResponseData;

class ProductPropertySectionSetBatchResult
{
    public function __construct(private readonly ResponseData $responseData)
    {
    }

    public function getResponseData(): ResponseData
    {
        return $this->responseData;
    }

    public function getPropertyId(): int
    {
        return (int)$this->responseData->getResult()['productPropertySection']['propertyId'];
    }

    public function productPropertySection(): ProductPropertySectionItemResult
    {
        return new ProductPropertySectionItemResult($this->responseData->getResult()['productPropertySection']);
    }
}
