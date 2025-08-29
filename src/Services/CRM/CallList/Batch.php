<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\CallList;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Generator;

/**
 * Class Batch
 *
 * @package Bitrix24\SDK\Services\CRM\CallList
 */
class Batch extends \Bitrix24\SDK\Core\Batch
{
    /**
     * Update calllist items with batch call
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
            foreach ($entityItems as $entityItem) {
                $cmdArguments = $entityItem;
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
            'ORDER' => $order,
            'FILTER' => $filter,
            'SELECT' => $select,
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
            $orderKey = $apiMethod === 'entity.item.get' ? 'SORT' : $defaultOrderKey;

            $params = [
                $orderKey => $this->getReverseOrder($order),
                'FILTER' => $filter,
                'SELECT' => $select,
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
                'ORDER' => $order,
                'FILTER' => $this->updateFilterForBatch($keyId, $startId, $lastElementIdInPage, $isLastPage, $filter),
                'SELECT' => $select,
                'start' => -1,
            ];

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
}
