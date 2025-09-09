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

namespace Bitrix24\SDK\Services\Sale\Payment;

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
 * @package Bitrix24\SDK\Services\Sale\Payment
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
    public function deleteEntityItems(
        string $apiMethod,
        array $entityItemId,
        ?array $additionalParameters = null
    ): Generator {
        $this->logger->debug(
            'deleteEntityItems.start',
            [
                'apiMethod'             => $apiMethod,
                'entityItemId'          => $entityItemId,
                'additionalParameters'  => $additionalParameters
            ]
        );
        
        // create commands
        $commandParams = [];
        foreach ($entityItemId as $id) {
            $commandParams[] = ['id' => $id];
        }

        // Build commands
        $commands = new CommandCollection();
        foreach ($commandParams as $index => $commandParam) {
            $commands->add(
                new Command(
                    $apiMethod,
                    $commandParam,
                    sprintf('%s_%s', $apiMethod, $index)
                )
            );
        }

        // Process batch commands and return results
        foreach ($this->processBatch($commands) as $item) {
            yield $item->getId() => $item->getResponseData();
        }

        $this->logger->debug('deleteEntityItems.finish');
    }
}
