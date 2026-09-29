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

namespace Bitrix24\SDK\Services\Booking;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Generator;

/**
 * Class Batch
 *
 * Overrides base Batch to handle the conventions shared by all booking.v1.* REST methods:
 * - entity identifiers are passed in the lowercase 'id' parameter instead of 'ID'
 * - list results are wrapped in a single entity key, for example
 *   {"result": {"booking": [...]}} or {"result": {"waitList": [...]}}
 * - list methods always answer with "total": 0 and never send "next", so the total
 *   element count is unknown and offset based paging must stop on the first short page
 * - list filters do not support the ">id" / "<id" operators used by the base
 *   implementation of getTraversableList()
 *
 * @see https://apidocs.bitrix24.com/api-reference/booking/index.html
 */
class Batch extends \Bitrix24\SDK\Core\Batch
{
    /**
     * Determines the ID key — lowercase 'id' for booking scope
     */
    #[\Override]
    protected function determineKeyId(string $apiMethod, ?array $additionalParameters): string
    {
        return 'id';
    }

    /**
     * Extracts elements from batch result, unwrapping the single entity key
     *
     * Booking list methods wrap their payload in one key named after the entity:
     * booking, resource, resourceType, waitList, clientType, bookingClient, externalData, slots.
     */
    #[\Override]
    protected function extractElementsFromBatchResult(ResponseData $responseData, bool $isCrmItemsInBatch): array
    {
        $resultData = $responseData->getResult();

        if (count($resultData) === 1) {
            $wrappedElements = reset($resultData);
            if (is_array($wrappedElements)) {
                return $wrappedElements;
            }
        }

        return $resultData;
    }

    /**
     * Get traversable list of booking entities
     *
     * Booking list filters do not support the ">id" / "<id" operators, so the base
     * filter based strategy is not applicable and offset based paging is used instead.
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

    /**
     * Get traversable list of booking entities with offset based paging
     *
     * Booking list methods always answer with "total": 0 and never send "next",
     * so the number of pages cannot be calculated in advance. Pages are requested
     * in batch packets and the traversal stops as soon as a page comes back
     * with less than MAX_ELEMENTS_IN_PAGE elements.
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

        $startItem = 0;
        $elementsCounter = 0;

        // the first page is requested directly, its size tells whether paging is needed at all
        $firstPageResponse = $this->core->call(
            $apiMethod,
            $this->buildListParameters($order, $filter, $startItem, $additionalParameters)
        );
        $pageElements = $this->extractElementsFromBatchResult($firstPageResponse->getResponseData(), false);

        foreach ($pageElements as $pageElement) {
            ++$elementsCounter;
            if ($limit !== null && $elementsCounter > $limit) {
                return;
            }

            yield $pageElement;
        }

        $isLastPageReached = count($pageElements) < self::MAX_ELEMENTS_IN_PAGE;

        while (!$isLastPageReached) {
            if ($limit !== null && $elementsCounter >= $limit) {
                return;
            }

            $this->clearCommands();
            $pagesInPacket = self::MAX_BATCH_PACKET_SIZE;
            if ($limit !== null) {
                $pagesInPacket = min(
                    $pagesInPacket,
                    (int)ceil(($limit - $elementsCounter) / self::MAX_ELEMENTS_IN_PAGE)
                );
            }

            for ($page = 0; $page < $pagesInPacket; ++$page) {
                $startItem += self::MAX_ELEMENTS_IN_PAGE;
                $this->registerCommand(
                    $apiMethod,
                    $this->buildListParameters($order, $filter, $startItem, $additionalParameters)
                );
            }

            $this->logger->debug(
                'getTraversableListWithCount.commandsRegistered',
                [
                    'commandsCount' => $this->commands->count(),
                    'lastRegisteredStartItem' => $startItem,
                ]
            );

            $lastPageElementsCount = 0;
            foreach ($this->getTraversable(true) as $responseData) {
                $pageElements = $this->extractElementsFromBatchResult($responseData, false);
                $lastPageElementsCount = count($pageElements);

                foreach ($pageElements as $pageElement) {
                    ++$elementsCounter;
                    if ($limit !== null && $elementsCounter > $limit) {
                        return;
                    }

                    yield $pageElement;
                }
            }

            $isLastPageReached = $lastPageElementsCount < self::MAX_ELEMENTS_IN_PAGE;
        }

        $this->logger->debug('getTraversableListWithCount.finish');
    }

    /**
     * Delete booking entity items with batch call using the lowercase 'id' parameter
     *
     * @param int[] $entityItemId
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
                            'invalid type «%s» of booking entity id «%s» at position %s, id must be integer type',
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
            $errorMessage = sprintf('batch delete booking entity items: %s', $exception->getMessage());
            $this->logger->error(
                $errorMessage,
                [
                    'trace' => $exception->getTrace(),
                ]
            );
            throw $exception;
        } catch (\Throwable $exception) {
            $errorMessage = sprintf('batch delete booking entity items: %s', $exception->getMessage());
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
     * Read nested collections of several parent entities with one batch call
     *
     * Booking has a family of list methods that return the whole collection attached
     * to a single parent entity and therefore are not paginated, for example
     * booking.v1.booking.client.list or booking.v1.resource.slots.list.
     *
     * @param string $parentIdKey name of the parent id parameter, for example bookingId or resourceId
     * @param int[] $parentIds
     *
     * @return Generator<int, array<int, array<string, mixed>>> parent id => raw collection items
     * @throws BaseException
     */
    public function getEntityItemsByParentIds(string $apiMethod, string $parentIdKey, array $parentIds): Generator
    {
        $this->logger->debug(
            'getEntityItemsByParentIds.start',
            [
                'apiMethod' => $apiMethod,
                'parentIdKey' => $parentIdKey,
                'parentIds' => $parentIds,
            ]
        );

        try {
            $this->clearCommands();
            $orderedParentIds = array_values($parentIds);
            foreach ($orderedParentIds as $cnt => $parentId) {
                if (!is_int($parentId)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'invalid type «%s» of %s «%s» at position %s, id must be integer type',
                            gettype($parentId),
                            $parentIdKey,
                            $parentId,
                            $cnt
                        )
                    );
                }

                $this->registerCommand($apiMethod, [$parentIdKey => $parentId]);
            }

