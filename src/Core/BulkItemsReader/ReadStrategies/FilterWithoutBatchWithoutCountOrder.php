<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Core\BulkItemsReader\ReadStrategies;

use Bitrix24\SDK\Core\Contracts\BulkItemsReaderInterface;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Generator;
use Psr\Log\LoggerInterface;

class FilterWithoutBatchWithoutCountOrder implements BulkItemsReaderInterface
{
    public function __construct(private readonly CoreInterface $core, private readonly LoggerInterface $log)
    {
    }

    /**
     *
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    public function getTraversableList(string $apiMethod, array $order, array $filter, array $select, ?int $limit = null): Generator
    {
        $this->log->debug('FilterWithoutBatchWithoutCountOrder.getTraversableList.start', [
            'apiMethod' => $apiMethod,
            'order'     => $order,
            'filter'    => $filter,
            'select'    => $select,
            'limit'     => $limit,
        ]);

        // Default strategy from the documentation
        //
        //Features:
        //— ✅ counting the number of elements in the sample is disabled
        //— ⚠️ The ID of elements in the sample increases, i.e. the results were sorted by ID
        //— do not use batch
        //— ❗️ parse the server response to get the next ID → problems with parallelizing queries
        //— sequential execution of queries
        //
        //Backlog for optimization
        //— limited use of parallel queries
        //
        // Queries are sent to the server sequentially with the "order" parameter: {"ID": "ASC"} (sorting in ascending order of ID),
        // and each subsequent query uses the results of the previous one (filtering by ID, where ID > the maximum ID in the results
        // of the previous query).
        //
        // In this case, to speed things up, the start = -1 parameter is used to disable the time-consuming operation of calculating the total
        // number of records (the total field), which is returned by default in each server response when calling methods of the *.list type.
        //
        // Potentially, to speed things up, you can try to move along the list of entities in two threads in parallel:
        // from the beginning of the list and from the end, continuing to receive pages until the IDs in the two threads intersect.
        // This method will probably provide a two-fold speedup until the pool of requests to the server is exhausted and throttling will
        // need to be enabled.

        // get total elements count

        // got the first element ID in the selection by filter
        // todo checked that this is a *.list command
        // todo checked that the select contains an ID, i.e. the developer understands that the ID is used
        // todo checked that the sorting is set as "order": {"ID": "ASC"} i.e. the developer understands that the data will come in this order
        // todo checked that if there is a limit, then it is >1
        // todo checked that there is no ID field in the filter, because we will work with it


        $firstElementId = $this->getFirstElementId($apiMethod, $filter, $select);
        if ($firstElementId === null) {
            $this->log->debug('FilterWithoutBatchWithoutCountOrder.getTraversableList.emptySelect');

            return;
        }

        $lastElementId = $this->getLastElementId($apiMethod, $filter, $select);
        if ($lastElementId === null) {
            $this->log->debug('FilterWithoutBatchWithoutCountOrder.getTraversableList.emptySelect');

            return;
        }

        // make requests to B24
        // todo take into account retraii
        // todo limits on the number of requests per second + request pool
        $currentElementId = $firstElementId;
        $isStop = false;
        while (!$isStop) {
            $filterQuery = '>ID';
            if ($currentElementId === $firstElementId) {
                $filterQuery = '>=ID';
            }

            $resultPage = $this->core->call(
                $apiMethod,
                [
                    'order'  => ['ID' => 'ASC'],
                    'filter' => array_merge(
                        [$filterQuery => $currentElementId],
                        $filter
                    ),
                    'select' => array_unique(array_merge(['ID'], $select)),
                    'start'  => -1,
                ]
            );


            foreach ($resultPage->getResponseData()->getResult() as $cnt => $item) {


                $currentElementId = (int)$item['ID'];
                yield $cnt => $item;
            }

            $this->log->debug('FilterWithoutBatchWithoutCountOrder.step', [
                'duration'         => $resultPage->getResponseData()->getTime()->duration,
                'currentElementId' => $currentElementId,
                'lastElementId'    => $lastElementId,
            ]);
            if ($currentElementId >= $lastElementId) {
                $isStop = true;
            }
        }
    }

    /**
     * Get first element id in filtered result ordered by id asc
     *
     *
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    private function getFirstElementId(string $apiMethod, array $filter, array $select): ?int
    {
        $this->log->debug('FilterWithoutBatchWithoutCountOrder.getFirstElementId.start', [
            'apiMethod' => $apiMethod,
            'filter'    => $filter,
            'select'    => $select,
        ]);

        $response = $this->core->call(
            $apiMethod,
            [
                'order'  => ['ID' => 'ASC'],
                'filter' => $filter,
                'select' => $select,
                'start'  => 0,
            ]
        );

        $elementId = $response->getResponseData()->getResult()[0]['ID'];

        $this->log->debug('FilterWithoutBatchWithoutCountOrder.getFirstElementId.finish', [
            'elementId' => $elementId,
        ]);

        return $elementId === null ? null : (int)$elementId;
    }

    /**
     * Get first element id in filtered result ordered by id asc
     *
     *
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    private function getLastElementId(string $apiMethod, array $filter, array $select): ?int
    {
        $this->log->debug('FilterWithoutBatchWithoutCountOrder.getLastElementId.start', [
            'apiMethod' => $apiMethod,
            'filter'    => $filter,
            'select'    => $select,
        ]);

        $response = $this->core->call(
            $apiMethod,
            [
                'order'  => ['ID' => 'DESC'],
                'filter' => $filter,
                'select' => $select,
                'start'  => 0,
            ]
        );

        $elementId = $response->getResponseData()->getResult()[0]['ID'];

        $this->log->debug('FilterWithoutBatchWithoutCountOrder.getLastElementId.finish', [
            'elementId' => $elementId,
        ]);

        return $elementId === null ? null : (int)$elementId;
    }
}