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

namespace Bitrix24\SDK\Services\IM;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Generator;

/**
 * Class Batch
 *
 * Overrides base Batch to handle parameter naming and pagination differences in im.* REST methods:
 * - all methods take flat upper-case parameters (CHAT_ID, MESSAGE_ID, DIALOG_ID, ...) instead of 'id' / 'fields'
 * - list methods paginate with top-level 'OFFSET' / 'LIMIT' parameters instead of 'start' and do not support
 *   'order' / 'filter' / 'select'
 * - im.recent.list wraps elements in the 'items' key and signals the next page with the 'hasMore' flag
 * - im.search.user.list returns elements as an object keyed by user identifier
 *
 * @see https://apidocs.bitrix24.com/api-reference/chats/index.html
 *
 * @package Bitrix24\SDK\Services\IM
 */
class Batch extends \Bitrix24\SDK\Core\Batch
{
    /**
     * Get traversable list for im.* list methods with top-level 'OFFSET' / 'LIMIT' pagination
     *
     * The first page is requested with a single call. If the page is not the last one, the next pages are
     * requested with batch packets. Iteration stops on the last page, when the total count of elements
     * is reached or when the limit is reached.
     *
     * @param array<string, mixed> $params method-specific selection parameters, for example DIALOG_ID or FIND
     *
     * @return Generator<int, mixed>
     * @throws BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    public function getTraversableListByOffset(string $apiMethod, array $params = [], ?int $limit = null): Generator
    {
        $this->logger->debug(
            'getTraversableListByOffset.start',
            [
                'apiMethod' => $apiMethod,
                'params' => $params,
                'limit' => $limit,
            ]
        );

        if ($limit !== null && $limit <= 0) {
            return;
        }

        $elementsCounter = 0;

        // Get first page
        $firstPageResponseData = $this->core->call(
            $apiMethod,
            $this->buildPageParams($params, 0)
        )->getResponseData();
        $firstPageElements = $this->extractElementsFromBatchResult($firstPageResponseData, false);

        foreach ($firstPageElements as $firstPageElement) {
            yield $firstPageElement;
            $elementsCounter++;
            if ($limit !== null && $elementsCounter >= $limit) {
                $this->logger->debug('getTraversableListByOffset.finish - limit reached');
                return;
            }
        }

        if ($this->isLastPage($firstPageResponseData, count($firstPageElements))) {
            $this->logger->debug('getTraversableListByOffset.finish - single page');
            return;
        }

        // Total count is unknown for some methods, for example im.recent.list always returns -1
        $total = $firstPageResponseData->getPagination()->getTotal();
        if ($total !== null && $total <= 0) {
            $total = null;
        }

        $offset = self::MAX_ELEMENTS_IN_PAGE;
        while ($total === null || $offset < $total) {
            $this->clearCommands();

            $pagesCount = self::MAX_BATCH_PACKET_SIZE;
            if ($limit !== null) {
                $pagesCount = min($pagesCount, (int)ceil(($limit - $elementsCounter) / self::MAX_ELEMENTS_IN_PAGE));
            }

            if ($total !== null) {
                $pagesCount = min($pagesCount, (int)ceil(($total - $offset) / self::MAX_ELEMENTS_IN_PAGE));
            }

            for ($i = 0; $i < $pagesCount; $i++) {
                $this->registerCommand(
                    $apiMethod,
                    $this->buildPageParams($params, $offset + $i * self::MAX_ELEMENTS_IN_PAGE)
                );
            }

            $offset += $pagesCount * self::MAX_ELEMENTS_IN_PAGE;

            foreach ($this->getTraversable(true) as $batchResult) {
                $resultElements = $this->extractElementsFromBatchResult($batchResult, false);
                foreach ($resultElements as $resultElement) {
                    yield $resultElement;
                    $elementsCounter++;
                    if ($limit !== null && $elementsCounter >= $limit) {
                        $this->logger->debug('getTraversableListByOffset.finish - limit reached');
                        return;
                    }
                }

                if ($this->isLastPage($batchResult, count($resultElements))) {
                    $this->logger->debug('getTraversableListByOffset.finish - last page', [
                        'elementsCounter' => $elementsCounter,
                    ]);
                    return;
                }
            }
        }

        $this->logger->debug('getTraversableListByOffset.finish - total reached', [
            'elementsCounter' => $elementsCounter,
        ]);
    }

    /**
     * Execute one command per parameter set with batch call
     *
     * im.* methods take flat upper-case parameters, so every item is passed to the REST method as is,
     * for example ['CHAT_ID' => 1, 'USER_ID' => 2] for im.chat.user.delete.
     *
     * @param array<int|string, array<string, mixed>> $entityItems
     *
     * @return Generator<int, ResponseData>
     * @throws BaseException
     */
    public function processEntityItems(string $apiMethod, array $entityItems): Generator
    {
        $this->logger->debug(
            'processEntityItems.start',
            [
                'apiMethod' => $apiMethod,
                'entityItems' => $entityItems,
            ]
        );

        try {
            $this->clearCommands();
            foreach ($entityItems as $entityItem) {
                $this->registerCommand($apiMethod, $entityItem);
            }

            foreach ($this->getTraversable(true) as $cnt => $processedItemResult) {
                yield $cnt => $processedItemResult;
            }
        } catch (\Throwable $throwable) {
            $errorMessage = sprintf('batch process entity items: %s', $throwable->getMessage());
            $this->logger->error(
                $errorMessage,
                [
                    'trace' => $throwable->getTrace(),
                ]
            );

            throw new BaseException($errorMessage, $throwable->getCode(), $throwable);
        }

        $this->logger->debug('processEntityItems.finish');
    }

    /**
     * Extracts elements from batch result
     *
     * Unwraps the 'items' key used by im.recent.list and drops keys of the object
     * returned by im.search.user.list, where every element is keyed by user identifier.
     */
    #[\Override]
    protected function extractElementsFromBatchResult(ResponseData $responseData, bool $isCrmItemsInBatch): array
    {
        $resultData = $responseData->getResult();

        if (array_key_exists('items', $resultData) && is_array($resultData['items'])) {
            return array_values($resultData['items']);
        }

        return array_values($resultData);
    }

    /**
     * Checks whether the page is the last one
     *
     * im.recent.list signals the next page with the 'hasMore' flag, other list methods
     * return a page with fewer elements than requested when there are no more elements.
     */
    protected function isLastPage(ResponseData $responseData, int $elementsCount): bool
    {
        $resultData = $responseData->getResult();
        if (array_key_exists('hasMore', $resultData)) {
            return !(bool)$resultData['hasMore'];
        }

        return $elementsCount < self::MAX_ELEMENTS_IN_PAGE;
    }

    /**
     * Builds parameters for a single page
     *
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    protected function buildPageParams(array $params, int $offset): array
    {
        // Null parameters are omitted so that the REST method applies its defaults
        $params = array_filter($params, static fn (mixed $value): bool => $value !== null);

        $params['OFFSET'] = $offset;
        $params['LIMIT'] = self::MAX_ELEMENTS_IN_PAGE;

        return $params;
    }
}
