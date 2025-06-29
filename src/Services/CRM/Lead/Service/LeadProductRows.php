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

namespace Bitrix24\SDK\Services\CRM\Lead\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\Lead\Result\LeadProductRowItemsResult;
use Money\Currency;

#[ApiServiceMetadata(new Scope(['crm']))]
class LeadProductRows extends AbstractService
{
    /**
     * Returns products inside the specified lead.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/leads/crm-lead-get.html
     *
     * @param Currency|null $currency
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.lead.productrows.get',
        'https://apidocs.bitrix24.com/api-reference/crm/leads/crm-lead-get.html',
        'Returns products inside the specified lead.'
    )]
    public function get(int $leadId, Currency $currency = null): LeadProductRowItemsResult
    {
        if (!$currency instanceof \Money\Currency) {
            $res = $this->core->call('batch', [
                'halt' => 0,
                'cmd' => [
                    'lead' => sprintf('crm.lead.get?ID=%s', $leadId),
                    'rows' => sprintf('crm.lead.productrows.get?ID=%s', $leadId)
                ],
            ]);
            $data = $res->getResponseData()->getResult();
            $currency = new Currency($data['result']['lead']['CURRENCY_ID']);
            return new LeadProductRowItemsResult($res, $currency);
        }

        return new LeadProductRowItemsResult(
            $this->core->call(
                'crm.lead.productrows.get',
                [
                    'id' => $leadId,
                ]
            ),
            $currency
        );
    }


    /**
     * Creates or updates product entries inside the specified lead.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/leads/crm-lead-productrows-set.html
     *
     * @param array<int, array{
     *   ID?: int,
     *   OWNER_ID?: int,
     *   OWNER_TYPE?: string,
     *   PRODUCT_ID?: int,
     *   PRODUCT_NAME?: string,
     *   PRICE?: string,
     *   PRICE_EXCLUSIVE?: string,
     *   PRICE_NETTO?: string,
     *   PRICE_BRUTTO?: string,
     *   QUANTITY?: string,
     *   DISCOUNT_TYPE_ID?: int,
     *   DISCOUNT_RATE?: string,
     *   DISCOUNT_SUM?: string,
     *   TAX_RATE?: string,
     *   TAX_INCLUDED?: string,
     *   CUSTOMIZED?: string,
     *   MEASURE_CODE?: int,
     *   MEASURE_NAME?: string,
     *   SORT?: int
     *   }> $productRows
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.lead.productrows.set',
        'https://apidocs.bitrix24.com/api-reference/crm/leads/crm-lead-productrows-set.html',
        'Creates or updates product entries inside the specified lead.'
    )]
    public function set(int $leadId, array $productRows): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'crm.lead.productrows.set',
                [
                    'id' => $leadId,
                    'rows' => $productRows,
                ]
            )
        );
    }
}
