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

namespace Bitrix24\SDK\Services\Main\UserHistory\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\SortOrder;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Filters\FilterBuilderInterface;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Main\UserHistory\Result\UserHistoriesResult;
use Bitrix24\SDK\Services\Main\UserHistoryField\Service\UserHistoryField;
use Bitrix24\SDK\Services\Main\UserHistoryField\Result\UserHistoryFieldsResult;
use Bitrix24\SDK\Services\Main\UserHistory\Result\UserHistoryTailResult;

#[ApiServiceMetadata(new Scope(['main']))]
class UserHistory extends AbstractService
{
    /**
     * The required userId equality filter is added automatically.
     * Additional filters are combined with it using AND.
     *
     * @param positive-int $userId
     * @param string[]|UserHistorySelectBuilder $select
     * @param array|FilterBuilderInterface $filter REST v3 conditions or a logic group
     * @param array<string, SortOrder|string> $order
     * @param array{page?: int, limit?: int, offset?: int} $pagination
     * @throws BaseException
     */
    #[ApiEndpointMetadata(
        'main.user.history.list',
        'https://apidocs.bitrix24.com/api-reference/rest-v3.html',
        'Read user history with a required userId filter',
        ApiVersion::v3
    )]
    public function list(
        int $userId,
        array|UserHistorySelectBuilder $select = [],
        array|FilterBuilderInterface $filter = [],
        array $order = [],
        array $pagination = [],
    ): UserHistoriesResult {
        $this->guardPositiveId($userId);
        if ($select instanceof UserHistorySelectBuilder) {
            $select = $select->buildSelect();
        }

        $normalizedOrder = [];
        foreach ($order as $field => $direction) {
            $normalizedOrder[$field] = $direction instanceof SortOrder ? $direction->value : $direction;
        }

        return new UserHistoriesResult($this->core->call(
            'main.user.history.list',
            array_filter([
                'filter' => $this->scopedFilter($userId, $filter),
                'select' => array_values($select),
                'order' => $normalizedOrder,
                'pagination' => $pagination,
            ], static fn (array $value): bool => $value !== []),
            ApiVersion::v3
        ));
    }

    /**
     * @throws BaseException
     */
    public function fields(): UserHistoryFieldsResult
    {
        return (new UserHistoryField($this->core, $this->log))->list();
    }

    private function scopedFilter(int $userId, array|FilterBuilderInterface $filter): array
    {
        if ($filter instanceof FilterBuilderInterface) {
            $filter = $filter->toArray();
        }

        // A root OR group must remain nested under the mandatory identifier's AND.
        if (!array_is_list($filter)) {
            $filter = [$filter];
        }

        return [['userId', '=', $userId], ...$filter];
    }

    /**
     * Read incremental history for one user. Keep the last non-null checkpoint.
     *
     * @param positive-int $userId
     * @param string[]|UserHistorySelectBuilder $select
     * @param array|FilterBuilderInterface $filter Additional REST v3 conditions
     * @throws BaseException
     */
    #[ApiEndpointMetadata(
        'main.user.history.tail',
        'https://apidocs.bitrix24.com/api-reference/rest-v3.html',
        'Read incremental user history from a cursor',
        ApiVersion::v3
    )]
    public function tail(
        int $userId,
        UserHistoryTailCursor $cursor,
        array|UserHistorySelectBuilder $select = [],
        array|FilterBuilderInterface $filter = [],
    ): UserHistoryTailResult {
        $this->guardPositiveId($userId);
        if ($select instanceof UserHistorySelectBuilder) {
            $select = $select->buildSelect();
        }

        return new UserHistoryTailResult($this->core->call(
            'main.user.history.tail',
            array_filter([
                'filter' => $this->scopedFilter($userId, $filter),
                'select' => array_values($select),
                'cursor' => $cursor->toArray(),
            ], static fn (array $value): bool => $value !== []),
            ApiVersion::v3
        ));
    }
}
