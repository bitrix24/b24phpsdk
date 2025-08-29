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

namespace Bitrix24\SDK\Services\Sale\TradePlatform\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Sale\TradePlatform\Result\TradePlatformsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['sale']))]
class TradePlatform extends AbstractService
{
    /**
     * Get a list of order sources
     *
     * @param array|null $select Array containing the list of fields to select
     * @param array|null $filter List of fields for filtering
     * @param array|null $order Sorting parameters
     * @param int|null $start Pagination parameter
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.tradePlatform.list',
        'https://apidocs.bitrix24.com/api-reference/sale/trade-platform/sale-trade-platform-list.html',
        'Method returns a list of order sources'
    )]
    public function list(
        ?array $select = null,
        ?array $filter = null,
        ?array $order = null,
        ?int $start = null
    ): TradePlatformsResult {
        $params = [];

        if ($select !== null) {
            $params['select'] = $select;
        }

        if ($filter !== null) {
            $params['filter'] = $filter;
        }

        if ($order !== null) {
            $params['order'] = $order;
        }

        if ($start !== null) {
            $params['start'] = $start;
        }

        return new TradePlatformsResult($this->core->call('sale.tradePlatform.list', $params));
    }

    /**
     * Get available fields for order sources
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.tradePlatform.getFields',
        'https://apidocs.bitrix24.com/api-reference/sale/trade-platform/sale-trade-platform-get-fields.html',
        'Method returns the available fields of order sources'
    )]
    public function getFields(): FieldsResult
    {
        return new FieldsResult($this->core->call('sale.tradePlatform.getFields', []));
    }
}
