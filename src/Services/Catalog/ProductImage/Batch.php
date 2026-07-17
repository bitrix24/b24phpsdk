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

namespace Bitrix24\SDK\Services\Catalog\ProductImage;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Generator;

/**
 * Class Batch
 *
 * Overrides base Batch to handle parameter naming differences in catalog.productImage.delete:
 * the method requires both 'productId' and 'id' parameters instead of the standard single 'ID'.
 *
 * @package Bitrix24\SDK\Services\Catalog\ProductImage
 */
class Batch extends \Bitrix24\SDK\Core\Batch
{
    /**
     * Delete product images with batch call
     *
     * @param array<int, array{productId: int, id: int}> $entityItemId
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
            foreach ($entityItemId as $cnt => $item) {
                if (!array_key_exists('productId', $item) || !array_key_exists('id', $item)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'entity item at position %s must contain «productId» and «id» keys',
                            $cnt
                        )
                    );
                }

                $this->registerCommand($apiMethod, ['productId' => $item['productId'], 'id' => $item['id']]);
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
}
