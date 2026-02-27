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

namespace Bitrix24\SDK\Services\Main\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\SelectBuilderInterface;
use Bitrix24\SDK\Core\Contracts\SortOrder;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Filters\FilterBuilderInterface;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Main\Result\EventLogResult;
use Bitrix24\SDK\Services\Main\Result\EventLogsResult;

#[ApiServiceMetadata(new Scope(['main']))]
class EventLog extends AbstractService
{
    /**
     * Returns a single event log entry by identifier.
     *
     * @see https://apidocs.bitrix24.com/api-reference/rest-v3/main/main-eventlog-get.html
     *
     * @param positive-int                            $id
     * @param array<int,string>|EventLogSelectBuilder $select
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'main.eventlog.get',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/main/main-eventlog-get.html',
        'Returns a single event log entry by identifier.',
        ApiVersion::v3
    )]
    public function get(int $id, array|EventLogSelectBuilder $select = []): EventLogResult
    {
        $this->guardPositiveId($id);

        if ($select instanceof SelectBuilderInterface) {
            $select = $select->buildSelect();
        }

        return new EventLogResult(
            $this->core->call(
                'main.eventlog.get',
                [
                    'id'     => $id,
                    'select' => $select,
                ],
                ApiVersion::v3
            )
        );
    }

    /**
     * Returns a list of event log entries by filter conditions.
     *
     * @see https://apidocs.bitrix24.com/api-reference/rest-v3/main/main-eventlog-list.html
     *
     * @param array<int,string>|EventLogSelectBuilder  $select
     * @param array|FilterBuilderInterface             $filter     Filter conditions (REST 3.0 format)
     * @param array<string,SortOrder>                  $order      ["field" => SortOrder::Ascending]
     * @param array                                    $pagination ["page" => int, "limit" => int, "offset" => int]
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'main.eventlog.list',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/main/main-eventlog-list.html',
        'Returns a list of event log entries by filter conditions.',
        ApiVersion::v3
    )]
    public function list(
        array|EventLogSelectBuilder  $select = [],
        array|FilterBuilderInterface $filter = [],
        array                        $order = [],
        array                        $pagination = []
    ): EventLogsResult {
        if ($select instanceof SelectBuilderInterface) {
            $select = $select->buildSelect();
        }

        if ($filter instanceof FilterBuilderInterface) {
            $filter = $filter->toArray();
        }

        $normalizedOrder = [];
        foreach ($order as $field => $direction) {
            $normalizedOrder[$field] = $direction instanceof SortOrder ? $direction->value : $direction;
        }

        return new EventLogsResult(
            $this->core->call(
                'main.eventlog.list',
                array_filter(
                    [
                        'select'     => $select,
                        'filter'     => $filter,
                        'order'      => $normalizedOrder,
                        'pagination' => $pagination,
                    ],
                    static fn (array $v): bool => $v !== []
                ),
                ApiVersion::v3
            )
        );
    }

    /**
     * Returns new event log entries after a reference cursor point.
     *
     * @see https://apidocs.bitrix24.com/api-reference/rest-v3/main/main-eventlog-tail.html
     *
     * @param array<int,string>|EventLogSelectBuilder  $select (required)
     * @param array|FilterBuilderInterface             $filter (required, pass [] or new EventLogFilter() for no filter)
     * @param EventLogTailCursor $eventLogTailCursor value object with field/order/value/limit
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'main.eventlog.tail',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/main/main-eventlog-tail.html',
        'Returns new event log entries after a reference cursor point.',
        ApiVersion::v3
    )]
    public function tail(
        array|EventLogSelectBuilder  $select,
        array|FilterBuilderInterface $filter,
        EventLogTailCursor           $eventLogTailCursor
    ): EventLogsResult {
        if ($select instanceof SelectBuilderInterface) {
            $select = $select->buildSelect();
        }

        if ($filter instanceof FilterBuilderInterface) {
            $filter = $filter->toArray();
        }

        return new EventLogsResult(
            $this->core->call(
                'main.eventlog.tail',
                [
                    'select' => $select,
                    'filter' => $filter,
                    'cursor' => $eventLogTailCursor->toArray(),
                ],
                ApiVersion::v3
            )
        );
    }
}
