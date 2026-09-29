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

namespace Bitrix24\SDK\Services\Sale\Delivery;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Generator;

/**
 * Class Batch
 *
 * Overrides base Batch to handle parameter naming differences in sale.delivery.* REST methods:
 * - update uses uppercase 'ID' and passes the fields to update as top-level parameters
 * - config.update uses uppercase 'ID' and 'CONFIG' parameters
 * - list uses uppercase 'SELECT', 'FILTER', 'ORDER' parameters and does not support pagination
 *
 * @see https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery/sale-delivery-update.html
 * @see https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery/sale-delivery-config-update.html
 * @see https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery/sale-delivery-get-list.html
 */
class Batch extends \Bitrix24\SDK\Core\Batch
{
    /**
     * Get traversable list of delivery services
     *
     * The sale.delivery.getlist method does not support pagination and returns all items in one response,
     * so the list is read with a single call.
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
        $this->logger->debug(
            'getTraversableList.start',
            [
                'apiMethod' => $apiMethod,
                'order' => $order,
                'filter' => $filter,
                'select' => $select,
                'limit' => $limit,
            ]
        );

        $params = [];
        if ($select !== null && $select !== []) {
            $params['SELECT'] = $select;
        }

        if ($filter !== null && $filter !== []) {
            $params['FILTER'] = $filter;
        }

        if ($order !== null && $order !== []) {
            $params['ORDER'] = $order;
        }

        $elementsCounter = 0;
        foreach ($this->core->call($apiMethod, $params)->getResponseData()->getResult() as $listElement) {
            ++$elementsCounter;
            if ($limit !== null && $elementsCounter > $limit) {
                return;
            }

            yield $listElement;
        }

        $this->logger->debug('getTraversableList.finish');
    }

    /**
     * Update entity items with batch call
     *
     * Update elements in array with structure
     * element_id => [
     *  // delivery service fields to update, or 'CONFIG' => [] for config update
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
            foreach ($entityItems as $entityItemId => $entityItem) {
                if (!is_int($entityItemId)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'invalid type "%s" of delivery service id "%s", the id must be integer type',
                            gettype($entityItemId),
                            $entityItemId
                        )
                    );
                }

                $this->registerCommand($apiMethod, array_merge(['ID' => $entityItemId], $entityItem));
            }

            foreach ($this->getTraversable(true) as $cnt => $updatedItemResult) {
                yield $cnt => $updatedItemResult;
            }
        } catch (InvalidArgumentException $exception) {
            $errorMessage = sprintf('batch update delivery services: %s', $exception->getMessage());
            $this->logger->error(
                $errorMessage,
                [
                    'trace' => $exception->getTrace(),
                ]
            );
            throw $exception;
        } catch (\Throwable $exception) {
            $errorMessage = sprintf('batch update delivery services: %s', $exception->getMessage());
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
}
