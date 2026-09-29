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

namespace Bitrix24\SDK\Services\Sale\TradePlatform;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Generator;

/**
 * Class Batch
 *
 * Overrides base Batch to handle parameter naming differences in sale.tradePlatform.* REST methods:
 * - list results are wrapped in 'tradePlatforms' key and use lowercase 'id'
 *
 * @see https://apidocs.bitrix24.com/api-reference/sale/trade-platform/sale-trade-platform-list.html
 */
class Batch extends \Bitrix24\SDK\Core\Batch
{
    /**
     * Determines the ID key - lowercase 'id' for trade platforms
     */
    #[\Override]
    protected function determineKeyId(string $apiMethod, ?array $additionalParameters): string
    {
        return 'id';
    }

    /**
     * Extracts elements from batch result, unwrapping the 'tradePlatforms' key
     */
    #[\Override]
    protected function extractElementsFromBatchResult(ResponseData $responseData, bool $isCrmItemsInBatch): array
    {
        $resultData = $responseData->getResult();

        if (array_key_exists('tradePlatforms', $resultData) && is_array($resultData['tradePlatforms'])) {
            return $resultData['tradePlatforms'];
        }

        return [];
    }

    /**
     * Returns reference field path including 'tradePlatforms' wrapper for batch query chaining
     */
    #[\Override]
    protected function getReferenceFieldPath(string $prevCommandId, int $lastIndex, string $keyId, bool $isCrmItemsInBatch): string
    {
        return sprintf('$result[%s][tradePlatforms][%d][%s]', $prevCommandId, $lastIndex, $keyId);
    }

    /**
     * Get traversable list using lowercase 'id' key and 'tradePlatforms' result wrapper
     *
     * The 'id' field is added to a non-empty select, because it is used for batch query chaining.
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
        if ($select !== null && $select !== [] && !in_array('id', $select, true)) {
            $select[] = 'id';
        }

        yield from parent::getTraversableList($apiMethod, $order, $filter, $select, $limit, $additionalParameters);
    }
}
