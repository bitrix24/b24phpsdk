<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\Ratio\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\Ratio\Result\RatioResult;
use Bitrix24\SDK\Services\Catalog\Ratio\Result\RatiosResult;

#[ApiServiceMetadata(new Scope(['catalog']))]
class Ratio extends AbstractService
{
    /**
     * Returns the values of the measurement unit ratio fields by identifier.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/ratio/catalog-ratio-get.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.ratio.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/ratio/catalog-ratio-get.html',
        'Returns the values of the measurement unit ratio fields by identifier.'
    )]
    public function get(int $id): RatioResult
    {
        $this->guardPositiveId($id);

        return new RatioResult($this->core->call('catalog.ratio.get', ['id' => $id]));
    }

    /**
     * Returns a list of measurement unit ratios from the catalog matching the given filter.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/ratio/catalog-ratio-list.html
     *
     * @param string[]             $select
     * @param array<string, mixed> $filter
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.ratio.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/ratio/catalog-ratio-list.html',
        'Returns a list of measurement unit ratios from the catalog matching the given filter.'
    )]
    public function list(array $select = [], array $filter = []): RatiosResult
    {
        return new RatiosResult($this->core->call('catalog.ratio.list', [
            'select' => $select,
            'filter' => $filter,
        ]));
    }

    /**
     * Returns the available fields of a measurement unit ratio.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/ratio/catalog-ratio-get-fields.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.ratio.getFields',
        'https://apidocs.bitrix24.com/api-reference/catalog/ratio/catalog-ratio-get-fields.html',
        'Returns the available fields of a measurement unit ratio.'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('catalog.ratio.getFields'));
    }
}
