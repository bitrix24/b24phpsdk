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

namespace Bitrix24\SDK\Services\Timeman\Record\Service;

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
use Bitrix24\SDK\Services\Timeman\Record\Result\RecordsResult;

#[ApiServiceMetadata(new Scope(['timeman']))]
class Record extends AbstractService
{
    /**
     * Returns a list of employee work-time records.
     *
     * The filter must contain the `userId` condition; otherwise the API responds with a
     * validation error (`BITRIX_REST_V3_EXCEPTION_VALIDATION_REQUESTVALIDATIONEXCEPTION`).
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/timeman/timeman-record-list.html
     *
     * @param array<int,string>|RecordSelectBuilder $select
     * @param array|FilterBuilderInterface          $filter     Filter conditions (REST v3 format), must include userId
     * @param array<string,SortOrder|string>        $order      ["field" => SortOrder::Ascending]
     * @param array                                 $pagination ["page" => int, "limit" => int, "offset" => int]
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'timeman.record.list',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/timeman/timeman-record-list.html',
        'Returns a list of employee work-time records.',
        ApiVersion::v3
    )]
    public function list(
        array|RecordSelectBuilder    $select = [],
        array|FilterBuilderInterface $filter = [],
        array                        $order = [],
        array                        $pagination = []
    ): RecordsResult {
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

        return new RecordsResult(
            $this->core->call(
                'timeman.record.list',
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
}
