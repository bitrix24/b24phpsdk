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

namespace Bitrix24\SDK\Services\Booking\WaitlistExternalData\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\Booking;
use Bitrix24\SDK\Services\Booking\WaitlistExternalData\Result\WaitlistExternalDataItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['booking']))]
class Batch
{
    public function __construct(protected Booking\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch read external data links of several waitlist entries
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/waitlist/external-data/booking-v1-waitlist-externaldata-list.html
     *
     * @param int[] $waitListIds
     *
     * @return Generator<int, WaitlistExternalDataItemResult[]> waitlist id => external data links of this waitlist entry
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'booking.v1.waitlist.externalData.list',
        'https://apidocs.bitrix24.com/api-reference/booking/waitlist/external-data/booking-v1-waitlist-externaldata-list.html',
        'Batch read external data links of several waitlist entries'
    )]
    public function list(array $waitListIds): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'waitListIds' => $waitListIds,
            ]
        );

        $externalDataGenerator = $this->batch->getEntityItemsByParentIds(
            'booking.v1.waitlist.externalData.list',
            'waitListId',
            $waitListIds
        );

        foreach ($externalDataGenerator as $waitListId => $externalData) {
            yield $waitListId => array_map(
                static fn (array $item): WaitlistExternalDataItemResult => new WaitlistExternalDataItemResult($item),
                $externalData
            );
        }
    }

    /**
     * Batch set external data links for several waitlist entries
     *
     * Elements in array with structure
     * waitlist id => [ // external data links replacing the ones currently stored for the waitlist entry
     *  ['moduleId' => 'crm', 'entityTypeId' => 'deal', 'value' => '1']
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/waitlist/external-data/booking-v1-waitlist-externaldata-set.html
     *
     * @param array<int, array<int, array<string, string>>> $waitListExternalData
     *
     * @return Generator<int, UpdatedItemBatchResult> waitlist id => set result
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'booking.v1.waitlist.externalData.set',
        'https://apidocs.bitrix24.com/api-reference/booking/waitlist/external-data/booking-v1-waitlist-externaldata-set.html',
        'Batch set external data links for several waitlist entries'
    )]
    public function set(array $waitListExternalData): Generator
    {
        $setGenerator = $this->batch->setEntityItems(
            'booking.v1.waitlist.externalData.set',
            'waitListId',
            'externalData',
            $waitListExternalData
        );

        foreach ($setGenerator as $waitListId => $item) {
            yield $waitListId => new UpdatedItemBatchResult($item);
        }
    }
}
