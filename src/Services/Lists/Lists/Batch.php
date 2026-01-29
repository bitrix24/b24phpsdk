<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Lists\Lists;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;

/**
 * Custom Batch implementation for Lists API
 *
 * The Lists API has specific requirements for batch operations:
 * - Uses 'FIELDS' (uppercase) instead of 'fields' for update operations
 * - Uses complex IDs with IBLOCK_TYPE_ID and IBLOCK_ID for delete operations
 */
class Batch extends \Bitrix24\SDK\Core\Batch
{
    /**
     * Update entity items with Lists API specific format
     */
    #[\Override]
    public function updateEntityItems(string $apiMethod, array $entityItems): \Generator
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

            foreach ($entityItems as $entityId => $entityItem) {
                if (!array_key_exists('FIELDS', $entityItem)) {
                    throw new InvalidArgumentException('array key «FIELDS» not found in entity item with id ' . $entityId);
                }

                $commandParams = $entityItem;
                $commandParams['IBLOCK_ID'] = $entityId;

                $this->registerCommand($apiMethod, $commandParams);
            }

            foreach ($this->getTraversable(true) as $cnt => $updatedItemResult) {
                yield $cnt => $updatedItemResult;
            }
        } catch (InvalidArgumentException $exception) {
            $this->logger->error('batch update entity items: ' . $exception->getMessage(), ['trace' => $exception->getTrace()]);
            throw $exception;
        } catch (\Throwable $exception) {
            $errorMessage = sprintf('batch update entity items: %s', $exception->getMessage());
            $this->logger->error($errorMessage, ['trace' => $exception->getTrace()]);
            throw new BaseException($errorMessage, $exception->getCode(), $exception);
        }

        $this->logger->debug('updateEntityItems.finish');
    }

    /**
     * Delete entity items with Lists API specific format
     */
    #[\Override]
    public function deleteEntityItems(string $apiMethod, array $entityIds, ?array $additionalParameters = null): \Generator
    {
        $this->logger->debug(
            'deleteEntityItems.start',
            [
                'apiMethod' => $apiMethod,
                'entityItems' => $entityIds,
                'additionalParameters' => $additionalParameters,
            ]
        );

        try {
            $this->clearCommands();

            foreach ($entityIds as $entityId) {
                if (!is_array($entityId)) {
                    throw new InvalidArgumentException(sprintf('invalid type «%s» of entity id «%s»', gettype($entityId), $entityId));
                }

                if (!array_key_exists('IBLOCK_TYPE_ID', $entityId)) {
                    throw new InvalidArgumentException('array key «IBLOCK_TYPE_ID» not found in entity id');
                }

                if (!array_key_exists('IBLOCK_ID', $entityId)) {
                    throw new InvalidArgumentException('array key «IBLOCK_ID» not found in entity id');
                }

                $commandParams = [
                    'IBLOCK_TYPE_ID' => $entityId['IBLOCK_TYPE_ID'],
                    'IBLOCK_ID' => $entityId['IBLOCK_ID'],
                ];

                // Add ELEMENT_ID if provided
                if (array_key_exists('ELEMENT_ID', $entityId)) {
                    $commandParams['ELEMENT_ID'] = $entityId['ELEMENT_ID'];
                }

                $this->registerCommand($apiMethod, $commandParams);
            }

            foreach ($this->getTraversable(true) as $cnt => $deletedItemResult) {
                yield $cnt => $deletedItemResult;
            }
        } catch (InvalidArgumentException $exception) {
            $this->logger->error('batch delete entity items: ' . $exception->getMessage(), ['trace' => $exception->getTrace()]);
            throw $exception;
        } catch (\Throwable $exception) {
            $errorMessage = sprintf('batch delete entity items: %s', $exception->getMessage());
            $this->logger->error($errorMessage, ['trace' => $exception->getTrace()]);
            throw new BaseException($errorMessage, $exception->getCode(), $exception);
        }

        $this->logger->debug('deleteEntityItems.finish');
    }
}
