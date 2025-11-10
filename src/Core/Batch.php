<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Core;

use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Commands\CommandCollection;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
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
 * @package Bitrix24\SDK\Core
 */
class Batch implements BatchOperationsInterface
{
    protected const ENTITY_METHODS = [
        'entity.item.delete',
        'entity.section.delete',
        'entity.item.get',
        'entity.section.get',
        'entity.item.update',
        'entity.section.update',
        'entity.item.property.update',
    ];

    protected const MAX_BATCH_PACKET_SIZE = 50;

    protected const MAX_ELEMENTS_IN_PAGE = 50;

    protected CommandCollection $commands;

    /**
     * Batch constructor.
     */
    public function __construct(protected readonly CoreInterface $core, protected readonly LoggerInterface $logger)
    {
        $this->commands = new CommandCollection();
    }

    /**
     * Clear api commands collection
     */
    protected function clearCommands(): void
    {
        $this->logger->debug(
            'clearCommands.start',
            [
                'commandsCount' => $this->commands->count(),
            ]
        );
        $this->commands = new CommandCollection();
        $this->logger->debug('clearCommands.finish');
    }

    /**
     * Add entity items with batch call
     *
     * @param array<int, mixed> $entityItems
     *
     * @return Generator<int, ResponseData>|ResponseData[]
     * @throws BaseException
     */
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
            foreach ($entityItems as $cnt => $item) {
                $this->registerCommand($apiMethod, $item);
            }

