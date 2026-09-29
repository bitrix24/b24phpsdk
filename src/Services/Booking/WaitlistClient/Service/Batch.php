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

namespace Bitrix24\SDK\Services\Booking\WaitlistClient\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\Booking;
use Bitrix24\SDK\Services\Booking\WaitlistClient\Result\WaitlistClientItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['booking']))]
class Batch
{
    public function __construct(protected Booking\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch read clients linked to several waitlist entries
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/waitlist/client/booking-v1-waitlist-client-list.html
     *
     * @param int[] $waitListIds
     *
     * @return Generator<int, WaitlistClientItemResult[]> waitlist id => clients of this waitlist entry
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'booking.v1.waitlist.client.list',
        'https://apidocs.bitrix24.com/api-reference/booking/waitlist/client/booking-v1-waitlist-client-list.html',
        'Batch read clients linked to several waitlist entries'
    )]
    public function list(array $waitListIds): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'waitListIds' => $waitListIds,
            ]
        );

        $clientsGenerator = $this->batch->getEntityItemsByParentIds(
            'booking.v1.waitlist.client.list',
            'waitListId',
            $waitListIds
        );

        foreach ($clientsGenerator as $waitListId => $clients) {
            yield $waitListId => array_map(
                static fn (array $client): WaitlistClientItemResult => new WaitlistClientItemResult($client),
                $clients
            );
        }
    }

    /**
     * Batch set clients for several waitlist entries
     *
     * Elements in array with structure
     * waitlist id => [ // clients replacing the ones currently linked to the waitlist entry
     *  ['id' => '1', 'type' => ['module' => 'crm', 'code' => 'CONTACT']]
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/waitlist/client/booking-v1-waitlist-client-set.html
     *
     * @param array<int, array<int, array<string, mixed>>> $waitListClients
     *
     * @return Generator<int, UpdatedItemBatchResult> waitlist id => set result
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'booking.v1.waitlist.client.set',
        'https://apidocs.bitrix24.com/api-reference/booking/waitlist/client/booking-v1-waitlist-client-set.html',
        'Batch set clients for several waitlist entries'
    )]
    public function set(array $waitListClients): Generator
    {
        $setGenerator = $this->batch->setEntityItems(
            'booking.v1.waitlist.client.set',
            'waitListId',
            'clients',
            $waitListClients
        );

        foreach ($setGenerator as $waitListId => $item) {
            yield $waitListId => new UpdatedItemBatchResult($item);
        }
    }
}
