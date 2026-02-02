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

namespace Bitrix24\SDK\Services\Lists\Section;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Generator;

/**
 * Custom Batch implementation for Section API
 *
 * The Section API has specific requirements for batch operations:
 * - Update operations work with specific section structure
 * - Delete operations require section identification parameters
 */
class Batch extends \Bitrix24\SDK\Core\Batch
{
    /**
     * Update entity items with Section API specific format
     *
     * Update elements in array with structure:
     * element_id => [
     *  'IBLOCK_TYPE_ID' => string,
     *  'IBLOCK_ID' => int,              // or use IBLOCK_CODE
     *  'SECTION_ID' => int,             // or use SECTION_CODE
     *  'FIELDS' => [],                  // Section fields to update
     *  'IBLOCK_CODE' => string,         // optional
     *  'SECTION_CODE' => string         // optional
     * ]
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

            foreach ($entityItems as $entityId => $entityItem) {
                if (!array_key_exists('FIELDS', $entityItem)) {
                    throw new InvalidArgumentException('array key «FIELDS» not found in entity item with id ' . $entityId);
                }

                $commandParams = $entityItem;

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
     * Delete entity items with Section API specific format
     *
     * Delete elements with structure:
     * [
     *  'IBLOCK_TYPE_ID' => string,
     *  'IBLOCK_ID' => int,              // or use IBLOCK_CODE
     *  'SECTION_ID' => int,             // or use SECTION_CODE
     *  'IBLOCK_CODE' => string,         // optional
     *  'SECTION_CODE' => string         // optional
     * ]
     */
    #[\Override]
    public function deleteEntityItems(string $apiMethod, array $entityItems, ?array $additionalParameters = null): Generator
    {
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

            foreach ($entityItems as $entityItem) {
                if (!is_array($entityItem)) {
                    throw new InvalidArgumentException(sprintf('invalid type «%s» of entity item', gettype($entityItem)));
                }

                if (!array_key_exists('IBLOCK_TYPE_ID', $entityItem)) {
                    throw new InvalidArgumentException('array key «IBLOCK_TYPE_ID» not found in entity item');
                }

                $commandParams = $entityItem;

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
