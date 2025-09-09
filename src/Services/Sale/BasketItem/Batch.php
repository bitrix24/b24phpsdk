<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\BasketItem;

use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Commands\CommandCollection;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\DTO\Pagination;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Bitrix24\SDK\Core\Response\DTO\Result;
use Bitrix24\SDK\Core\Response\DTO\Time;
use Bitrix24\SDK\Core\Response\Response;
use Generator;
use Psr\Log\LoggerInterface;

/**
 * Class Batch
 *
 * @package Bitrix24\SDK\Services\Sale\BasketItem
 */
class Batch extends \Bitrix24\SDK\Core\Batch
{
    /**
     * Delete entity items with batch call
     *
     *
     * @return Generator<int, ResponseData>|ResponseData[]
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    public function deleteEntityItems(
        string $apiMethod,
        array $entityItemId,
        ?array $additionalParameters = null
    ): Generator {
        $this->logger->debug(
            'deleteEntityItems.start',
            [
                'apiMethod' => $apiMethod,
                'entityItems' => $entityItemId,
                'additionalParameters' => $additionalParameters,
            ]
        );

        try {
            $this->clearCommands();
            foreach ($entityItemId as $cnt => $itemId) {
                if (!is_int($itemId)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'invalid type «%s» of basket item id «%s» at position %s, basket item id must be integer type',
                            gettype($itemId),
                            $itemId,
                            $cnt
                        )
                    );
                }

                $this->registerCommand($apiMethod, ['id' => $itemId]);
            }

            foreach ($this->getTraversable(true) as $cnt => $deletedItemResult) {
                $this->logger->debug('deleteEntityItems', ['result' => $deletedItemResult->getResult()]);
                yield $cnt => $deletedItemResult;
            }
        } catch (InvalidArgumentException $exception) {
            $errorMessage = sprintf('batch delete basket items: %s', $exception->getMessage());
            $this->logger->error(
                $errorMessage,
                [
                    'trace' => $exception->getTrace(),
                ]
            );
            throw $exception;
        } catch (\Throwable $exception) {
            $errorMessage = sprintf('batch delete basket items: %s', $exception->getMessage());
            $this->logger->error(
                $errorMessage,
                [
                    'trace' => $exception->getTrace(),
                ]
            );

            throw new BaseException($errorMessage, $exception->getCode(), $exception);
        }

        $this->logger->debug('deleteEntityItems.finish');
    }

    /**
     * Update entity items with batch call
     *
     * Update elements in array with structure
     * element_id => [
     *  // fields to update
     * ]
     *
     * @param array<int, array<string, mixed>> $entityItems
     *
     * @return Generator<int, ResponseData>|ResponseData[]
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    public function updateEntityItems(string $apiMethod, array $entityItems): Generator
    {
        $this->logger->debug(
            'updateEntityItems.start',
            [
                'apiMethod' => $apiMethod,
                'entityItems' => $entityItems,
            ]
        );

        try {
            $this->clearCommands();
            foreach ($entityItems as $entityItemId => $entityItem) {
                if (!is_int($entityItemId)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'invalid type «%s» of basket item id «%s», the id must be integer type',
                            gettype($entityItemId),
                            $entityItemId
                        )
                    );
                }

                $cmdArguments = [];
                $cmdArguments['id'] = $entityItemId;
                $cmdArguments['fields'] = $entityItem['fields'];

                $this->registerCommand($apiMethod, $cmdArguments);
            }

            foreach ($this->getTraversable(true) as $cnt => $updatedItemResult) {
                yield $cnt => $updatedItemResult;
            }
        } catch (InvalidArgumentException $exception) {
            $errorMessage = sprintf('batch update basket items: %s', $exception->getMessage());
            $this->logger->error(
                $errorMessage,
                [
                    'trace' => $exception->getTrace(),
                ]
            );
            throw $exception;
        } catch (\Throwable $exception) {
            $errorMessage = sprintf('batch update basket items: %s', $exception->getMessage());
            $this->logger->error(
                $errorMessage,
                [
                    'trace' => $exception->getTrace(),
                ]
            );

            throw new BaseException($errorMessage, $exception->getCode(), $exception);
        }

        $this->logger->debug('updateEntityItems.finish');
    }

    /**
     * Get traversable list without count elements
     *
     * @param array<string,string> $order
     * @param array<string,mixed> $filter
     * @param array<string,mixed> $select
     *
     * @return \Generator<mixed>
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getTraversableList(
        string $apiMethod,
        ?array $order = [],
        ?array $filter = [],
        ?array $select = [],
        ?int $limit = null,
        ?array $additionalParameters = null
    ): Generator {
        $apiMethod = strtolower($apiMethod);
        $this->logger->debug(
            'getTraversableList.start',
            [
                'apiMethod' => $apiMethod,
                'order' => $order,
                'filter' => $filter,
                'select' => $select,
                'limit' => $limit,
                'additionalParameters' => $additionalParameters,
            ]
        );

        // Determine sort direction and ID key
        $keyId = 'id';
        $order = array_key_exists($keyId, $order) ? [$keyId => $order[$keyId]] : [$keyId => 'asc'];

        $isAscendingSort = $order[$keyId] == 'asc';

        // Get first page
        $params = [
            'order' => $order,
            'filter' => $filter,
            'select' => $select,
            'start' => 0,
        ];

        if ($additionalParameters !== null) {
            $params = array_merge($params, $additionalParameters);
        }

        $firstPageResponse = $this->core->call($apiMethod, $params);
        $totalElementsCount = $firstPageResponse->getResponseData()->getPagination()->getTotal();
        $this->logger->debug('getTraversableListAlter.totalElementsCount', [
            'totalElementsCount' => $totalElementsCount,
        ]);

        // Process first page and count returned elements
        $elementsCounter = 0;

        // Process first page results
        $firstPageElements = $firstPageResponse->getResponseData()->getResult()['basketItems'];

        foreach ($firstPageElements as $firstPageElement) {
            $elementsCounter++;
            if ($limit !== null && $elementsCounter > $limit) {
                return;
            }

            yield $firstPageElement;
        }

        // If total elements count is less than or equal to page size, finish
        if ($totalElementsCount <= self::MAX_ELEMENTS_IN_PAGE) {
            $this->logger->debug('getTraversableListAlter.finish - single page');
            return;
        }

        // Get ID of the last element on the page
        $lastElementId = $this->getLastElementIdAlter($firstPageElements, $keyId, $isAscendingSort);
        $this->logger->debug('getTraversableListAlter.lastElementId', [
            'lastElementId' => $lastElementId,
        ]);

        // Form and execute sequential batch requests
        $batchNumber = 0;
        while ($elementsCounter < $totalElementsCount && ($limit === null || $elementsCounter < $limit)) {
            $this->clearCommands();
            $this->logger->debug('getTraversableListAlter.preparingBatch', [
                'batchNumber' => $batchNumber,
                'elementsCounter' => $elementsCounter,
            ]);

            // Form the first request based on sort order
            $firstCommandId = "cmd_0";
            $firstParams = [];

            $updatedFilter = $this->updateFilterForNextBatchAlter($filter, $keyId, $lastElementId, $isAscendingSort);
            $firstParams = [
                'order' => $order,
                'filter' => $updatedFilter,
                'select' => $select,
                'start' => -1
            ];

            if ($additionalParameters !== null) {
                $firstParams = array_merge($firstParams, $additionalParameters);
            }

            $this->logger->debug('getTraversableListAlter.batchFirstParams', [
                'nextParams' => $firstParams,
            ]);

            // Register the first command
            $this->registerCommand($apiMethod, $firstParams, $firstCommandId);

            // Calculate how many additional pages we need for remaining elements
            $remainingElements = $totalElementsCount - $elementsCounter;
            $neededPages = ceil($remainingElements / self::MAX_ELEMENTS_IN_PAGE);
            // one page we already registered
            $neededPages -= 1;

            // Limit by the maximum packet size and the limit parameter if provided
            $maxBatchSize = min(
                (int)$neededPages, // Only register as many commands as we need pages
                self::MAX_BATCH_PACKET_SIZE - 1 // -1 because we've already registered cmd_0
            );

            if ($limit !== null) {
                // If we have a limit, we might need even fewer pages
                $remainingLimit = $limit - $elementsCounter;
                $pagesForLimit = ceil($remainingLimit / self::MAX_ELEMENTS_IN_PAGE);
                $maxBatchSize = min($maxBatchSize, (int)$pagesForLimit);
            }

            $this->logger->debug('getTraversableListAlter.batchSizeCalculation', [
                'totalElementsCount' => $totalElementsCount,
                'elementsCounter' => $elementsCounter,
                'remainingElements' => $remainingElements,
                'neededPages' => $neededPages,
                'maxBatchSize' => $maxBatchSize,
            ]);

            // Use a unified approach for both ASC and DESC sorting with dynamic filters
            for ($i = 1; $i <= $maxBatchSize; $i++) {
                $prevCommandId = "cmd_" . ($i - 1);
                $currentCommandId = "cmd_" . $i;

                // Dynamic filter referencing the result of the previous request
                $referenceFilter = [];
                $lastIndex = (self::MAX_ELEMENTS_IN_PAGE - 1);
                $referenceFieldPath = sprintf('$result[%s][basketItems][%d][%s]', $prevCommandId, $lastIndex, $keyId);

                // Create the appropriate filter based on sort direction
                $filterOperator = $isAscendingSort ? '>' . $keyId : '<' . $keyId;
                $referenceFilter[$filterOperator] = $referenceFieldPath;

                $nextParams = [
                    'order' => $order,
                    'filter' => array_merge($filter, $referenceFilter),
                    'select' => $select,
                    'start' => -1
                ];

                if ($additionalParameters !== null) {
                    $nextParams = array_merge($nextParams, $additionalParameters);
                }

                $this->logger->debug('getTraversableListAlter.batchCommandParams', [
                    'nextParams' => $nextParams,
                ]);

                // Register the next command
                $this->registerCommand($apiMethod, $nextParams, $currentCommandId);
            }

            $this->logger->debug('getTraversableListAlter.batchCommandsRegistered', [
                'commandsCount' => $this->commands->count(),
            ]);

            // Use the existing getTraversable method to process commands
            foreach ($this->getTraversable(true) as $batchResult) {
                // Extract elements from the result
                $resultElements = $this->extractElementsFromBatchResultAlter($batchResult, $keyId);

                // For each result element, return it and track the last element ID
                // The lastElementId will be used for the next batch if using ASC sort
                foreach ($resultElements as $resultElement) {
                    // Update lastElementId properly depending on sort order
                    if (isset($resultElement[$keyId])) {
                        $lastElementId = (int)$resultElement[$keyId];
                    }

                    yield $resultElement;
                    $elementsCounter++;
                    if ($limit !== null && $elementsCounter >= $limit) {
                        $this->logger->debug('getTraversableListAlter.finish - limit reached', [
                            'elementsCounter' => $elementsCounter,
                            'limit' => $limit,
                        ]);
                        return;
                    }
                }

                // If there are no elements in the result, stop execution
                if ($resultElements === []) {
                    $this->logger->debug('getTraversableListAlter.finish - empty result');
                    return;
                }
            }

            $batchNumber++;
        }

        $this->logger->debug('getTraversableListAlter.finish - all elements processed', [
            'elementsCounter' => $elementsCounter,
            'totalBatches' => $batchNumber,
        ]);
    }

    /**
     * Gets the ID of the last element from an array of elements
     * For ASC sorting, returns the highest ID (last element)
     * For DESC sorting, returns the lowest ID (last element)
     */
    protected function getLastElementIdAlter(array $elements, string $keyId, bool $isAscendingSort): int
    {
        if ($elements === []) {
            return 0;
        }

        $lastElement = $isAscendingSort ? end($elements) : end($elements);

        return (int)$lastElement[$keyId];
    }

    /**
     * Updates the filter for the next batch of requests
     *
     * @param array<string,mixed> $filter
     * @return array<string,mixed>
     */
    protected function updateFilterForNextBatchAlter(array $filter, string $keyId, int $lastElementId, bool $isAscendingSort): array
    {
        if ($isAscendingSort) {
            return array_merge($filter, ['>' . $keyId => $lastElementId]);
        }
        return array_merge($filter, ['<' . $keyId => $lastElementId]);
    }

    /**
     * Extracts elements from batch request result
     */
    protected function extractElementsFromBatchResultAlter(ResponseData $responseData, string $keyId): array
    {
        $results = [];
        $resultData = $responseData->getResult();

        foreach ($resultData['basketItems'] as $item) {
            $results[] = $item;
        }

        return $results;
    }
}
