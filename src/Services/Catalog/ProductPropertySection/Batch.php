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

namespace Bitrix24\SDK\Services\Catalog\ProductPropertySection;

use Bitrix24\SDK\Core\Response\DTO\ResponseData;

/**
 * Class Batch
 *
 * Overrides base Batch to handle differences in catalog.productPropertySection.* REST methods:
 * - section settings have no own id, they are identified by the 'propertyId' key
 *   instead of the uppercase 'ID' key used by default in the base Batch class
 * - catalog.productPropertySection.list wraps list items under the 'productPropertySections' key
 *   instead of returning a flat array in 'result', as the base class assumes for non-CRM methods
 *
 * @see https://apidocs.bitrix24.com/api-reference/catalog/product-property-section/catalog-product-property-section-list.html
 */
class Batch extends \Bitrix24\SDK\Core\Batch
{
    /**
     * Determines the ID key - 'propertyId' for catalog.productPropertySection.* methods
     */
    #[\Override]
    protected function determineKeyId(string $apiMethod, ?array $additionalParameters): string
    {
        return 'propertyId';
    }

    /**
     * Extracts list items from the 'productPropertySections' key of the batch/list result
     */
    #[\Override]
    protected function extractElementsFromBatchResult(ResponseData $responseData, bool $isCrmItemsInBatch): array
    {
        return $responseData->getResult()['productPropertySections'] ?? [];
    }
}