            foreach ($this->getTraversable(true) as $cnt => $addedItemResult) {
                yield $cnt => $addedItemResult;
            }
        } catch (\Throwable $throwable) {
            $errorMessage = sprintf('batch add entity items: %s', $throwable->getMessage());
            $this->logger->error(
                $errorMessage,
                [
                    'trace' => $throwable->getTrace(),
                ]
            );

            throw new BaseException($errorMessage, $throwable->getCode(), $throwable);
        }

        $this->logger->debug('addEntityItems.finish');
    }

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

        $useFieldsInsteadOfId = $apiMethod === 'crm.address.delete';

        try {
            $this->clearCommands();
            foreach ($entityItemId as $cnt => $itemId) {
                if (!$useFieldsInsteadOfId && !is_int($itemId)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'invalid type «%s» of entity id «%s» at position %s, entity id must be integer type',
                            gettype($itemId),
                            $itemId,
                            $cnt
                        )
                    );
                }

                $parameters = $useFieldsInsteadOfId ? ['fields' => $itemId] : ['ID' => $itemId];
                // TODO: delete after migration to RestAPI v2
                if (in_array($apiMethod, self::ENTITY_METHODS)) {
                    $parameters = array_merge($parameters, $additionalParameters);
                }

                $this->registerCommand($apiMethod, $parameters);
            }

            foreach ($this->getTraversable(true) as $cnt => $deletedItemResult) {
                yield $cnt => $deletedItemResult;
            }
        } catch (InvalidArgumentException $exception) {
            $errorMessage = sprintf('batch delete entity items: %s', $exception->getMessage());
            $this->logger->error(
                $errorMessage,
                [
                    'trace' => $exception->getTrace(),
                ]
            );
            throw $exception;
        } catch (\Throwable $exception) {
            $errorMessage = sprintf('batch delete entity items: %s', $exception->getMessage());
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
     *  'fields' => [], // required element fields to update
     *  'params' => []  // optional fields
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

            $useFieldsInsteadOfId = $apiMethod === 'crm.address.update';

            foreach ($entityItems as $entityItemId => $entityItem) {
                if (!$useFieldsInsteadOfId && !is_int($entityItemId)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'invalid type «%s» of entity id «%s», entity id must be integer type',
                            gettype($entityItemId),
                            $entityItemId
                        )
                    );
                }

                if (!in_array($apiMethod, self::ENTITY_METHODS)) {
                    if (!array_key_exists('fields', $entityItem)) {
                        throw new InvalidArgumentException(
                            sprintf('array key «fields» not found in entity item with id %s', $entityItemId)
                        );
                    }

                    if ($useFieldsInsteadOfId) {
                        $cmdArguments = [
                            'fields' => $entityItem['fields']
                        ];
                    } else {
                        $cmdArguments = [
                            'id' => $entityItemId,
                            'fields' => $entityItem['fields']
                        ];
                    }

                    if (array_key_exists('params', $entityItem)) {
                        $cmdArguments['params'] = $entityItem['params'];
                    }
                } else {
                    $cmdArguments = $entityItem;
                }

                $this->registerCommand($apiMethod, $cmdArguments);
            }

            foreach ($this->getTraversable(true) as $cnt => $updatedItemResult) {
                yield $cnt => $updatedItemResult;
            }
        } catch (InvalidArgumentException $exception) {
            $errorMessage = sprintf('batch update entity items: %s', $exception->getMessage());
            $this->logger->error(
                $errorMessage,
                [
                    'trace' => $exception->getTrace(),
                ]
            );
            throw $exception;
        } catch (\Throwable $exception) {
            $errorMessage = sprintf('batch update entity items: %s', $exception->getMessage());
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
     * Register api command to command collection for batch calls
     *
     * @param array<mixed,mixed> $parameters
     *
     * @throws \Exception
     */
    protected function registerCommand(
        string $apiMethod,
        array $parameters = [],
        ?string $commandName = null,
    ): void {
        $this->logger->debug(
            'registerCommand.start',
            [
                'apiMethod' => $apiMethod,
                'parameters' => $parameters,
                'commandName' => $commandName,
            ]
        );

        $this->commands->attach(new Command($apiMethod, $parameters, $commandName));

        $this->logger->debug(
            'registerCommand.finish',
            [
                'commandsCount' => $this->commands->count(),
            ]
        );
    }

    /**
     * @param array<string,string> $order
     *
     * @return array|string[]
     */
    protected function getReverseOrder(array $order): array
    {
        $this->logger->debug(
            'getReverseOrder.start',
            [
                'order' => $order,
            ]
        );
        $reverseOrder = null;

        if ($order === []) {
            $reverseOrder = ['ID' => 'DESC'];
        } else {
            $order = array_change_key_case($order, CASE_UPPER);
            $oldDirection = array_values($order)[0];
            $newOrderDirection = $oldDirection === 'ASC' ? 'DESC' : 'ASC';

            $reverseOrder[array_key_first($order)] = $newOrderDirection;
        }

        $this->logger->debug(
            'getReverseOrder.finish',
            [
                'order' => $reverseOrder,
            ]
        );

        return $reverseOrder;
    }

    /**
     * @param array<string,mixed> $oldFilter
     *
     * @return array<string,mixed>
     */
    protected function updateFilterForBatch(
        string $keyId,
        int $startElementId,
        int $lastElementId,
        bool $isLastPage,
        array $oldFilter
    ): array {
        $this->logger->debug('updateFilterForBatch.start', [
            'startElementId' => $startElementId,
            'lastElementId' => $lastElementId,
            'isLastPage' => $isLastPage,
            'oldFilter' => $oldFilter,
            'key' => $keyId,
        ]);

        $filter = array_merge(
            $oldFilter,
            [
                sprintf('>=%s', $keyId) => $startElementId,
                $isLastPage ? sprintf('<=%s', $keyId) : sprintf('<%s', $keyId) => $lastElementId,
            ]
        );
        $this->logger->debug('updateFilterForBatch.finish', [
            'filter' => $filter,
        ]);

        return $filter;
    }

    /**
     * Get traversable list without count elements
     *
     * @link https://apidocs.bitrix24.com/api-reference/performance/huge-data.html
     *
     * Depending on the requested sorting option, the method uses two scenarios
     * 1. Sorting is not specified or starts with sorting by ID (in any direction)
     * We use fast data loading with start=-1 and a dynamic filter by the ID value from the result of the previous subquery. See
     * https://apidocs.bitrix24.com/api-reference/how-to-call-rest-api/batch.html
     * 2. Sorting by a field other than ID is specified
     * In this case, the method delegates execution to the getTraversableListWithCount() method. It uses the start >= 0 parameter, and data loading is
     * somewhat slower.
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

        // Check the sorting and select the appropriate scenario
        $keyId = $this->determineKeyId($apiMethod, $additionalParameters);
        if ($order !== []) {
            $fistKey = key($order);
            if ($fistKey != $keyId) {
                $this->logger->warning(
                    'getTraversableList.unoptimalParams',
                    [
                        'order' => $order,
                    ]
                );

                foreach($this->getTraversableListWithCount(
                        $apiMethod,
                        $order,
                        $filter,
                        $select,
                        $limit,
                        $additionalParameters
                    ) as $item
                ) {
                    yield $item;
                }

                return;
            }
        }

        if (!array_key_exists($keyId, $order)) {
            $order = [$keyId => 'ASC'];
        }

        $isAscendingSort = mb_strtoupper($order[$keyId]) === 'ASC';

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
        $this->logger->debug('getTraversableList.totalElementsCount', [
            'totalElementsCount' => $totalElementsCount,
        ]);

        // Process first page and count returned elements
        $elementsCounter = 0;
        $isCrmItemsInBatch = $additionalParameters !== null && array_key_exists('entityTypeId', $additionalParameters);

        // Process first page results
        $firstPageElements = $this->extractElementsFromBatchResult($firstPageResponse->getResponseData(), $isCrmItemsInBatch);

        foreach ($firstPageElements as $firstPageElement) {
            $elementsCounter++;
            if ($limit !== null && $elementsCounter > $limit) {
                return;
            }

            yield $firstPageElement;
        }

        // If total elements count is less than or equal to page size, finish
        if ($totalElementsCount <= self::MAX_ELEMENTS_IN_PAGE) {
            $this->logger->debug('getTraversableList.finish - single page');
            return;
        }

        // Get ID of the last element on the page
        $lastElementId = $this->getLastElementId($firstPageElements, $keyId, $isAscendingSort);
        $this->logger->debug('getTraversableList.lastElementId', [
            'lastElementId' => $lastElementId,
        ]);

        // Form and execute sequential batch requests
        $batchNumber = 0;
        while ($elementsCounter < $totalElementsCount && ($limit === null || $elementsCounter < $limit)) {
            $this->clearCommands();
            $this->logger->debug('getTraversableList.preparingBatch', [
                'batchNumber' => $batchNumber,
                'elementsCounter' => $elementsCounter,
            ]);

            // Form the first request based on sort order
            $firstCommandId = "cmd_0";
            $firstParams = [];

            $updatedFilter = $this->updateFilterForNextBatch($filter, $keyId, $lastElementId, $isAscendingSort);
            $firstParams = [
                'order' => $order,
                'filter' => $updatedFilter,
                'select' => $select,
                'start' => -1
            ];

            if ($additionalParameters !== null) {
                $firstParams = array_merge($firstParams, $additionalParameters);
            }

            $this->logger->debug('getTraversableList.batchFirstParams', [
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

            $this->logger->debug('getTraversableList.batchSizeCalculation', [
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
                $referenceFieldPath = $this->getReferenceFieldPath($prevCommandId, $lastIndex, $keyId, $isCrmItemsInBatch);

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

                $this->logger->debug('getTraversableList.batchCommandParams', [
                    'nextParams' => $nextParams,
                ]);

                // Register the next command
                $this->registerCommand($apiMethod, $nextParams, $currentCommandId);
            }

            $this->logger->debug('getTraversableList.batchCommandsRegistered', [
                'commandsCount' => $this->commands->count(),
            ]);

            // Use the existing getTraversable method to process commands
            foreach ($this->getTraversable(true) as $batchResult) {
                // Extract elements from the result
                $resultElements = $this->extractElementsFromBatchResult($batchResult, $isCrmItemsInBatch);

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
                        $this->logger->debug('getTraversableList.finish - limit reached', [
                            'elementsCounter' => $elementsCounter,
                            'limit' => $limit,
                        ]);
                        return;
                    }
                }

                // If there are no elements in the result, stop execution
                if ($resultElements === []) {
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

    /**
     * batch wrapper for *.list methods
     *
     * work with start item position and elements count
     *
     * @param array<string,string> $order
     * @param array<string,mixed> $filter
     * @param array<string,mixed> $select
     *
     * @return Generator<mixed>
     * @throws BaseException
     * @throws Exceptions\TransportException
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Exception
     */
    public function getTraversableListWithCount(
        string $apiMethod,
        array $order,
        array $filter,
        array $select,
        ?int $limit = null,
        ?array $additionalParameters = null
    ): Generator {
        $this->logger->debug(
            'getTraversableListWithCount.start',
            [
                'apiMethod' => $apiMethod,
                'order' => $order,
                'filter' => $filter,
                'select' => $select,
                'limit' => $limit,
                'additionalParameters' => $additionalParameters,
            ]
        );
        $this->clearCommands();

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

        // get total elements count
        $response = $this->core->call(
            $apiMethod,
            $params
        );

        $isCrmItemsInBatch = $additionalParameters !== null && array_key_exists('entityTypeId', $additionalParameters);
        $nextItem = $response->getResponseData()->getPagination()->getNextItem();
        $total = $response->getResponseData()->getPagination()->getTotal();

        $this->logger->debug(
            'getTraversableListWithCount.calculateCommandsRange',
            [
                'nextItem' => $nextItem,
                'totalItems' => $total,
            ]
        );

        if ($total <= self::MAX_ELEMENTS_IN_PAGE) {
            $elementsCounter = 0;
            $firstPageElements = $this->extractElementsFromBatchResult($response->getResponseData(), $isCrmItemsInBatch);
            foreach ($firstPageElements as $firstPageElement) {
                $elementsCounter++;
                if ($limit !== null && $elementsCounter > $limit) {
                    return;
                }

                yield $firstPageElement;
            }

            return;
        }



        if ($nextItem !== null) {
            //more than one page in results -  register list commands
            for ($startItem = 0; $startItem <= $total; $startItem += $nextItem) {
                $params = [
                    'order' => $order,
                    'filter' => $filter,
                    'select' => $select,
                    'start' => $startItem,
                ];

                if ($additionalParameters !== null) {
                    $params = array_merge($params, $additionalParameters);
                }

                $this->registerCommand(
                    $apiMethod,
                    $params
                );
                if ($limit !== null && $limit < $startItem) {
                    break;
                }
            }
        }

        $this->logger->debug(
            'getTraversableListWithCount.commandsRegistered',
            [
                'commandsCount' => $this->commands->count(),
                'totalItemsToSelect' => $total,
            ]
        );

        // iterate batch queries, max:  50 results per 50 elements in each result
        $elementsCounter = 0;
        foreach ($this->getTraversable(true) as $queryCnt => $queryResultData) {
            /**
             * @var $queryResultData ResponseData
             */
            $this->logger->debug(
                'getTraversableListWithCount.batchResultItem',
                [
                    'batchCommandItemNumber' => $queryCnt,
                    'nextItem' => $queryResultData->getPagination()->getNextItem(),
                    'durationTime' => $queryResultData->getTime()->duration,
                ]
            );
            $resultElements = $this->extractElementsFromBatchResult($queryResultData, $isCrmItemsInBatch);
            // iterate items in batch query result
            foreach ($resultElements as $resultElement) {
                ++$elementsCounter;
                if ($limit !== null && $elementsCounter > $limit) {
                    return;
                }

                yield $resultElement;
            }
        }

        $this->logger->debug('getTraversableListWithCount.finish');
    }

    /**
     *
     * @return Generator<int, ResponseData>
     * @throws BaseException
     * @throws Exceptions\TransportException
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Exception
     */
    protected function getTraversable(bool $isHaltOnError): Generator
    {
        $this->logger->debug(
            'getTraversable.start',
            [
                'isHaltOnError' => $isHaltOnError,
            ]
        );

        foreach ($this->getTraversableBatchResults($isHaltOnError) as $batchItem => $traversableBatchResult) {
            /**
             * @var $batchResult Response
             */
            $this->logger->debug(
                'getTraversable.batchResultItem.processStart',
                [
                    'batchItemNumber' => $batchItem,
                    'batchApiCommand' => $traversableBatchResult->getApiCommand()->getApiMethod(),
                    'batchApiCommandId' => $traversableBatchResult->getApiCommand()->getId(),
                ]
            );
            // todo try to multiplex requests
            $response = $traversableBatchResult->getResponseData();

            // single queries
            // todo handle error field
            $resultDataItems = $response->getResult()['result'];
            $resultQueryTimeItems = $response->getResult()['result_time'];

            // list queries
            //todo handle result_error for list queries
            $resultNextItems = $response->getResult()['result_next'];
            $totalItems = $response->getResult()['result_total'];
            foreach ($resultDataItems as $singleQueryKey => $singleQueryResult) {
                if (!is_array($singleQueryResult)) {
                    $singleQueryResult = [$singleQueryResult];
                }

                if (!array_key_exists($singleQueryKey, $resultQueryTimeItems)) {
                    throw new BaseException(sprintf('query time with key %s not found', $singleQueryKey));
                }

                $nextItem = null;
                if ($resultNextItems !== null && array_key_exists($singleQueryKey, $resultNextItems)) {
                    $nextItem = $resultNextItems[$singleQueryKey];
                }

                $total = null;
                if ($totalItems !== null && count($totalItems) > 0) {
                    $total = $totalItems[$singleQueryKey];
                }

                yield new ResponseData(
                    $singleQueryResult,
                    Time::initFromResponse($resultQueryTimeItems[$singleQueryKey]),
                    new Pagination($nextItem, $total)
                );
            }

            $this->logger->debug('getTraversable.batchResult.processFinish');
        }

        $this->logger->debug('getTraversable.finish');
    }

    /**
     *
     * @return Generator<Response>
     * @throws BaseException
     * @throws Exceptions\TransportException
     */
    protected function getTraversableBatchResults(bool $isHaltOnError): Generator
    {
        $this->logger->debug(
            'getTraversableBatchResults.start',
            [
                'commandsCount' => $this->commands->count(),
                'isHaltOnError' => $isHaltOnError,
            ]
        );

        // todo check unique names if exists
        $apiCommands = $this->convertToApiCommands();
        $batchQueryCounter = 0;
        while (count($apiCommands)) {
            $batchQuery = array_splice($apiCommands, 0, self::MAX_BATCH_PACKET_SIZE);
            $this->logger->debug(
                'getTraversableBatchResults.batchQuery',
                [
                    'batchQueryNumber' => $batchQueryCounter,
                    'queriesCount' => count($batchQuery),
                ]
            );
            // batch call
            $batchResult = $this->core->call('batch', ['halt' => $isHaltOnError, 'cmd' => $batchQuery]);
            // todo analyze batch result and halt on error

            ++$batchQueryCounter;
            yield $batchResult;
        }

        $this->logger->debug('getTraversableBatchResults.finish');
    }

    /**
     * @return array<string, string>
     */
    private function convertToApiCommands(): array
    {
        $apiCommands = [];
        foreach ($this->commands as $command) {
            $apiCommands[$command->getId()] = sprintf(
                '%s?%s',
                $command->getApiMethod(),
                http_build_query($command->getParameters())
            );
        }

        return $apiCommands;
    }


    /**
     * Returns relative path to previous ID value
     */
    protected function getReferenceFieldPath(string $prevCommandId, int $lastIndex, string $keyId, bool $isCrmItemsInBatch): string
    {
        return $isCrmItemsInBatch ?
            sprintf('$result[%s][items][%d][%s]', $prevCommandId, $lastIndex, $keyId) :
            sprintf('$result[%s][%d][%s]', $prevCommandId, $lastIndex, $keyId);
    }

    /**
     * Determines the ID key based on API method and parameters
     */
    protected function determineKeyId(string $apiMethod, ?array $additionalParameters): string {
        // For CRM items 'id' is used, for others - 'ID'
        $isCrmItemsInBatch = $additionalParameters !== null && array_key_exists('entityTypeId', $additionalParameters);
        return $isCrmItemsInBatch ? 'id' : 'ID';
    }

    /**
     * Gets the ID of the last element from an array of elements
     * For ASC sorting, returns the highest ID (last element)
     * For DESC sorting, returns the lowest ID (last element)
     */
    protected function getLastElementId(array $elements, string $keyId, bool $isAscendingSort): int {
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
    protected function updateFilterForNextBatch(array $filter, string $keyId, int $lastElementId, bool $isAscendingSort): array {
        if ($isAscendingSort) {
            return array_merge($filter, ['>' . $keyId => $lastElementId]);
        }

        return array_merge($filter, ['<' . $keyId => $lastElementId]);
    }

    /**
     * Extracts elements from batch request result
     */
    protected function extractElementsFromBatchResult(ResponseData $responseData, bool $isCrmItemsInBatch): array {
        $resultData = $responseData->getResult();
        if ($isCrmItemsInBatch) {
            if (array_key_exists('items', $resultData)) {
                return $resultData['items'];
            }
        } else {
            return $resultData;
        }

        return [];
    }
}
