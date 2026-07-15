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

namespace Bitrix24\SDK\Services\Catalog\Price;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Generator;

class Batch extends \Bitrix24\SDK\Core\Batch
{
    /**
     * Delete price items with batch call using lowercase «id» parameter key
     *
     * @param int[] $entityItemId
     *
     * @return Generator<int, ResponseData>
     * @throws BaseException
     */
    public function deletePriceItems(string $apiMethod, array $entityItemId): Generator
    {
        $this->logger->debug(
            'deletePriceItems.start',
            [
                'apiMethod' => $apiMethod,
                'entityItems' => $entityItemId,
            ]
        );

        try {
            $this->clearCommands();
            foreach ($entityItemId as $cnt => $id) {
                if (!is_int($id)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'invalid type «%s» of price id at position %s, id must be integer type',
                            gettype($id),
                            $cnt
                        )
                    );
                }

                $this->registerCommand($apiMethod, ['id' => $id]);
            }

            foreach ($this->getTraversable(true) as $cnt => $deletedItemResult) {
                yield $cnt => $deletedItemResult;
            }
        } catch (InvalidArgumentException $exception) {
            $errorMessage = sprintf('batch delete price items: %s', $exception->getMessage());
            $this->logger->error(
                $errorMessage,
                [
                    'trace' => $exception->getTrace(),
                ]
            );
            throw $exception;
        } catch (\Throwable $exception) {
            $errorMessage = sprintf('batch delete price items: %s', $exception->getMessage());
            $this->logger->error(
                $errorMessage,
                [
                    'trace' => $exception->getTrace(),
                ]
            );

            throw new BaseException($errorMessage, $exception->getCode(), $exception);
        }

        $this->logger->debug('deletePriceItems.finish');
    }
}
