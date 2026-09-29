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

namespace Bitrix24\SDK\Services\Catalog\Product\Result;

use Bitrix24\SDK\Core\Response\DTO\ResponseData;

class ProductAddedBatchResult
{
    public function __construct(private readonly ResponseData $responseData)
    {
    }

    public function getResponseData(): ResponseData
    {
        return $this->responseData;
    }

    public function getId(): int
    {
        return (int)$this->extractProduct()['id'];
    }

    public function product(): ProductItemResult
    {
        return new ProductItemResult($this->extractProduct());
    }

    /**
     * catalog.product.add returns the product under the 'element' key, while
     * some portals answer with the 'product' key - support both.
     *
     * @return array<string, mixed>
     */
    private function extractProduct(): array
    {
        $result = $this->responseData->getResult();

        return $result['element'] ?? $result['product'];
    }
}
