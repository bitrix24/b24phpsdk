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

namespace Bitrix24\SDK\Services\Catalog\UserfieldDocument;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Generator;

/**
 * Class Batch
 *
 * Overrides base Batch to handle parameter naming differences in catalog.userfield.document.* REST methods:
 * - update uses 'documentId' instead of the base class's hardcoded 'id' command argument key
 *
 * @see https://apidocs.bitrix24.com/api-reference/catalog/userfield-document/catalog-userfield-document-update.html
 */
class Batch extends \Bitrix24\SDK\Core\Batch
{
    #[\Override]
    protected function determineKeyId(string $apiMethod, ?array $additionalParameters): string
    {
        return 'documentId';
    }

    /**
     * Update entity items with batch call using 'documentId' instead of the base class's 'id'
     *
     * @param array<int, array> $entityItems keyed by document id, each value must contain a 'fields' key
     *
     * @return Generator<int, ResponseData>
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

            foreach ($entityItems as $documentId => $entityItem) {
                if (!is_int($documentId)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'invalid type «%s» of document id «%s», document id must be integer type',
                            gettype($documentId),
                            $documentId
                        )
                    );
                }

                if (!array_key_exists('fields', $entityItem)) {
                    throw new InvalidArgumentException(
                        sprintf('array key «fields» not found in entity item with id %s', $documentId)
                    );
                }

                $this->registerCommand($apiMethod, ['documentId' => $documentId, 'fields' => $entityItem['fields']]);
            }

            foreach ($this->getTraversable(true) as $cnt => $updatedItemResult) {
                yield $cnt => $updatedItemResult;
            }
        } catch (InvalidArgumentException $exception) {
            $errorMessage = sprintf('batch update document userfield items: %s', $exception->getMessage());
            $this->logger->error(
                $errorMessage,
                [
                    'trace' => $exception->getTrace(),
                ]
            );
            throw $exception;
        } catch (\Throwable $exception) {
            $errorMessage = sprintf('batch update document userfield items: %s', $exception->getMessage());
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
