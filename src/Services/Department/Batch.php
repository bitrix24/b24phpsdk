<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Department;

use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Commands\CommandCollection;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
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
 * @package Bitrix24\SDK\Services\Department
 */
class Batch extends \Bitrix24\SDK\Core\Batch
{
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
            'updateItems.start',
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
                            'invalid type «%s» of department id «%s», the id must be integer type',
                            gettype($entityItemId),
                            $entityItemId
                        )
                    );
                }

                $entityItem['ID'] = $entityItemId;
                $cmdArguments = $entityItem;

                $this->registerCommand($apiMethod, $cmdArguments);
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

        $this->logger->debug('updateCurrencyItems.finish');
    }


}
