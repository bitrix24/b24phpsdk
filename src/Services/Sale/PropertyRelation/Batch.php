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

namespace Bitrix24\SDK\Services\Sale\PropertyRelation;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Generator;

/**
 * Class Batch
 *
 * Overrides base Batch to handle differences in sale.propertyRelation.* REST methods:
 * - list results are wrapped in 'propertyRelations' key
 * - property relations have no own identifier, so the list is read with pagination by 'start' parameter
 *
 * @see https://apidocs.bitrix24.com/api-reference/sale/property-relation/sale-property-relation-list.html
 */
class Batch extends \Bitrix24\SDK\Core\Batch
{
    /**
     * Extracts elements from batch result, unwrapping the 'propertyRelations' key
     */
    #[\Override]
    protected function extractElementsFromBatchResult(ResponseData $responseData, bool $isCrmItemsInBatch): array
    {
        $resultData = $responseData->getResult();

        if (array_key_exists('propertyRelations', $resultData) && is_array($resultData['propertyRelations'])) {
            return $resultData['propertyRelations'];
        }

        return [];
    }

    /**
     * Get traversable list of property relations
     *
     * Property relations have no own identifier, so the list is always read with pagination by 'start' parameter.
     *
     * @param array<string,string> $order
     * @param array<string,mixed> $filter
     * @param array<string,mixed> $select
     *
     * @return Generator<mixed>
     * @throws BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    #[\Override]
    public function getTraversableList(
        string $apiMethod,
        ?array $order = [],
        ?array $filter = [],
        ?array $select = [],
        ?int $limit = null,
        ?array $additionalParameters = null
    ): Generator {
        yield from $this->getTraversableListWithCount(
            $apiMethod,
            $order ?? [],
            $filter ?? [],
            $select ?? [],
            $limit,
            $additionalParameters
        );
    }
}
