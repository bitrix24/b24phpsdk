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

namespace Bitrix24\SDK\Services\Main\UserHistoryChange\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\SortOrder;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Filters\FilterBuilderInterface;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Main\UserHistoryChange\Result\UserHistoryChangesResult;
use Bitrix24\SDK\Services\Main\UserHistoryChangeField\Service\UserHistoryChangeField;
use Bitrix24\SDK\Services\Main\UserHistoryChangeField\Result\UserHistoryChangeFieldsResult;

#[ApiServiceMetadata(new Scope(['main']))]
class UserHistoryChange extends AbstractService
{
    /**
     * The required historyId equality filter is added automatically.
     * Additional filters are combined with it using AND.
     *
     * @param positive-int $historyId
     * @param string[]|UserHistoryChangeSelectBuilder $select
     * @param array|FilterBuilderInterface $filter REST v3 conditions or a logic group
     * @param array<string, SortOrder|string> $order
     * @param array{page?: int, limit?: int, offset?: int} $pagination
     * @throws BaseException
     */
    #[ApiEndpointMetadata(
        'main.user.history.fields.list',
        'https://apidocs.bitrix24.com/api-reference/rest-v3.html',
        'Read user history field changes with a required historyId filter',
        ApiVersion::v3
    )]
    public function list(
        int $historyId,
        array|UserHistoryChangeSelectBuilder $select = [],
        array|FilterBuilderInterface $filter = [],
        array $order = [],
        array $pagination = [],
    ): UserHistoryChangesResult {
        $this->guardPositiveId($historyId);
        if ($select instanceof UserHistoryChangeSelectBuilder) {
            $select = $select->buildSelect();
        }

        $normalizedOrder = [];
        foreach ($order as $field => $direction) {
            $normalizedOrder[$field] = $direction instanceof SortOrder ? $direction->value : $direction;
        }

        return new UserHistoryChangesResult($this->core->call(
            'main.user.history.fields.list',
            array_filter([
                'filter' => $this->scopedFilter($historyId, $filter),
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
    public function fields(): UserHistoryChangeFieldsResult
    {
        return (new UserHistoryChangeField($this->core, $this->log))->list();
    }

    private function scopedFilter(int $historyId, array|FilterBuilderInterface $filter): array
    {
        if ($filter instanceof FilterBuilderInterface) {
            $filter = $filter->toArray();
        }

        // A root OR group must remain nested under the mandatory identifier's AND.
        if (!array_is_list($filter)) {
            $filter = [$filter];
        }

        return [['historyId', '=', $historyId], ...$filter];
    }
}
