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

namespace Bitrix24\SDK\Services\Lists\Element;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Generator;

/**
 * Custom Batch implementation for Element API
 *
 * The Element API has specific requirements for batch operations:
 * - Add operations require element code and fields structure
 * - Update operations work with specific element structure
 * - Delete operations require element identification parameters
 */
class Batch extends \Bitrix24\SDK\Core\Batch
{
    /**
     * Add entity items with Element API specific format
     *
     * Add elements in array with structure:
     * [
     *  'IBLOCK_TYPE_ID' => string,
     *  'IBLOCK_ID' => int,              // or use IBLOCK_CODE
     *  'ELEMENT_CODE' => string,
     *  'FIELDS' => [],                  // Element fields
     *  'IBLOCK_CODE' => string,         // optional
     *  'IBLOCK_SECTION_ID' => int,      // optional
     *  'LIST_ELEMENT_URL' => string     // optional
     * ]
     */
    #[\Override]
    public function addEntityItems(string $apiMethod, array $entityItems): Generator
    {
        $this->logger->debug(
            'addEntityItems.start',
            [
                'apiMethod' => $apiMethod,
                'entityItems' => $entityItems,
            ]
        );

        try {
            $this->clearCommands();

            foreach ($entityItems as $cnt => $entityItem) {
                if (!is_array($entityItem)) {
                    throw new InvalidArgumentException(sprintf('item %s must be array', $cnt));
                }

                if (!array_key_exists('FIELDS', $entityItem)) {
                    throw new InvalidArgumentException('array key «FIELDS» not found in entity item with id ' . $cnt);
                }

                $this->registerCommand($apiMethod, $entityItem);
            }

            foreach ($this->getTraversable(true) as $cnt => $addedItemResult) {
                yield $cnt => $addedItemResult;
            }
        } catch (InvalidArgumentException $exception) {
            $this->logger->error('batch add entity items: ' . $exception->getMessage(), ['trace' => $exception->getTrace()]);
            throw $exception;
        } catch (\Throwable $exception) {
            $errorMessage = sprintf('batch add entity items: %s', $exception->getMessage());
            $this->logger->error($errorMessage, ['trace' => $exception->getTrace()]);
            throw new BaseException($errorMessage, $exception->getCode(), $exception);
        }

        $this->logger->debug('addEntityItems.finish');
    }

    /**
     * Update entity items with Element API specific format
     *
     * Update elements in array with structure:
     * element_id => [
     *  'IBLOCK_TYPE_ID' => string,
     *  'IBLOCK_ID' => int,              // or use IBLOCK_CODE
     *  'ELEMENT_ID' => int,             // or use ELEMENT_CODE
     *  'FIELDS' => [],                  // Element fields to update
     *  'IBLOCK_CODE' => string,         // optional
     *  'ELEMENT_CODE' => string         // optional
     * ]
     */
    #[\Override]
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

            foreach ($entityItems as $entityId => $entityItem) {
                if (!is_array($entityItem)) {
                    throw new InvalidArgumentException(sprintf('item %s must be array', $entityId));
                }

                if (!array_key_exists('FIELDS', $entityItem)) {
                    throw new InvalidArgumentException('array key «FIELDS» not found in entity item with id ' . $entityId);
                }

                $this->registerCommand($apiMethod, $entityItem);
            }

