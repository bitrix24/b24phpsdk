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

namespace Bitrix24\SDK\Services\Catalog\Extra\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\Extra\Result\ExtraResult;
use Bitrix24\SDK\Services\Catalog\Extra\Result\ExtrasResult;

#[ApiServiceMetadata(new Scope(['catalog']))]
class Extra extends AbstractService
{
    /**
     * Returns information about a markup by its identifier.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/extra/catalog-extra-get.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.extra.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/extra/catalog-extra-get.html',
        'Returns information about a markup by its identifier.'
    )]
    public function get(int $id): ExtraResult
    {
        $this->guardPositiveId($id);

        return new ExtraResult($this->core->call('catalog.extra.get', ['id' => $id]));
    }

    /**
     * Returns a list of markups matching the given filter.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/extra/catalog-extra-list.html
     *
     * @param string[]             $select
     * @param array<string, mixed> $filter
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.extra.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/extra/catalog-extra-list.html',
        'Returns a list of markups matching the given filter.'
    )]
    public function list(array $select = [], array $filter = []): ExtrasResult
    {
        return new ExtrasResult($this->core->call('catalog.extra.list', [
            'select' => $select,
            'filter' => $filter,
        ]));
    }

    /**
     * Returns the fields for markup in the catalog module.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/extra/catalog-extra-get-fields.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.extra.getFields',
        'https://apidocs.bitrix24.com/api-reference/catalog/extra/catalog-extra-get-fields.html',
        'Returns the fields for markup in the catalog module.'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('catalog.extra.getFields'));
    }
}
