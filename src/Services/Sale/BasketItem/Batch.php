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
use Bitrix24\SDK\Core\Exceptions;
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
     * Determines the ID key for Sale API
     * Sale API always uses lowercase 'id' regardless of parameters
     */
<<<<<<< HEAD
    protected function determineKeyId(string $apiMethod, ?array $additionalParameters): string
    {
        return 'id';
    }
=======
    #[\Override]
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
>>>>>>> 4e6e76c48dee212540ce7f8b740643014af953e6

    /**
     * Returns relative path to previous ID value for dynamic filtering
     * Sale API returns data in 'basketItems' key
     */
    protected function getReferenceFieldPath(string $prevCommandId, int $lastIndex, string $keyId, bool $isCrmItemsInBatch = false): string
    {
        // Sale API always uses 'basketItems' key regardless of $isCrmItemsInBatch parameter
        return sprintf('$result[%s][basketItems][%d][%s]', $prevCommandId, $lastIndex, $keyId);
    }

    /**
     * Gets the ID of the last element from an array of elements
     * For ASC sorting, returns the highest ID (last element)
     * For DESC sorting, returns the lowest ID (last element)
     */
    protected function getLastElementId(array $elements, string $keyId, bool $isAscendingSort): int
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
    protected function updateFilterForNextBatch(array $filter, string $keyId, int $lastElementId, bool $isAscendingSort): array
    {
        if ($isAscendingSort) {
            return array_merge($filter, ['>' . $keyId => $lastElementId]);
        }

        return array_merge($filter, ['<' . $keyId => $lastElementId]);
    }

    /**
     * Extracts elements from batch request result
     * Sale API returns data in 'basketItems' key
     */
    protected function extractElementsFromBatchResult(ResponseData $responseData, bool $isCrmItemsInBatch = false): array
    {
        // Sale API always uses 'basketItems' key regardless of $isCrmItemsInBatch parameter
        $resultData = $responseData->getResult();

        if (array_key_exists('basketItems', $resultData)) {
            return $resultData['basketItems'];
        }

        return [];
    }
}
