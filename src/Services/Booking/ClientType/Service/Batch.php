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

namespace Bitrix24\SDK\Services\Booking\ClientType\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Booking;
use Bitrix24\SDK\Services\Booking\ClientType\Result\ClientTypeItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['booking']))]
class Batch
{
    public function __construct(protected Booking\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list of client types
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/booking-v1-clienttype-list.html
     *
     * @return Generator<int, ClientTypeItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'booking.v1.clienttype.list',
        'https://apidocs.bitrix24.com/api-reference/booking/booking-v1-clienttype-list.html',
        'Batch list of client types'
    )]
    public function list(?int $limit = null): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'limit' => $limit,
            ]
        );

        foreach ($this->batch->getTraversableList('booking.v1.clienttype.list', [], [], [], $limit) as $key => $value) {
            yield $key => new ClientTypeItemResult($value);
        }
    }
}
