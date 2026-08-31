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

namespace Bitrix24\SDK\Services\MailService;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Generator;
use Psr\Log\LoggerInterface;

/**
 * Custom Batch for MailService scope.
 *
 * Overrides updateEntityItems to pass ID as a top-level key (flat structure),
 * matching the mailservice.update parameter format which does not use nested 'fields'.
 *
 * @see https://apidocs.bitrix24.com/api-reference/mailservice/mailservice-update.html
 */
class Batch extends \Bitrix24\SDK\Core\Batch
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Update mail service items with batch call.
     *
     * Expects array structure:
     * [
     *   $mailServiceId => ['NAME' => '...', 'ACTIVE' => 'Y', ...],
     *   ...
     * ]
     *
     * @param array<int, array<string, mixed>> $entityItems
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
            foreach ($entityItems as $entityItemId => $entityItem) {
                if (!is_int($entityItemId)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'invalid type «%s» of mail service id «%s», the id must be integer type',
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

        $this->logger->debug('updateEntityItems.finish');
    }
}
