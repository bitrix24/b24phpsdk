<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Deal\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\Deal\Result\DealRecurringResult;
use Bitrix24\SDK\Services\CRM\Deal\Result\DealRecurringsResult;
use Bitrix24\SDK\Services\CRM\Deal\Result\DealRecurringExposeResult;

#[ApiServiceMetadata(new Scope(['crm']))]
class DealRecurring extends AbstractService
{
    /**
     * Creates a new recurring deal template.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/deals/recurring-deals/crm-deal-recurring-add.html
     *
     * @param array{
     *   DEAL_ID?: int,
     *   BASED_ID?: int,
     *   ACTIVE?: string,
     *   NEXT_EXECUTION?: string,
     *   LAST_EXECUTION?: string,
     *   COUNTER_REPEAT?: int,
     *   START_DATE?: string,
     *   CATEGORY_ID?: string,
     *   IS_LIMIT?: string,
     *   LIMIT_REPEAT?: int,
     *   LIMIT_DATE?: string,
     *   PARAMS?: array{
     *          MODE?: string,
     *          MULTIPLE_TYPE?: string,
     *          MULTIPLE_INTERVAL?: string,
     *          SINGLE_BEFORE_START_DATE_TYPE?: string,
     *          SINGLE_BEFORE_START_DATE_VALUE?: string,
     *          OFFSET_BEGINDATE_TYPE?: string,
     *          OFFSET_BEGINDATE_VALUE?: string,
     *          OFFSET_CLOSEDATE_TYPE?: string,
     *          OFFSET_CLOSEDATE_VALUE?: string,
     *      },
     *   } $fields
     *
     * @return AddedItemResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.deal.recurring.add',
        'https://apidocs.bitrix24.com/api-reference/crm/deals/recurring-deals/crm-deal-recurring-add.html',
        'Creates a new recurring deal template'
    )]
    public function add(array $fields): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call(
                'crm.deal.recurring.add',
                [
                    'fields' => $fields,
                ]
            )
        );
    }

    /**
     * Deletes a recurring deal template.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/deals/recurring-deals/crm-deal-recurring-delete.html
     *
     * @param int $id
     *
     * @return DeletedItemResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.deal.recurring.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/deals/recurring-deals/crm-deal-recurring-delete.html',
        'Deletes a recurring deal template'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call(
                'crm.deal.recurring.delete',
                [
                    'id' => $id,
                ]
            )
        );
    }

    /**
     * Returns a list of fields for the recurring deal template.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/deals/recurring-deals/crm-deal-recurring-fields.html
     *
     * @return FieldsResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.deal.recurring.fields',
        'https://apidocs.bitrix24.com/api-reference/crm/deals/recurring-deals/crm-deal-recurring-fields.html',
        'Returns a list of fields for the recurring deal template'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('crm.deal.recurring.fields'));
    }

    /**
     * Returns the settings of the recurring deal template by Id.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/deals/recurring-deals/crm-deal-recurring-get.html
     *
     * @param int $id
     *
     * @return DealRecurringResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.deal.recurring.get',
        'https://apidocs.bitrix24.com/api-reference/crm/deals/recurring-deals/crm-deal-recurring-get.html',
        'Returns the settings of the recurring deal template by Id'
    )]
    public function get(int $id): DealRecurringResult
    {
        return new DealRecurringResult(
            $this->core->call(
                'crm.deal.recurring.get',
                [
                    'id' => $id,
                ]
            )
        );
    }

    /**
     * Returns a list of recurring deal templates.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/deals/recurring-deals/crm-deal-recurring-list.html
     *
     * @param array $order
     * @param array $filter
     * @param array $select
     * @param int $start
     *
     * @return DealRecurringResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.deal.recurring.list',
        'https://apidocs.bitrix24.com/api-reference/crm/deals/recurring-deals/crm-deal-recurring-list.html',
        'Returns a list of recurring deal templates'
    )]
    public function list(array $order, array $filter, array $select, int $start): DealRecurringsResult
    {
        return new DealRecurringsResult(
            $this->core->call(
                'crm.deal.recurring.list',
                [
                    'order' => $order,
                    'filter' => $filter,
                    'select' => $select,
                    'start' => $start,
                ]
            )
        );
    }

    /**
     * Creates a new deal based on the template.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/deals/recurring-deals/crm-deal-recurring-expose.html
     *
     * @param int $id
     *
     * @return DealRecurringExposeResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.deal.recurring.expose',
        'https://apidocs.bitrix24.com/api-reference/crm/deals/recurring-deals/crm-deal-recurring-expose.html',
        'Creates a new deal based on the template'
    )]
    public function expose(int $id): DealRecurringExposeResult
    {
        return new DealRecurringExposeResult(
            $this->core->call(
                'crm.deal.recurring.expose',
                [
                    'id' => $id,
                ]
            )
        );
    }

    /**
     * Modifies the settings of the recurring deal template.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/deals/recurring-deals/crm-deal-recurring-update.html
     *
     * @param int $id
     * @param array{
     *   ID?: int,
     *   DEAL_ID?: int,
     *   BASED_ID?: int,
     *   ACTIVE?: string,
     *   NEXT_EXECUTION?: string,
     *   LAST_EXECUTION?: string,
     *   COUNTER_REPEAT?: int,
     *   START_DATE?: string,
     *   CATEGORY_ID?: string,
     *   IS_LIMIT?: string,
     *   LIMIT_REPEAT?: int,
     *   LIMIT_DATE?: string,
     *   PARAMS?: array{
     *          MODE?: string,
     *          MULTIPLE_TYPE?: string,
     *          MULTIPLE_INTERVAL?: string,
     *          SINGLE_BEFORE_START_DATE_TYPE?: string,
     *          SINGLE_BEFORE_START_DATE_VALUE?: string,
     *          OFFSET_BEGINDATE_TYPE?: string,
     *          OFFSET_BEGINDATE_VALUE?: string,
     *          OFFSET_CLOSEDATE_TYPE?: string,
     *          OFFSET_CLOSEDATE_VALUE?: string,
     *      },
     *   } $fields
     *
     * @return UpdatedItemResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.deal.recurring.update',
        'https://apidocs.bitrix24.com/api-reference/crm/deals/recurring-deals/crm-deal-recurring-update.html',
        'Modifies the settings of the recurring deal template.'
    )]
    public function update(int $id, array $fields): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'crm.deal.recurring.update',
                [
                    'id' => $id,
                    'fields' => $fields,
                ]
            )
        );
    }
}