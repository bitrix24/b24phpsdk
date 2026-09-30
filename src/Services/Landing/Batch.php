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

namespace Bitrix24\SDK\Services\Landing;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Generator;

/**
 * Class Batch
 *
 * Overrides base Batch to handle parameter naming and pagination differences in landing.* REST methods:
 * - *.getList methods wrap select, filter and order into the 'params' object and paginate
 *   with ORM-level 'params.limit' and 'params.offset' instead of 'start' / 'total'
 * - landing.landing.delete and landing.landing.update use 'lid' instead of 'ID'
 * - landing.site.delete and landing.site.update use lowercase 'id'
 * - *.unregister methods use string 'code' instead of integer 'ID'
 *
 * @see https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-get-list.html
 * @see https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-delete.html
 *
 * @package Bitrix24\SDK\Services\Landing
 */
class Batch extends \Bitrix24\SDK\Core\Batch
{
    /**
     * REST methods that identify the entity by page identifier 'lid'
     */
    protected const METHODS_WITH_LID_KEY = [
        'landing.landing.delete',
        'landing.landing.update',
    ];

    /**
     * REST methods that identify the entity by string 'code'
     */
    protected const METHODS_WITH_CODE_KEY = [
        'landing.demos.unregister',
        'landing.repo.unregister',
        'landing.repowidget.unregister',
    ];

    /**
     * Get traversable list for landing *.getList methods with offset pagination inside the 'params' object
     *
     * The first page is requested with a single call. If the page is full, the next pages are requested
     * with batch packets. Iteration stops on the first page that contains fewer elements than a full page
     * or when the limit is reached.
     *
     * @param array<string, mixed> $params select, filter, order and other method-specific selection parameters
     *
     * @return Generator<int, array<string, mixed>>
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
        $firstPageElements = $this->core->call(
            $apiMethod,
            ['params' => $this->buildPageParams($params, 0)]
        )->getResponseData()->getResult();

        foreach ($firstPageElements as $firstPageElement) {
            yield $firstPageElement;
            $elementsCounter++;
            if ($limit !== null && $elementsCounter >= $limit) {
                $this->logger->debug('getTraversableListByOffset.finish - limit reached');
                return;
            }
        }

        if (count($firstPageElements) < self::MAX_ELEMENTS_IN_PAGE) {
            $this->logger->debug('getTraversableListByOffset.finish - single page');
            return;
        }

        $offset = self::MAX_ELEMENTS_IN_PAGE;
        while (true) {
            $this->clearCommands();

            $pagesCount = self::MAX_BATCH_PACKET_SIZE;
            if ($limit !== null) {
                $pagesCount = min($pagesCount, (int)ceil(($limit - $elementsCounter) / self::MAX_ELEMENTS_IN_PAGE));
            }

            for ($i = 0; $i < $pagesCount; $i++) {
                $this->registerCommand(
                    $apiMethod,
                    ['params' => $this->buildPageParams($params, $offset + $i * self::MAX_ELEMENTS_IN_PAGE)]
                );
            }

            $offset += $pagesCount * self::MAX_ELEMENTS_IN_PAGE;

            foreach ($this->getTraversable(true) as $batchResult) {
                $resultElements = $batchResult->getResult();
                foreach ($resultElements as $resultElement) {
                    yield $resultElement;
                    $elementsCounter++;
                    if ($limit !== null && $elementsCounter >= $limit) {
                        $this->logger->debug('getTraversableListByOffset.finish - limit reached');
                        return;
                    }
                }

                // Incomplete page means that there are no more elements
                if (count($resultElements) < self::MAX_ELEMENTS_IN_PAGE) {
                    $this->logger->debug('getTraversableListByOffset.finish - last page', [
                        'elementsCounter' => $elementsCounter,
                    ]);
                    return;
                }
            }
        }
    }

    /**
     * Get traversable list by executing one list command per parameter set
     *
     * Used for list methods without pagination support (for example landing.block.getlist for several pages)
     *
     * @param array<int|string, array<string, mixed>> $commandsParameters
     *
     * @return Generator<int, mixed>
     * @throws BaseException
     */
    public function getTraversableListByCommands(string $apiMethod, array $commandsParameters): Generator
    {
        $this->logger->debug(
            'getTraversableListByCommands.start',
            [
                'apiMethod' => $apiMethod,
                'commandsParameters' => $commandsParameters,
            ]
        );

        foreach ($this->processEntityItems($apiMethod, $commandsParameters) as $responseData) {
            foreach ($responseData->getResult() as $element) {
                yield $element;
            }
        }

        $this->logger->debug('getTraversableListByCommands.finish');
    }

    /**
     * Execute one command per parameter set with batch call
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
     * Delete entity items with batch call
     *
     * Uses 'lid' for landing.landing.delete, string 'code' for *.unregister methods and lowercase 'id' otherwise
     *
     * @param array<int, int|string> $entityItemId
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

        $keyId = $this->determineEntityKey($apiMethod);
        $isCodeKey = $keyId === 'code';

        try {
            $this->clearCommands();
            foreach ($entityItemId as $cnt => $itemId) {
                if ($isCodeKey && (!is_string($itemId) || $itemId === '')) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'invalid type «%s» of entity code «%s» at position %s, entity code must be non-empty string',
                            gettype($itemId),
                            $itemId,
                            $cnt
                        )
                    );
                }

                if (!$isCodeKey && !is_int($itemId)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'invalid type «%s» of entity id «%s» at position %s, entity id must be integer type',
                            gettype($itemId),
                            $itemId,
                            $cnt
                        )
                    );
                }

                $this->registerCommand($apiMethod, [$keyId => $itemId]);
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
     * Uses 'lid' for landing.landing.update and lowercase 'id' otherwise.
     *
     * Update elements in array with structure:
     * element_id => [
     *   'fields' => [], // required: element fields to update
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

        $keyId = $this->determineEntityKey($apiMethod);

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

                $this->registerCommand($apiMethod, [
                    $keyId => $entityItemId,
                    'fields' => $entityItem['fields'],
                ]);
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
     * Determines the entity key for delete and update commands
     */
    protected function determineEntityKey(string $apiMethod): string
    {
        $apiMethod = strtolower($apiMethod);
        if (in_array($apiMethod, self::METHODS_WITH_LID_KEY, true)) {
            return 'lid';
        }

        if (in_array($apiMethod, self::METHODS_WITH_CODE_KEY, true)) {
            return 'code';
        }

        return 'id';
    }

    /**
     * Builds 'params' object for a single page
     *
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    protected function buildPageParams(array $params, int $offset): array
    {
        // Empty selection parameters are omitted so that the REST method applies its defaults
        $params = array_filter($params, static fn (mixed $value): bool => $value !== []);

        // Stable sort order is required for offset pagination, ID is used as a tie-breaker
        $order = $params['order'] ?? [];
        if (!array_key_exists('ID', array_change_key_case($order, CASE_UPPER))) {
            $order['ID'] = 'ASC';
        }

        $params['order'] = $order;

        $params['limit'] = self::MAX_ELEMENTS_IN_PAGE;
        $params['offset'] = $offset;

        return $params;
    }
}
