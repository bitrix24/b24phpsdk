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

namespace Bitrix24\SDK\Services\Sale\DeliveryRequest;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Generator;

/**
 * Class Batch
 *
 * Overrides base Batch to handle parameter naming differences in sale.delivery.request.* REST methods:
 * - delivery request is identified by a pair of 'DELIVERY_ID' and 'REQUEST_ID' parameters instead of a single 'ID'
 * - update passes the fields to update as top-level parameters
 *
 * @see https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery-request/sale-delivery-request-update.html
 * @see https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery-request/sale-delivery-request-delete.html
 */
class Batch extends \Bitrix24\SDK\Core\Batch
{
    /**
     * Update entity items with batch call
     *
     * Update elements in array with structure
     * [
     *  'DELIVERY_ID' => int,     // delivery service id
     *  'REQUEST_ID' => string,   // delivery request id
     *  // other fields to update (FINALIZE, STATUS, PROPERTIES, OVERWRITE_PROPERTIES)
     * ]
     *
     * @param array<int, array<string, mixed>> $entityItems
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
            foreach ($entityItems as $cnt => $entityItem) {
                $this->assertRequestIdentifier($entityItem, $cnt);
                $this->registerCommand($apiMethod, $entityItem);
            }

            foreach ($this->getTraversable(true) as $cnt => $updatedItemResult) {
                yield $cnt => $updatedItemResult;
            }
        } catch (InvalidArgumentException $exception) {
            $errorMessage = sprintf('batch update delivery requests: %s', $exception->getMessage());
            $this->logger->error(
                $errorMessage,
                [
                    'trace' => $exception->getTrace(),
                ]
            );
            throw $exception;
        } catch (\Throwable $exception) {
            $errorMessage = sprintf('batch update delivery requests: %s', $exception->getMessage());
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
     * Delete entity items with batch call
     *
     * Delete elements in array with structure
     * [
     *  'DELIVERY_ID' => int,     // delivery service id
     *  'REQUEST_ID' => string,   // delivery request id
     * ]
     *
     * @param array<int, array{DELIVERY_ID: int, REQUEST_ID: string}> $entityItemId
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
            foreach ($entityItemId as $cnt => $entityItem) {
                $this->assertRequestIdentifier($entityItem, $cnt);
                $this->registerCommand($apiMethod, [
                    'DELIVERY_ID' => $entityItem['DELIVERY_ID'],
                    'REQUEST_ID' => $entityItem['REQUEST_ID'],
                ]);
            }

            foreach ($this->getTraversable(true) as $cnt => $deletedItemResult) {
                yield $cnt => $deletedItemResult;
            }
        } catch (InvalidArgumentException $exception) {
            $errorMessage = sprintf('batch delete delivery requests: %s', $exception->getMessage());
            $this->logger->error(
                $errorMessage,
                [
                    'trace' => $exception->getTrace(),
                ]
            );
            throw $exception;
        } catch (\Throwable $exception) {
            $errorMessage = sprintf('batch delete delivery requests: %s', $exception->getMessage());
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
     * Checks that the item contains delivery request identifier: 'DELIVERY_ID' and 'REQUEST_ID'
     *
     * @throws InvalidArgumentException
     */
    private function assertRequestIdentifier(mixed $entityItem, int|string $position): void
    {
        if (!is_array($entityItem)) {
            throw new InvalidArgumentException(
                sprintf('invalid type "%s" of delivery request at position %s, array expected', gettype($entityItem), $position)
            );
        }

        if (!array_key_exists('DELIVERY_ID', $entityItem) || !is_int($entityItem['DELIVERY_ID'])) {
            throw new InvalidArgumentException(
                sprintf('integer key "DELIVERY_ID" not found in delivery request at position %s', $position)
            );
        }

        if (!array_key_exists('REQUEST_ID', $entityItem) || !is_string($entityItem['REQUEST_ID']) || $entityItem['REQUEST_ID'] === '') {
            throw new InvalidArgumentException(
                sprintf('non-empty string key "REQUEST_ID" not found in delivery request at position %s', $position)
            );
        }
    }
}
