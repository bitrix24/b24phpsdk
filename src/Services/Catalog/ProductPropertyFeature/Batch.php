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

namespace Bitrix24\SDK\Services\Catalog\ProductPropertyFeature;

use Bitrix24\SDK\Core\Response\DTO\ResponseData;

/**
 * Class Batch
 *
 * Overrides base Batch to handle differences in catalog.productPropertyFeature.* REST methods:
 * - the id key is lowercase 'id', not 'ID' as assumed by the base class default
 * - catalog.productPropertyFeature.list wraps list items under the 'productPropertyFeatures' key
 *   instead of returning a flat array in 'result', as the base class assumes for non-CRM methods
 *
 * @see https://apidocs.bitrix24.com/api-reference/catalog/product-property-feature/catalog-product-property-feature-list.html
 */
class Batch extends \Bitrix24\SDK\Core\Batch
{
    /**
     * Determines the ID key — lowercase 'id' for catalog.productPropertyFeature.*
     */
    #[\Override]
    protected function determineKeyId(string $apiMethod, ?array $additionalParameters): string
    {
        return 'id';
    }

    /**
     * Extracts list items from the 'productPropertyFeatures' key of the batch/list result
     */
    #[\Override]
    protected function extractElementsFromBatchResult(ResponseData $responseData, bool $isCrmItemsInBatch): array
    {
        return $responseData->getResult()['productPropertyFeatures'] ?? [];
    }
}
