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

namespace Bitrix24\SDK\Services\CRM\Currency\Localizations;

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
 * @package Bitrix24\SDK\Services\CRM\Currency\Localizations
 */
class Batch extends \Bitrix24\SDK\Core\Batch
{
    /**
     * Delete entity items with batch call
     *
     *
     * @return Generator<int, ResponseData>|ResponseData[]
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    public function deleteLocalizationItems(
        string $apiMethod,
        array $entityItemId,
        ?array $additionalParameters = null
    ): Generator {
        $this->logger->debug(
            'deleteLocalizationItems.start',
            [
                'apiMethod' => $apiMethod,
                'entityItems' => $entityItemId,
                'additionalParameters' => $additionalParameters,
            ]
        );

        try {
            $this->clearCommands();
            foreach ($entityItemId as $cnt => $code) {
                if (!is_array($code)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'invalid type «%s» of currency localizations «%s» at position %s, the value must be array type',
                            gettype($code),
                            print_r($code, true),
                            $cnt
                        )
                    );
                }
                if (is_array($code) && !is_string($code['id'])) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'invalid type «%s» of currency code «%s» at position %s, the code must be string type',
                            gettype($code),
                            print_r($code),
                            $cnt
                        )
                    );
                }
                if (is_array($code) && !is_array($code['lids'])) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'invalid type «%s» of localization codes «%s» at position %s, the codes must be array type',
                            gettype($code),
                            print_r($code),
                            $cnt
                        )
                    );
                }

                $parameters = ['id' => $code['id'], 'lids' => $code['lids']];
                $this->registerCommand($apiMethod, $parameters);
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

        $this->logger->debug('deleteLocalizationItems.finish');
    }

}
