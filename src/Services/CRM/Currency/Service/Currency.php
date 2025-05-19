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
    public Batch $batch;

    /**
     * Currency constructor.
     *
     * @param Batch           $batch
     * @param CoreInterface   $core
     * @param LoggerInterface $log
     */
    public function __construct(Batch $batch, CoreInterface $core, LoggerInterface $log)
    {
        parent::__construct($core, $log);
        $this->batch = $batch;
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
     *   AMOUNT?: double,
     *   SORT?: int,
     *   LANG?: array,
     *   } $fields
     *
     * @return AddedItemResult
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
     * @param string $id
     *
     * @return DeletedItemResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.currency.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/currency/crm-currency-delete.html',
        'Deletes the specified currency'
    )]
    public function delete(int $id): DeletedItemResult
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
     * @return FieldsResult
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
     * @param string $id
     *
     * @return CurrencyResult
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
     * @return CurrenciesResult
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
     * @param string $id
     * @param array{
     *   BASE?: string,
     *   AMOUNT_CNT?: int,
     *   AMOUNT?: double,
     *   SORT?: int,
     *   LANG?: array,
     *   } $fields
     *
     * @return UpdatedItemResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.currency.update',
        'https://apidocs.bitrix24.com/api-reference/crm/currency/crm-currency-update.html',
        'Updates the specified (existing) currency.'
    )]
    public function update(int $id, array $fields): UpdatedItemResult
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