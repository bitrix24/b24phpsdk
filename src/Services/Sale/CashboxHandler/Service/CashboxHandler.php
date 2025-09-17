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

namespace Bitrix24\SDK\Services\Sale\CashboxHandler\Service;

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
use Bitrix24\SDK\Services\Sale\CashboxHandler\Result\CashboxHandlersResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['sale', 'cashbox']))]
class CashboxHandler extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a REST cashbox handler.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-handler-add.html
     *
     * @param string $code             Unique code of the REST handler
     * @param string $name             Name of the REST handler
     * @param array  $settings         Handler settings including PRINT_URL, CHECK_URL, CONFIG
     * @param int    $sort             Sorting order (default: 100)
     * @param string $supportsFFD105   Support for fiscal data format 1.05 (Y/N, default: N)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.cashbox.handler.add',
        'https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-handler-add.html',
        'Adds a REST cashbox handler.'
    )]
    public function add(
        string $code,
        string $name,
        array $settings,
        int $sort = 100,
        string $supportsFFD105 = 'N'
    ): AddedItemResult {
        return new AddedItemResult(
            $this->core->call('sale.cashbox.handler.add', [
                'CODE' => $code,
                'NAME' => $name,
                'SORT' => $sort,
                'SUPPORTS_FFD105' => $supportsFFD105,
                'SETTINGS' => $settings,
            ])
        );
    }

    /**
     * Updates the data of the REST cashbox handler.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-handler-update.html
     *
     * @param int   $id     Identifier of the handler being updated
     * @param array $fields Values of the fields to be updated (NAME, SORT, SETTINGS)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.cashbox.handler.update',
        'https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-handler-update.html',
        'Updates the data of the REST cashbox handler.'
    )]
    public function update(int $id, array $fields): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('sale.cashbox.handler.update', [
                'ID' => $id,
                'FIELDS' => $fields,
            ])
        );
    }

    /**
     * Returns a list of available REST cashbox handlers.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-handler-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.cashbox.handler.list',
        'https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-handler-list.html',
        'Returns a list of available REST cashbox handlers.'
    )]
    public function list(): CashboxHandlersResult
    {
        return new CashboxHandlersResult(
            $this->core->call('sale.cashbox.handler.list')
        );
    }

    /**
     * Deletes the REST cashbox handler.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-handler-delete.html
     *
     * @param int $id Identifier of the cashbox handler to be deleted
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.cashbox.handler.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-handler-delete.html',
        'Deletes the REST cashbox handler.'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('sale.cashbox.handler.delete', [
                'ID' => $id,
            ])
        );
    }
}
