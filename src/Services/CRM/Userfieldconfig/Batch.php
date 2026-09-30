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

namespace Bitrix24\SDK\Services\CRM\Userfieldconfig;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Generator;

/**
 * userfieldconfig.update and userfieldconfig.delete require a lowercase `id` key and a
 * top-level `moduleId` key that the base \Bitrix24\SDK\Core\Batch does not build
 * (it defaults to an uppercase `ID` key for deletes and has no concept of `moduleId`),
 * so this scope needs its own batch command builder.
 */
class Batch extends \Bitrix24\SDK\Core\Batch
{
    /**
     * Update entity items with batch call
     *
     * @param array<int, array<string, mixed>> $entityItems keyed by userfieldconfig id
     *
     * @return Generator<int, ResponseData>|ResponseData[]
     * @throws BaseException
     */
    public function updateUserfieldConfigItems(string $apiMethod, string $moduleId, array $entityItems): Generator
    {
        $this->logger->debug(
            'updateUserfieldConfigItems.start',
            [
                'apiMethod' => $apiMethod,
                'moduleId' => $moduleId,
                'entityItems' => $entityItems,
            ]
        );

        try {
            $this->clearCommands();
            foreach ($entityItems as $entityItemId => $field) {
                if (!is_int($entityItemId)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'invalid type «%s» of userfieldconfig id «%s», id must be integer type',
                            gettype($entityItemId),
                            $entityItemId
                        )
                    );
                }

                $this->registerCommand($apiMethod, [
                    'moduleId' => $moduleId,
                    'id' => $entityItemId,
                    'field' => $field,
                ]);
            }

            foreach ($this->getTraversable(true) as $cnt => $updatedItemResult) {
                yield $cnt => $updatedItemResult;
            }
        } catch (InvalidArgumentException $exception) {
            $errorMessage = sprintf('batch update userfieldconfig items: %s', $exception->getMessage());
            $this->logger->error($errorMessage, ['trace' => $exception->getTrace()]);
            throw $exception;
        } catch (\Throwable $exception) {
            $errorMessage = sprintf('batch update userfieldconfig items: %s', $exception->getMessage());
            $this->logger->error($errorMessage, ['trace' => $exception->getTrace()]);
            throw new BaseException($errorMessage, $exception->getCode(), $exception);
        }

        $this->logger->debug('updateUserfieldConfigItems.finish');
    }

    /**
     * Delete entity items with batch call
     *
     * @param array<int, int> $entityItemIds
     *
     * @return Generator<int, ResponseData>|ResponseData[]
     * @throws BaseException
     */
    public function deleteUserfieldConfigItems(string $apiMethod, string $moduleId, array $entityItemIds): Generator
    {
        $this->logger->debug(
            'deleteUserfieldConfigItems.start',
            [
                'apiMethod' => $apiMethod,
                'moduleId' => $moduleId,
                'entityItemIds' => $entityItemIds,
            ]
        );

        try {
            $this->clearCommands();
            foreach ($entityItemIds as $cnt => $entityItemId) {
                if (!is_int($entityItemId)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'invalid type «%s» of userfieldconfig id «%s» at position %s, id must be integer type',
                            gettype($entityItemId),
                            $entityItemId,
                            $cnt
                        )
                    );
                }

                $this->registerCommand($apiMethod, [
                    'moduleId' => $moduleId,
                    'id' => $entityItemId,
                ]);
            }

            foreach ($this->getTraversable(true) as $cnt => $deletedItemResult) {
                yield $cnt => $deletedItemResult;
            }
        } catch (InvalidArgumentException $exception) {
            $errorMessage = sprintf('batch delete userfieldconfig items: %s', $exception->getMessage());
            $this->logger->error($errorMessage, ['trace' => $exception->getTrace()]);
            throw $exception;
        } catch (\Throwable $exception) {
            $errorMessage = sprintf('batch delete userfieldconfig items: %s', $exception->getMessage());
            $this->logger->error($errorMessage, ['trace' => $exception->getTrace()]);
            throw new BaseException($errorMessage, $exception->getCode(), $exception);
        }

        $this->logger->debug('deleteUserfieldConfigItems.finish');
    }
}
