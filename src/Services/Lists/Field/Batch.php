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

namespace Bitrix24\SDK\Services\Lists\Field;

use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Commands\CommandCollection;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Generator;
use Psr\Log\LoggerInterface;

/**
 * Class Batch
 *
 * Extended Batch class for Field service that handles complex parameter structures
 *
 * @package Bitrix24\SDK\Services\Lists\Field
 */
class Batch extends \Bitrix24\SDK\Core\Batch
{
    /**
     * Update entity items with batch call for field API
     *
     * Update elements in array with structure:
     * [
     *  'IBLOCK_TYPE_ID' => string,
     *  'IBLOCK_ID' => int,
     *  'FIELD_ID' => string,
     *  'FIELDS' => [],
     *  'IBLOCK_CODE' => string (optional)
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
                // For field update, we use the complete array as parameters
                $this->registerCommand($apiMethod, $entityItem);
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
     * Delete entity items with batch call for field API
     *
     * Delete elements with structure:
     * [
     *  'IBLOCK_TYPE_ID' => string,
     *  'IBLOCK_ID' => int,
     *  'FIELD_ID' => string,
     *  'IBLOCK_CODE' => string (optional)
     * ]
     *
     * @param array<int, array<string, mixed>> $entityItems
     *
     * @return Generator<int, ResponseData>|ResponseData[]
     * @throws BaseException
     */
    #[\Override]
    public function deleteEntityItems(
        string $apiMethod,
        array $entityItems,
        ?array $additionalParameters = null
    ): Generator {
        $this->logger->debug(
            'deleteEntityItems.start',
            [
                'apiMethod' => $apiMethod,
                'entityItems' => $entityItems,
                'additionalParameters' => $additionalParameters,
            ]
        );

        try {
            $this->clearCommands();

            foreach ($entityItems as $cnt => $entityItem) {
                // For field delete, we use the complete array as parameters
                $this->registerCommand($apiMethod, $entityItem);
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
