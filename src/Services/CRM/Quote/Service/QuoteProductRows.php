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

namespace Bitrix24\SDK\Services\CRM\Quote\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\Quote\Result\QuoteProductRowItemsResult;
use Money\Currency;

#[ApiServiceMetadata(new Scope(['crm']))]
class QuoteProductRows extends AbstractService
{
    /**
     * Returns products inside the specified quote.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/quote/crm-quote-product-rows-get.html
     *
     * @param Currency|null $currency
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.quote.productrows.get',
        'https://apidocs.bitrix24.com/api-reference/crm/quote/crm-quote-product-rows-get.html',
        'Returns products inside the specified quote.'
    )]
    public function get(int $quoteId, ?Currency $currency = null): QuoteProductRowItemsResult
    {
        if (!$currency instanceof \Money\Currency) {
            $res = $this->core->call('batch', [
                'halt' => 0,
                'cmd' => [
                    'quote' => sprintf('crm.quote.get?ID=%s', $quoteId),
                    'rows' => sprintf('crm.quote.productrows.get?ID=%s', $quoteId)
                ],
            ]);
            $data = $res->getResponseData()->getResult();
            $currency = new Currency($data['result']['quote']['CURRENCY_ID']);
            return new QuoteProductRowItemsResult($res, $currency);
        }

        return new QuoteProductRowItemsResult(
            $this->core->call(
                'crm.quote.productrows.get',
                [
                    'id' => $quoteId,
                ]
            ),
            $currency
        );
    }


    /**
     * Creates or updates product entries inside the specified quote.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/quote/crm-quote-product-rows-set.html
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
        'crm.quote.productrows.set',
        'https://apidocs.bitrix24.com/api-reference/crm/quote/crm-quote-product-rows-set.html',
        'Creates or updates product entries inside the specified quote.'
    )]
    public function set(int $quoteId, array $productRows): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'crm.quote.productrows.set',
                [
                    'id' => $quoteId,
                    'rows' => $productRows,
                ]
            )
        );
    }
}
