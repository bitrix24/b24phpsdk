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

namespace Bitrix24\SDK\Services\Paysystem\Handler\Service;

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
use Bitrix24\SDK\Services\Paysystem\Handler\Result\HandlersResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['pay_system']))]
class Handler extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a REST handler for the payment system.
     *
     * @link https://apidocs.bitrix24.com/api-reference/pay-system/sale-pay-system-handler-add.html
     *
     * @param string $name     Name of the REST handler
     * @param string $code     Code of the REST handler. Must be unique among all handlers
     * @param array  $settings Handler settings
     * @param int    $sort     Sorting. Default is 100
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.paysystem.handler.add',
        'https://apidocs.bitrix24.com/api-reference/pay-system/sale-pay-system-handler-add.html',
        'Adds a REST handler for the payment system.'
    )]
    public function add(string $name, string $code, array $settings, int $sort = 100): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call('sale.paysystem.handler.add', [
                'NAME' => $name,
                'CODE' => $code,
                'SETTINGS' => $settings,
                'SORT' => $sort,
            ])
        );
    }

    /**
     * Updates a REST handler for the payment system.
     *
     * @link https://apidocs.bitrix24.com/api-reference/pay-system/sale-pay-system-handler-update.html
     *
     * @param int   $id     Identifier of the REST handler
     * @param array $fields Set of values for updating
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.paysystem.handler.update',
        'https://apidocs.bitrix24.com/api-reference/pay-system/sale-pay-system-handler-update.html',
        'Updates a REST handler for the payment system.'
    )]
    public function update(int $id, array $fields): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('sale.paysystem.handler.update', [
                'ID' => $id,
                'FIELDS' => $fields,
            ])
        );
    }

    /**
     * Returns a list of REST handlers for the payment system.
     *
     * @link https://apidocs.bitrix24.com/api-reference/pay-system/sale-pay-system-handler-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.paysystem.handler.list',
        'https://apidocs.bitrix24.com/api-reference/pay-system/sale-pay-system-handler-list.html',
        'Returns a list of REST handlers for the payment system.'
    )]
    public function list(): HandlersResult
    {
        return new HandlersResult(
            $this->core->call('sale.paysystem.handler.list')
        );
    }

    /**
     * Deletes a REST handler for the payment system.
     *
     * @link https://apidocs.bitrix24.com/api-reference/pay-system/sale-pay-system-handler-delete.html
     *
     * @param int $id Identifier of the REST handler
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.paysystem.handler.delete',
        'https://apidocs.bitrix24.com/api-reference/pay-system/sale-pay-system-handler-delete.html',
        'Deletes a REST handler for the payment system.'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('sale.paysystem.handler.delete', [
                'ID' => $id,
            ])
        );
    }
}