            foreach ($this->getTraversable(true) as $cnt => $updatedItemResult) {
                yield $cnt => $updatedItemResult;
            }
        } catch (InvalidArgumentException $exception) {
            $this->logger->error('batch update entity items: ' . $exception->getMessage(), ['trace' => $exception->getTrace()]);
            throw $exception;
        } catch (\Throwable $exception) {
            $errorMessage = sprintf('batch update entity items: %s', $exception->getMessage());
            $this->logger->error($errorMessage, ['trace' => $exception->getTrace()]);
            throw new BaseException($errorMessage, $exception->getCode(), $exception);
        }

        $this->logger->debug('updateEntityItems.finish');
    }

    /**
     * Delete entity items with Element API specific format
     *
     * Delete elements in array with structure:
     * [
     *  'IBLOCK_TYPE_ID' => string,
     *  'IBLOCK_ID' => int,              // or use IBLOCK_CODE
     *  'ELEMENT_ID' => int,             // or use ELEMENT_CODE
     *  'IBLOCK_CODE' => string,         // optional
     *  'ELEMENT_CODE' => string         // optional
     * ]
     */
    #[\Override]
    public function deleteEntityItems(string $apiMethod, array $entityItems, ?array $additionalParameters = null): Generator
    {
        $this->logger->debug(
            'deleteEntityItems.start',
            [
                'apiMethod' => $apiMethod,
                'entityItems' => $entityItems,
                'additionalParameters' => $additionalParameters,
            ]
        );

        try {
            $this->clearCommands();

            foreach ($entityItems as $cnt => $entityItem) {
                if (!is_array($entityItem)) {
                    throw new InvalidArgumentException(sprintf('item %s must be array', $cnt));
                }

                $this->registerCommand($apiMethod, $entityItem);
            }

            foreach ($this->getTraversable(true) as $cnt => $deletedItemResult) {
                yield $cnt => $deletedItemResult;
            }
        } catch (InvalidArgumentException $exception) {
            $this->logger->error('batch delete entity items: ' . $exception->getMessage(), ['trace' => $exception->getTrace()]);
            throw $exception;
        } catch (\Throwable $exception) {
            $errorMessage = sprintf('batch delete entity items: %s', $exception->getMessage());
            $this->logger->error($errorMessage, ['trace' => $exception->getTrace()]);
            throw new BaseException($errorMessage, $exception->getCode(), $exception);
        }

        $this->logger->debug('deleteEntityItems.finish');
    }

    /**
     * Get traversable list for Element API specific format
     *
     * @throws BaseException
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

        // Build parameters for Element API format
        $params = [];

        if ($additionalParameters !== null) {
            $params = $additionalParameters;
        }

        if ($select !== null && $select !== []) {
            $params['SELECT'] = $select;
        }

        if ($filter !== null && $filter !== []) {
            $params['FILTER'] = $filter;
        }

        if ($order !== null && $order !== []) {
            $params['ELEMENT_ORDER'] = $order;
        }

        // Get first page to determine total count
        $firstPageParams = array_merge($params, ['start' => 0]);
        $firstPageResponse = $this->core->call($apiMethod, $firstPageParams);
        $totalElementsCount = $firstPageResponse->getResponseData()->getPagination()->getTotal();

        $this->logger->debug('getTraversableList.totalElementsCount', [
            'totalElementsCount' => $totalElementsCount,
        ]);

        // Process first page and count returned elements
        $elementsCounter = 0;
        $firstPageElements = $firstPageResponse->getResponseData()->getResult();

        foreach ($firstPageElements as $firstPageElement) {
            $elementsCounter++;
            if ($limit !== null && $elementsCounter > $limit) {
                $this->logger->debug('getTraversableList.finish - limit reached on first page');
                return;
            }

            yield $firstPageElement;
        }

        // If total elements count is less than or equal to page size, finish
        if ($totalElementsCount <= 50) {
            $this->logger->debug('getTraversableList.finish - single page');
            return;
        }

        // Process remaining pages using batch requests
        $batchNumber = 0;
        while ($elementsCounter < $totalElementsCount && ($limit === null || $elementsCounter < $limit)) {
            $this->clearCommands();

            $this->logger->debug('getTraversableList.preparingBatch', [
                'batchNumber' => $batchNumber,
                'elementsCounter' => $elementsCounter,
            ]);

            // Calculate how many pages we need
            $remainingElements = $totalElementsCount - $elementsCounter;
            if ($limit !== null) {
                $remainingLimit = $limit - $elementsCounter;
                $remainingElements = min($remainingElements, $remainingLimit);
            }

            $neededPages = ceil($remainingElements / 50);
            $maxBatchSize = min($neededPages, 50); // Maximum 50 commands per batch

            // Register batch commands for multiple pages
            for ($i = 0; $i < $maxBatchSize; $i++) {
                $startPosition = $elementsCounter + ($i * 50);
                if ($startPosition >= $totalElementsCount) {
                    break;
                }

                $batchParams = array_merge($params, ['start' => $startPosition]);
                $commandId = "cmd_" . $i;

                $this->registerCommand($apiMethod, $batchParams, $commandId);
            }

            $this->logger->debug('getTraversableList.batchCommandsRegistered', [
                'commandsCount' => $this->commands->count(),
            ]);

            // Execute batch and process results
            foreach ($this->getTraversable(true) as $batchResult) {
                $resultElements = $batchResult->getResult();

                // Process each element in the batch result
                foreach ($resultElements as $resultElement) {
                    $elementsCounter++;

                    if ($limit !== null && $elementsCounter > $limit) {
                        $this->logger->debug('getTraversableList.finish - limit reached', [
                            'elementsCounter' => $elementsCounter,
                            'limit' => $limit,
                        ]);
                        return;
                    }

                    yield $resultElement;
                }

                // If there are no elements in the result, stop execution
                if (empty($resultElements)) {
                    $this->logger->debug('getTraversableList.finish - empty result');
                    return;
                }
            }

            $batchNumber++;
        }

        $this->logger->debug('getTraversableList.finish - all elements processed', [
            'elementsCounter' => $elementsCounter,
            'totalBatches' => $batchNumber,
        ]);
    }
}
