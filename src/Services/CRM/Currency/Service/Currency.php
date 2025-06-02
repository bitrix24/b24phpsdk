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

namespace Bitrix24\SDK\Services\CRM\Currency\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\Currency\Result\CurrencyResult;
use Bitrix24\SDK\Services\CRM\Currency\Result\CurrenciesResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['crm']))]
class Currency extends AbstractService
{
    /**
     * Currency constructor.
     */
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * add new currency
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/currency/crm-currency-add.html
     *
     * @param array{
     *   CURRENCY?: string,
     *   BASE?: string,
     *   AMOUNT_CNT?: int,
     *   AMOUNT?: float,
     *   SORT?: int,
     *   LANG?: array,
     *   } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.currency.add',
        'https://apidocs.bitrix24.com/api-reference/crm/currency/crm-currency-add.html',
        'Method adds new currency'
    )]
    public function add(array $fields): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call(
                'crm.currency.add',
                [
                    'fields' => $fields,
                ]
            )
        );
    }

    /**
     * Deletes the specified currency
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/currency/crm-currency-delete.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.currency.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/currency/crm-currency-delete.html',
        'Deletes the specified currency'
    )]
    public function delete(string $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call(
                'crm.currency.delete',
                [
                    'id' => $id,
                ]
            )
        );
    }

    /**
     * Returns the description of the currency fields.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/currency/crm-currency-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.currency.fields',
        'https://apidocs.bitrix24.com/api-reference/crm/currency/crm-currency-fields.html',
        'Returns the description of the currency fields.'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('crm.currency.fields'));
    }

    /**
     * Returns a currency by the currency ID.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/currency/crm-currency-get.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.currency.get',
        'https://apidocs.bitrix24.com/api-reference/crm/currency/crm-currency-get.html',
        'Returns a currency by the currency ID.'
    )]
    public function get(string $id): CurrencyResult
    {
        return new CurrencyResult($this->core->call('crm.currency.get', ['id' => $id]));
    }

    /**
     * Get list of currency items.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/currency/crm-currency-list.html
     *
     * @param array   $order     - order of currency items
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.currency.list',
        'https://apidocs.bitrix24.com/api-reference/crm/currency/crm-currency-list.html',
        'Get list of lead items.'
    )]
    public function list(array $order): CurrenciesResult
    {
        return new CurrenciesResult(
            $this->core->call(
                'crm.currency.list',
                [
                    'order'  => $order,
                ]
            )
        );
    }

    /**
     * Updates the specified (existing) currency.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/currency/crm-currency-update.html
     *
     * @param array{
     *   BASE?: string,
     *   AMOUNT_CNT?: int,
     *   AMOUNT?: float,
     *   SORT?: int,
     *   LANG?: array,
     *   } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.currency.update',
        'https://apidocs.bitrix24.com/api-reference/crm/currency/crm-currency-update.html',
        'Updates the specified (existing) currency.'
    )]
    public function update(string $id, array $fields): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'crm.currency.update',
                [
                    'id'     => $id,
                    'fields' => $fields,
                ]
            )
        );
    }

}
