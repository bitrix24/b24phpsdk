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

namespace Bitrix24\SDK\Services\Sale\Status;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Generator;

/**
 * Class Batch
 *
 * Overrides base Batch to handle parameter naming differences in sale.status.* REST methods:
 * - status identifier is a string code, sent as lowercase 'id'
 * - update and delete accept string identifiers
 * - list results are wrapped in 'statuses' key, the list is read with pagination by 'start' parameter
 *   because string identifiers can not be used for batch query chaining
 *
 * @see https://apidocs.bitrix24.com/api-reference/sale/status/sale-status-update.html
 * @see https://apidocs.bitrix24.com/api-reference/sale/status/sale-status-delete.html
 * @see https://apidocs.bitrix24.com/api-reference/sale/status/sale-status-list.html
 */
class Batch extends \Bitrix24\SDK\Core\Batch
{
    /**
     * Determines the ID key - lowercase 'id' for statuses
     */
    #[\Override]
    protected function determineKeyId(string $apiMethod, ?array $additionalParameters): string
    {
        return 'id';
    }

    /**
     * Extracts elements from batch result, unwrapping the 'statuses' key
     */
    #[\Override]
    protected function extractElementsFromBatchResult(ResponseData $responseData, bool $isCrmItemsInBatch): array
    {
        $resultData = $responseData->getResult();

        if (array_key_exists('statuses', $resultData) && is_array($resultData['statuses'])) {
            return $resultData['statuses'];
        }

        return [];
    }

    /**
     * Get traversable list of statuses
     *
     * Status identifiers are string codes, so the list is always read with pagination by 'start' parameter.
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
     * Update entity items with batch call using string identifiers
     *
     * Update elements in array with structure
     * status_id => [
     *  'fields' => [] // status fields to update
     * ]
     *
     * @param array<int|string, array<string, mixed>> $entityItems
     *
     * @return Generator<int, ResponseData>|ResponseData[]
     * @throws BaseException
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
            foreach ($entityItems as $entityItemId => $entityItem) {
                if (!array_key_exists('fields', $entityItem)) {
                    throw new InvalidArgumentException(
                        sprintf('array key "fields" not found in entity item with id %s', $entityItemId)
                    );
                }

                $this->registerCommand($apiMethod, [
                    'id' => (string)$entityItemId,
                    'fields' => $entityItem['fields'],
                ]);
            }

            foreach ($this->getTraversable(true) as $cnt => $updatedItemResult) {
                yield $cnt => $updatedItemResult;
            }
        } catch (InvalidArgumentException $exception) {
            $errorMessage = sprintf('batch update statuses: %s', $exception->getMessage());
            $this->logger->error(
                $errorMessage,
                [
                    'trace' => $exception->getTrace(),
                ]
            );
            throw $exception;
        } catch (\Throwable $exception) {
            $errorMessage = sprintf('batch update statuses: %s', $exception->getMessage());
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
     * Delete entity items with batch call using lowercase 'id' parameter with string identifiers
     *
     * @param string[]          $entityItemId
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
                if (!is_string($itemId) || $itemId === '') {
                    throw new InvalidArgumentException(
                        sprintf(
                            'invalid type "%s" of status id "%s" at position %s, status id must be non-empty string',
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
            $errorMessage = sprintf('batch delete statuses: %s', $exception->getMessage());
            $this->logger->error(
                $errorMessage,
                [
                    'trace' => $exception->getTrace(),
                ]
            );
            throw $exception;
        } catch (\Throwable $exception) {
            $errorMessage = sprintf('batch delete statuses: %s', $exception->getMessage());
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
}
