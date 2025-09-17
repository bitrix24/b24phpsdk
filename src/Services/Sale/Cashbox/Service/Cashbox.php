<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\Cashbox\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Sale\Cashbox\Result\CashboxesResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['sale', 'cashbox']))]
class Cashbox extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a new cash register.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-add.html
     *
     * @param array $fields Fields for cash register creation (NAME*, REST_CODE*, EMAIL*, OFD, OFD_SETTINGS, NUMBER_KKM, ACTIVE, SORT, USE_OFFLINE, SETTINGS)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.cashbox.add',
        'https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-add.html',
        'Adds a new cash register.'
    )]
    public function add(array $fields): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call('sale.cashbox.add', $fields)
        );
    }

    /**
     * Updates an existing cash register.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-update.html
     *
     * @param int   $id     Identifier of the cash register
     * @param array $fields Fields to update (NAME, EMAIL, OFD, OFD_SETTINGS, NUMBER_KKM, ACTIVE, SORT, USE_OFFLINE, SETTINGS)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.cashbox.update',
        'https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-update.html',
        'Updates an existing cash register.'
    )]
    public function update(int $id, array $fields): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('sale.cashbox.update', [
                'ID' => $id,
                'FIELDS' => $fields,
            ])
        );
    }

    /**
     * Returns a list of configured cash registers.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-list.html
     *
     * @param array|null $select Fields to select
     * @param array|null $filter Filter conditions
     * @param array|null $order  Sorting parameters
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.cashbox.list',
        'https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-list.html',
        'Returns a list of configured cash registers.'
    )]
    public function list(?array $select = null, ?array $filter = null, ?array $order = null): CashboxesResult
    {
        $params = [];
        if ($select !== null) {
            $params['SELECT'] = $select;
        }
        if ($filter !== null) {
            $params['FILTER'] = $filter;
        }
        if ($order !== null) {
            $params['ORDER'] = $order;
        }

        return new CashboxesResult(
            $this->core->call('sale.cashbox.list', $params)
        );
    }

    /**
     * Deletes a cash register.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-delete.html
     *
     * @param int $id Identifier of the cash register to be deleted
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.cashbox.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-delete.html',
        'Deletes a cash register.'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('sale.cashbox.delete', [
                'ID' => $id,
            ])
        );
    }

    /**
     * Saves the result of printing the receipt.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-check-apply.html
     *
     * @param array $fields Fields for receipt result (UUID*, PRINT_END_TIME, REG_NUMBER_KKT, FISCAL_DOC_ATTR, FISCAL_DOC_NUMBER, FISCAL_RECEIPT_NUMBER, FN_NUMBER, SHIFT_NUMBER)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.cashbox.check.apply',
        'https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-check-apply.html',
        'Saves the result of printing the receipt.'
    )]
    public function checkApply(array $fields): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('sale.cashbox.check.apply', $fields)
        );
    }
}