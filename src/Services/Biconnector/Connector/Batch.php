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

namespace Bitrix24\SDK\Services\Biconnector\Connector;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Generator;

/**
 * Class Batch
 *
 * Overrides base Batch to handle parameter naming differences in biconnector.connector.* REST methods:
 * - list uses 'page' (page number, 50 records per page) instead of 'start' (offset) for pagination
 * - delete uses lowercase 'id' instead of 'ID'
 *
 * @see https://apidocs.bitrix24.com/api-reference/biconnector/connector/biconnector-connector-list.html
 * @see https://apidocs.bitrix24.com/api-reference/biconnector/connector/biconnector-connector-delete.html
 */
class Batch extends \Bitrix24\SDK\Core\Batch
{
    /**
     * Determines the ID key — lowercase 'id' for biconnector connector
     */
    #[\Override]
    protected function determineKeyId(string $apiMethod, ?array $additionalParameters): string
    {
        return 'id';
    }

    /**
     * Delete entity items with batch call using lowercase 'id' parameter
     *
     * @param int[]         $entityItemId
     * @param array<mixed>|null $additionalParameters
     *
     * @return Generator<int, ResponseData>|ResponseData[]
     * @throws BaseException
     */
    #[\Override]
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
                            'invalid type «%s» of connector id «%s» at position %s, connector id must be integer type',
                            gettype($itemId),
                            $itemId,
                            $cnt
                        )
                    );
                }

                $this->registerCommand($apiMethod, ['id' => $itemId]);
            }

            foreach ($this->getTraversable(true) as $cnt => $deletedItemResult) {
                yield $cnt => $deletedItemResult;
            }
        } catch (InvalidArgumentException $exception) {
            $errorMessage = sprintf('batch delete connector items: %s', $exception->getMessage());
            $this->logger->error(
                $errorMessage,
                [
                    'trace' => $exception->getTrace(),
                ]
            );
            throw $exception;
        } catch (\Throwable $exception) {
            $errorMessage = sprintf('batch delete connector items: %s', $exception->getMessage());
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
     * Get traversable list using page-based pagination.
     *
     * The biconnector.connector.list method uses 'page' parameter (page number, 50 records per page)
     * instead of the standard 'start' (offset) parameter used by most other REST methods.
     *
     * @link https://apidocs.bitrix24.com/api-reference/biconnector/connector/biconnector-connector-list.html
     *
     * @param array<string,string> $order
     * @param array<string,mixed>  $filter
     * @param array<string,mixed>  $select
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

    /**
     * Get traversable list using page-based pagination (page number, 50 records per page).
     *
     * The biconnector.connector.list method accepts 'page' parameter instead of 'start'.
     * Page 1 returns items 1–50, page 2 returns items 51–100, etc.
     *
     * @link https://apidocs.bitrix24.com/api-reference/biconnector/connector/biconnector-connector-list.html
     *
     * @param array<string,string> $order
     * @param array<string,mixed>  $filter
     * @param array<string,mixed>  $select
     *
     * @return Generator<mixed>
     * @throws BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    #[\Override]
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

        // Fetch first page to determine total count
        $params = [
            'order' => $order,
            'filter' => $filter,
            'select' => $select,
            'page' => 1,
        ];

        if ($additionalParameters !== null) {
            $params = array_merge($params, $additionalParameters);
        }

        $response = $this->core->call($apiMethod, $params);
        $total = $response->getResponseData()->getPagination()->getTotal();

        $this->logger->debug(
            'getTraversableListWithCount.totalElementsCount',
            [
                'totalElementsCount' => $total,
            ]
        );

        if ($total <= self::MAX_ELEMENTS_IN_PAGE) {
            $elementsCounter = 0;
            foreach ($response->getResponseData()->getResult() as $item) {
                $elementsCounter++;
                if ($limit !== null && $elementsCounter > $limit) {
                    return;
                }

                yield $item;
            }

            return;
        }

        // Register batch commands for all pages
        $totalPages = (int)ceil($total / self::MAX_ELEMENTS_IN_PAGE);
        for ($page = 1; $page <= $totalPages; $page++) {
            $pageParams = [
                'order' => $order,
                'filter' => $filter,
                'select' => $select,
                'page' => $page,
            ];

            if ($additionalParameters !== null) {
                $pageParams = array_merge($pageParams, $additionalParameters);
            }

            $this->registerCommand($apiMethod, $pageParams);

            if ($limit !== null && $limit < $page * self::MAX_ELEMENTS_IN_PAGE) {
                break;
            }
        }

        $this->logger->debug(
            'getTraversableListWithCount.commandsRegistered',
            [
                'commandsCount' => $this->commands->count(),
                'totalItemsToSelect' => $total,
            ]
        );

        $elementsCounter = 0;
        foreach ($this->getTraversable(true) as $queryResultData) {
            $resultElements = $this->extractElementsFromBatchResult($queryResultData, false);
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
}


