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

namespace Bitrix24\SDK\Services\Telephony\FollowUp\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\SelectBuilderInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Filters\FilterBuilderInterface;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Telephony\FollowUp\Result\FollowUpResult;
use Bitrix24\SDK\Services\Telephony\FollowUp\Result\FollowUpsResult;

#[ApiServiceMetadata(new Scope(['call']))]
class FollowUp extends AbstractService
{
    /**
     * Returns a cursor-paginated list of Follow-ups for a period / filter.
     *
     * @link https://apidocs.bitrix24.com/api-reference/telephony/follow-up/call-followup-list.html
     *
     * @param array|FilterBuilderInterface        $filter Selection conditions, e.g. a `startDate` range
     * @param array|FollowUpSelectBuilder         $select Fields and nested paths to return in each item
     * @param array<string,string>                $order  Sort order, e.g. ['startDate' => 'desc']
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'call.followup.list',
        'https://apidocs.bitrix24.com/api-reference/telephony/follow-up/call-followup-list.html',
        'Returns a list of Follow-ups for the given period.',
        ApiVersion::v3
    )]
    public function list(
        array|FilterBuilderInterface $filter,
        array|FollowUpSelectBuilder $select = [],
        array $order = ['startDate' => 'desc'],
        ?FollowUpListPagination $pagination = null,
        ?FollowUpMentionFormat $mentionFormat = null,
    ): FollowUpsResult {
        if ($filter instanceof FilterBuilderInterface) {
            $filter = $filter->toArray();
        }

        if ($select instanceof SelectBuilderInterface) {
            $select = $select->buildSelect();
        }

        return new FollowUpsResult(
            $this->core->call(
                'call.followup.list',
                array_filter(
                    [
                        'filter'        => $filter,
                        'select'        => $select,
                        'order'         => $order,
                        'pagination'    => $pagination?->toArray(),
                        'mentionFormat' => $mentionFormat?->value,
                    ],
                    static fn (mixed $value): bool => $value !== null && $value !== []
                ),
                ApiVersion::v3
            )
        );
    }

    /**
     * Returns a single Follow-up by call identifier.
     *
     * @link https://apidocs.bitrix24.com/api-reference/telephony/follow-up/call-followup-get.html
     *
     * @param array|FollowUpSelectBuilder $select Fields and nested paths to return; empty select returns all fields
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'call.followup.get',
        'https://apidocs.bitrix24.com/api-reference/telephony/follow-up/call-followup-get.html',
        'Returns a single Follow-up by call identifier.',
        ApiVersion::v3
    )]
    public function get(
        int $callId,
        array|FollowUpSelectBuilder $select = [],
        ?FollowUpMentionFormat $mentionFormat = null,
    ): FollowUpResult {
        if ($select instanceof SelectBuilderInterface) {
            $select = $select->buildSelect();
        }

        return new FollowUpResult(
            $this->core->call(
                'call.followup.get',
                array_filter(
                    [
                        'callId'        => $callId,
                        'select'        => $select,
                        'mentionFormat' => $mentionFormat?->value,
                    ],
                    static fn (mixed $value): bool => $value !== null && $value !== []
                ),
                ApiVersion::v3
            )
        );
    }
}
