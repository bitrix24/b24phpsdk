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

namespace Bitrix24\SDK\Services\Catalog\Measure;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Generator;

/**
 * Class Batch
 *
 * Overrides base Batch to handle differences in catalog.measure.* REST methods:
 * - the id key is lowercase 'id', not 'ID' as assumed by the base class default
 * - catalog.measure.list wraps list items under the 'measures' key instead of returning
 *   a flat array in 'result', as the base class assumes for non-CRM methods
 *
 * @see https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-list.html
 * @see https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-delete.html
 */
class Batch extends \Bitrix24\SDK\Core\Batch
{
    /**
     * Determines the ID key - lowercase 'id' for catalog.measure.* methods
     */
    #[\Override]
    protected function determineKeyId(string $apiMethod, ?array $additionalParameters): string
    {
        return 'id';
    }

    /**
     * Extracts list items from the 'measures' key of the batch/list result
     */
    #[\Override]
    protected function extractElementsFromBatchResult(ResponseData $responseData, bool $isCrmItemsInBatch): array
    {
        return $responseData->getResult()['measures'] ?? [];
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
                            'invalid type «%s» of measure id «%s» at position %s, measure id must be integer type',
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
            $errorMessage = sprintf('batch delete measure items: %s', $exception->getMessage());
            $this->logger->error(
                $errorMessage,
                [
                    'trace' => $exception->getTrace(),
                ]
            );
            throw $exception;
        } catch (\Throwable $exception) {
            $errorMessage = sprintf('batch delete measure items: %s', $exception->getMessage());
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