            foreach ($this->getTraversable(true) as $cnt => $responseData) {
                yield $orderedParentIds[$cnt] => $this->extractElementsFromBatchResult($responseData, false);
            }
        } catch (InvalidArgumentException $exception) {
            $errorMessage = sprintf('batch read booking entity items by parent id: %s', $exception->getMessage());
            $this->logger->error(
                $errorMessage,
                [
                    'trace' => $exception->getTrace(),
                ]
            );
            throw $exception;
        } catch (\Throwable $exception) {
            $errorMessage = sprintf('batch read booking entity items by parent id: %s', $exception->getMessage());
            $this->logger->error(
                $errorMessage,
                [
                    'trace' => $exception->getTrace(),
                ]
            );

            throw new BaseException($errorMessage, $exception->getCode(), $exception);
        }

        $this->logger->debug('getEntityItemsByParentIds.finish');
    }

    /**
     * Replace nested collections of several parent entities with one batch call
     *
     * Booking set methods take the parent id and the whole collection that replaces
     * the previously stored one, for example booking.v1.booking.client.set
     * takes bookingId and clients.
     *
     * @param string $parentIdKey name of the parent id parameter, for example bookingId or resourceId
     * @param string $payloadKey name of the collection parameter, for example clients, externalData or slots
     * @param array<int, array<mixed>> $entityItems parent id => collection to store
     *
     * @return Generator<int, ResponseData> parent id => response data
     * @throws BaseException
     */
    public function setEntityItems(
        string $apiMethod,
        string $parentIdKey,
        string $payloadKey,
        array $entityItems
    ): Generator {
        $this->logger->debug(
            'setEntityItems.start',
            [
                'apiMethod' => $apiMethod,
                'parentIdKey' => $parentIdKey,
                'payloadKey' => $payloadKey,
                'entityItems' => $entityItems,
            ]
        );

        try {
            $this->clearCommands();
            $orderedParentIds = [];
            foreach ($entityItems as $parentId => $payload) {
                if (!is_int($parentId)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'invalid type «%s» of %s «%s», id must be integer type',
                            gettype($parentId),
                            $parentIdKey,
                            $parentId
                        )
                    );
                }

                $orderedParentIds[] = $parentId;
                $this->registerCommand($apiMethod, [
                    $parentIdKey => $parentId,
                    $payloadKey => $payload,
                ]);
            }

            foreach ($this->getTraversable(true) as $cnt => $responseData) {
                yield $orderedParentIds[$cnt] => $responseData;
            }
        } catch (InvalidArgumentException $exception) {
            $errorMessage = sprintf('batch set booking entity items: %s', $exception->getMessage());
            $this->logger->error(
                $errorMessage,
                [
                    'trace' => $exception->getTrace(),
                ]
            );
            throw $exception;
        } catch (\Throwable $exception) {
            $errorMessage = sprintf('batch set booking entity items: %s', $exception->getMessage());
            $this->logger->error(
                $errorMessage,
                [
                    'trace' => $exception->getTrace(),
                ]
            );

            throw new BaseException($errorMessage, $exception->getCode(), $exception);
        }

        $this->logger->debug('setEntityItems.finish');
    }

    /**
     * Build parameters for a single page of a booking list method
     *
     * The select parameter is intentionally omitted: booking list methods do not support it.
     *
     * @param array<string,string> $order
     * @param array<string,mixed> $filter
     * @param array<mixed>|null $additionalParameters
     *
     * @return array<string, mixed>
     */
    protected function buildListParameters(
        array $order,
        array $filter,
        int $startItem,
        ?array $additionalParameters = null
    ): array {
        $parameters = [
            'filter' => $filter,
            'order' => $order,
            'start' => $startItem,
        ];

        if ($additionalParameters !== null) {
            return array_merge($parameters, $additionalParameters);
        }

        return $parameters;
    }
}
