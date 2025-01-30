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
    protected const MAX_BATCH_PACKET_SIZE = 50;

    protected const MAX_ELEMENTS_IN_PAGE = 50;

    protected CommandCollection $commands;

    /**
     * Batch constructor.
     */
    public function __construct(private readonly CoreInterface $core, private readonly LoggerInterface $logger)
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

        try {
            $this->clearCommands();
            foreach ($entityItemId as $cnt => $itemId) {
                if (!is_int($itemId)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'invalid type «%s» of entity id «%s» at position %s, entity id must be integer type',
                            gettype($itemId),
                            $itemId,
                            $cnt
                        )
                    );
                }

                $parameters = ['ID' => $itemId];
                if ($apiMethod === 'entity.item.delete') {
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
            foreach ($entityItems as $entityItemId => $entityItem) {
                if (!is_int($entityItemId)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'invalid type «%s» of entity id «%s», entity id must be integer type',
                            gettype($entityItemId),
                            $entityItemId
                        )
                    );
                }

                if (!array_key_exists('fields', $entityItem)) {
                    throw new InvalidArgumentException(
                        sprintf('array key «fields» not found in entity item with id %s', $entityItemId)
                    );
                }

                $cmdArguments = [
                    'id' => $entityItemId,
                    'fields' => $entityItem['fields']
                ];
                if (array_key_exists('params', $entityItem)) {
                    $cmdArguments['params'] = $entityItem['params'];
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
     * @param callable|null $callback not implemented
     *
     * @throws \Exception
     */
    protected function registerCommand(
        string $apiMethod,
        array $parameters = [],
        ?string $commandName = null,
        callable $callback = null
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
        array $order,
        array $filter,
        array $select,
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

        // strategy.3 — ID filter, batch, no count, order
        // — ✅ counting of the number of elements in the selection is disabled
        // — ⚠️ The ID of elements in the selection is increasing, i.e. the results were sorted by ID
        // — using batch
        // — sequential execution of queries
        //
        // Optimization groundwork
        // — limited use of parallel queries
        //
        // Queries are sent to the server sequentially with the "order" parameter: {"ID": "ASC"} (sorting in ascending ID).
        // Since the results are sorted in ascending ID, they can be combined into batch queries with counting of the number of elements in each disabled.
        //
        // Filter formation order:
        //
        // took a filter with "direct" sorting and got the first ID
        // took a filter with "reverse" sorting and got the last ID
        // Since ID increases monotonically, then we assume that all pages are filled with elements uniformly, in fact there will be "holes" due to master-master replication and deleted elements. i.e. the resulting selections will not always contain exactly 50 elements.
        // we form selections from ready-made filters and pack them into batch commands.
        // if possible, batch queries are executed in parallel

        // we got the first id of the element in the selection by filter
        // todo checked that this is a *.list command
        // todo checked that there is an ID in the select, i.e. the developer understands that ID is used
        // todo checked that sorting is set as "order": {"ID": "ASC"} i.e. the developer understands that the data will arrive in this order
        // todo checked that if there is a limit, then it is >1
        // todo checked that there is no ID field in the filter, since we will work with it

        $params = [
            'order' => $order,
            'filter' => $filter,
            'select' => $select,
            'start' => 0,
        ];

        // data structures for crm.items.* is little different =\
        $isCrmItemsInBatch = false;
        if ($additionalParameters !== null) {
            if (array_key_exists('entityTypeId', $additionalParameters)) {
                $isCrmItemsInBatch = true;
            }

            $params = array_merge($params, $additionalParameters);
        }

        $keyId = $isCrmItemsInBatch ? 'id' : 'ID';
        $this->logger->debug('getTraversableList.getFirstPage', [
            'apiMethod' => $apiMethod,
            'params' => $params,
        ]);
        $response = $this->core->call($apiMethod, $params);
        $totalElementsCount = $response->getResponseData()->getPagination()->getTotal();
        $this->logger->debug('getTraversableList.totalElementsCount', [
            'totalElementsCount' => $totalElementsCount,
        ]);
        // filtered elements count less than or equal one result page(50 elements)
        $elementsCounter = 0;
        if ($totalElementsCount <= self::MAX_ELEMENTS_IN_PAGE) {
            foreach ($response->getResponseData()->getResult() as $listElement) {
                ++$elementsCounter;
                if ($limit !== null && $elementsCounter > $limit) {
                    return;
                }

                yield $listElement;
            }

            $this->logger->debug('getTraversableList.finish');

            return;
        }

        // filtered elements count more than one result page(50 elements)
        // return first page
        $lastElementIdInFirstPage = null;
        if ($isCrmItemsInBatch) {
            foreach ($response->getResponseData()->getResult()['items'] as $listElement) {
                ++$elementsCounter;
                $lastElementIdInFirstPage = (int)$listElement[$keyId];
                if ($limit !== null && $elementsCounter > $limit) {
                    return;
                }

                yield $listElement;
            }
        } else {
            foreach ($response->getResponseData()->getResult() as $listElement) {
                ++$elementsCounter;
                $lastElementIdInFirstPage = (int)$listElement[$keyId];
                if ($limit !== null && $elementsCounter > $limit) {
                    return;
                }

                yield $listElement;
            }
        }

        $this->clearCommands();
        if (!in_array($keyId, $select, true)) {
            $select[] = $keyId;
        }

        // getLastElementId in filtered result
        // todo wait new api version
        if ($apiMethod !== 'user.get') {
            $defaultOrderKey = 'order';
            if ($apiMethod === 'entity.item.get') {
                $orderKey = 'SORT';
            } else {
                $orderKey = $defaultOrderKey;
            }

            $params = [
                $orderKey => $this->getReverseOrder($order),
                'filter' => $filter,
                'select' => $select,
                'start' => 0,
            ];
        } elseif ($order === []) {
            $select = [];
            // ID - ASC
            $params = [
                'order' => 'DESC',
                'filter' => $filter,
                'select' => $select,
                'start' => 0,
            ];
        }

        if ($additionalParameters !== null) {
            $params = array_merge($params, $additionalParameters);
        }
        $this->logger->debug('getTraversableList.getLastPage', [
            'apiMethod' => $apiMethod,
            'params' => $params,
        ]);
        $lastResultPage = $this->core->call($apiMethod, $params);
        if ($isCrmItemsInBatch) {
            $lastElementId = (int)$lastResultPage->getResponseData()->getResult()['items'][0][$keyId];
        } else {
            $lastElementId = (int)$lastResultPage->getResponseData()->getResult()[0][$keyId];
        }
        $this->logger->debug('getTraversableList.lastElementsId', [
            'lastElementIdInFirstPage' => $lastElementIdInFirstPage,
            'lastElementIdInLastPage' => $lastElementId,
        ]);


        // reverse order if elements in batch ordered in DESC direction
        if ($lastElementIdInFirstPage > $lastElementId) {
            $tmp = $lastElementIdInFirstPage;
            $lastElementIdInFirstPage = $lastElementId;
            $lastElementId = $tmp;
        }

        // register commands with updated filter
        //more than one page in results -  register list commands
        ++$lastElementIdInFirstPage;
        for ($startId = $lastElementIdInFirstPage; $startId <= $lastElementId; $startId += self::MAX_ELEMENTS_IN_PAGE) {
            $this->logger->debug('registerCommand.item', [
                'startId' => $startId,
                'lastElementId' => $lastElementId,
                'delta' => $lastElementId - $startId,
            ]);

            $delta = $lastElementId - $startId;
            $isLastPage = false;
            if ($delta > self::MAX_ELEMENTS_IN_PAGE) {
                // ignore
                // - master–master replication with id
                // - deleted elements
                $lastElementIdInPage = $startId + self::MAX_ELEMENTS_IN_PAGE;
            } else {
                $lastElementIdInPage = $lastElementId;
                $isLastPage = true;
            }

            $params = [
                'order' => $order,
                'filter' => $this->updateFilterForBatch($keyId, $startId, $lastElementIdInPage, $isLastPage, $filter),
                'select' => $select,
                'start' => -1,
            ];
            if ($additionalParameters !== null) {
                $params = array_merge($params, $additionalParameters);
            }

            $this->registerCommand($apiMethod, $params);
        }

        $this->logger->debug(
            'getTraversableList.commandsRegistered',
            [
                'commandsCount' => $this->commands->count(),
            ]
        );

        // iterate batch queries, max:  50 results per 50 elements in each result
        foreach ($this->getTraversable(true) as $queryCnt => $queryResultData) {
            /**
             * @var $queryResultData ResponseData
             */
            $this->logger->debug(
                'getTraversableList.batchResultItem',
                [
                    'batchCommandItemNumber' => $queryCnt,
                    'nextItem' => $queryResultData->getPagination()->getNextItem(),
                    'durationTime' => $queryResultData->getTime()->duration,
                ]
            );

            // iterate items in batch query result
            if ($isCrmItemsInBatch) {
                foreach ($queryResultData->getResult()['items'] as $listElement) {
                    ++$elementsCounter;
                    if ($limit !== null && $elementsCounter > $limit) {
                        return;
                    }

                    yield $listElement;
                }
            } else {
                foreach ($queryResultData->getResult() as $listElement) {
                    ++$elementsCounter;
                    if ($limit !== null && $elementsCounter > $limit) {
                        return;
                    }

                    yield $listElement;
                }
            }
        }

        $this->logger->debug('getTraversableList.finish');
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
        ?int $limit = null
    ): Generator {
        $this->logger->debug(
            'getTraversableListWithCount.start',
            [
                'apiMethod' => $apiMethod,
                'order' => $order,
                'filter' => $filter,
                'select' => $select,
                'limit' => $limit,
            ]
        );
        $this->clearCommands();

        // get total elements count
        $response = $this->core->call(
            $apiMethod,
            [
                'order' => $order,
                'filter' => $filter,
                'select' => $select,
                'start' => 0,
            ]
        );

        $nextItem = $response->getResponseData()->getPagination()->getNextItem();
        $total = $response->getResponseData()->getPagination()->getTotal();

        $this->logger->debug(
            'getTraversableListWithCount.calculateCommandsRange',
            [
                'nextItem' => $nextItem,
                'totalItems' => $total,
            ]
        );

        if ($total > self::MAX_ELEMENTS_IN_PAGE && $nextItem !== null) {
            //more than one page in results -  register list commands
            for ($startItem = 0; $startItem <= $total; $startItem += $nextItem) {
                $this->registerCommand(
                    $apiMethod,
                    [
                        'order' => $order,
                        'filter' => $filter,
                        'select' => $select,
                        'start' => $startItem,
                    ]
                );
                if ($limit !== null && $limit < $startItem) {
                    break;
                }
            }
        } else {
            // one page in results
            $this->registerCommand(
                $apiMethod,
                [
                    'order' => $order,
                    'filter' => $filter,
                    'select' => $select,
                    'start' => 0,
                ]
            );
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
            // iterate items in batch query result
            foreach ($queryResultData->getResult() as $listElement) {
                ++$elementsCounter;
                if ($limit !== null && $elementsCounter > $limit) {
                    return;
                }

                yield $listElement;
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
    private function getTraversableBatchResults(bool $isHaltOnError): Generator
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
}