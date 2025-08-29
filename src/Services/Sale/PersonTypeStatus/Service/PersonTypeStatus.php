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

namespace Bitrix24\SDK\Services\Sale\PersonTypeStatus\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Sale\PersonTypeStatus\Result\PersonTypeStatusesResult;
use Bitrix24\SDK\Services\Sale\PersonTypeStatus\Result\PersonTypeStatusFieldsResult;
use Bitrix24\SDK\Services\Sale\PersonTypeStatus\Result\PersonTypeStatusAddResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Psr\Log\LoggerInterface;

class PersonTypeStatus extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Add business value for person domain.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/business-value-person-domain/sale-business-value-person-domain-add.html
     *
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    #[ApiEndpointMetadata(
        'sale.businessValuePersonDomain.add',
        'https://apidocs.bitrix24.com/api-reference/sale/business-value-person-domain/sale-business-value-person-domain-add.html',
        'Add business value for person domain'
    )]
    public function add(int $personTypeId, string $domain): PersonTypeStatusAddResult
    {
        return new PersonTypeStatusAddResult(
            $this->core->call(
                'sale.businessValuePersonDomain.add',
                [
                    'fields' => [
                        'personTypeId' => $personTypeId,
                        'domain' => $domain
                    ]
                ]
            )
        );
    }

    /**
     * Retrieves list of business values for person domain.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/business-value-person-domain/sale-business-value-person-domain-list.html
     *
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    #[ApiEndpointMetadata(
        'sale.businessValuePersonDomain.list',
        'https://apidocs.bitrix24.com/api-reference/sale/business-value-person-domain/sale-business-value-person-domain-list.html',
        'List business values for person domain'
    )]
    public function list(array $filter = [], array $order = [], array $select = [], ?int $start = null): PersonTypeStatusesResult
    {
        $params = ['filter' => $filter, 'order' => $order, 'select' => $select];
        if ($start !== null) {
            $params['start'] = $start;
        }

        return new PersonTypeStatusesResult(
            $this->core->call(
                'sale.businessValuePersonDomain.list',
                $params
            )
        );
    }

    /**
     * Delete business values matching filter.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/business-value-person-domain/sale-business-value-person-domain-delete-by-filter.html
     *
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    #[ApiEndpointMetadata(
        'sale.businessValuePersonDomain.deleteByFilter',
        'https://apidocs.bitrix24.com/api-reference/sale/business-value-person-domain/sale-business-value-person-domain-delete-by-filter.html',
        'Delete business values by filter'
    )]
    public function delete(int $personTypeId, string $domain): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call(
                'sale.businessValuePersonDomain.deleteByFilter',
                ['fields' =>
                    [
                        'personTypeId' => $personTypeId,
                        'domain' => $domain
                    ]
                ]
            )
        );
    }

    /**
     * Get fields description for business value person domain.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/business-value-person-domain/sale-business-value-person-domain-getfields.html
     *
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    #[ApiEndpointMetadata(
        'sale.businessValuePersonDomain.getFields',
        'https://apidocs.bitrix24.com/api-reference/sale/business-value-person-domain/sale-business-value-person-domain-getfields.html',
        'Get fields for business value person domain'
    )]
    public function getFields(): PersonTypeStatusFieldsResult
    {
        return new PersonTypeStatusFieldsResult(
            $this->core->call(
                'sale.businessValuePersonDomain.getFields',
                []
            )
        );
    }
}
