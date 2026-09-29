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

namespace Bitrix24\SDK\Services\Sale\ShipmentItem;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Generator;

/**
 * Class Batch
 *
 * Overrides base Batch to handle parameter naming differences in sale.shipmentitem.* REST methods:
 * - delete uses lowercase 'id' instead of 'ID'
 * - list results are wrapped in 'shipmentItems' key and use lowercase 'id'
 *
 * @see https://apidocs.bitrix24.com/api-reference/sale/shipment-item/sale-shipment-item-delete.html
 * @see https://apidocs.bitrix24.com/api-reference/sale/shipment-item/sale-shipment-item-list.html
 */
class Batch extends \Bitrix24\SDK\Core\Batch
{
    /**
     * Determines the ID key - lowercase 'id' for shipment items
     */
    #[\Override]
    protected function determineKeyId(string $apiMethod, ?array $additionalParameters): string
    {
        return 'id';
    }

    /**
     * Extracts elements from batch result, unwrapping the 'shipmentItems' key
     */
    #[\Override]
    protected function extractElementsFromBatchResult(ResponseData $responseData, bool $isCrmItemsInBatch): array
    {
        $resultData = $responseData->getResult();

        if (array_key_exists('shipmentItems', $resultData) && is_array($resultData['shipmentItems'])) {
            return $resultData['shipmentItems'];
        }

        return [];
    }

    /**
     * Returns reference field path including 'shipmentItems' wrapper for batch query chaining
     */
    #[\Override]
    protected function getReferenceFieldPath(string $prevCommandId, int $lastIndex, string $keyId, bool $isCrmItemsInBatch): string
    {
        return sprintf('$result[%s][shipmentItems][%d][%s]', $prevCommandId, $lastIndex, $keyId);
    }

    /**
     * Get traversable list using lowercase 'id' key and 'shipmentItems' result wrapper
     *
     * The 'id' field is added to a non-empty select, because it is used for batch query chaining.
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
        if ($select !== null && $select !== [] && !in_array('id', $select, true)) {
            $select[] = 'id';
        }

        yield from parent::getTraversableList($apiMethod, $order, $filter, $select, $limit, $additionalParameters);
    }

    /**
     * Delete entity items with batch call using lowercase 'id' parameter
     *
     * @param int[]             $entityItemId
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
                            'invalid type "%s" of shipment item id "%s" at position %s, shipment item id must be integer type',
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
            $errorMessage = sprintf('batch delete shipment items: %s', $exception->getMessage());
            $this->logger->error(
                $errorMessage,
                [
                    'trace' => $exception->getTrace(),
                ]
            );
            throw $exception;
        } catch (\Throwable $exception) {
            $errorMessage = sprintf('batch delete shipment items: %s', $exception->getMessage());
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
