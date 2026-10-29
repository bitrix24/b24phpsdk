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

namespace Bitrix24\SDK\Services\Catalog\V3\CatalogField\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\V3\CatalogField\Result\CatalogFieldResult;
use Bitrix24\SDK\Services\Catalog\V3\CatalogField\Result\CatalogFieldsResult;

#[ApiServiceMetadata(new Scope(['catalog']))]
class CatalogField extends AbstractService
{
    /**
     * @param non-empty-string $name
     * @param string[]         $select
     *
     * @throws BaseException
     * @throws TransportException
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/catalog/catalog-catalog-field-get.html
     */
    #[ApiEndpointMetadata(
        'catalog.catalog.field.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/catalog/catalog-catalog-field-get.html',
        'Returns metadata of a trade catalog field by name',
        ApiVersion::v3
    )]
    public function get(string $name, array $select = []): CatalogFieldResult
    {
        $this->guardNonEmptyString($name, 'field name must not be empty');

        $params = ['name' => $name];
        if ($select !== []) {
            $params['select'] = $select;
        }

        return new CatalogFieldResult($this->core->call('catalog.catalog.field.get', $params, ApiVersion::v3));
    }

    /**
     * @param string[] $select
     *
     * @throws BaseException
     * @throws TransportException
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/catalog/catalog-catalog-field-list.html
     */
    #[ApiEndpointMetadata(
        'catalog.catalog.field.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/catalog/catalog-catalog-field-list.html',
        'Returns metadata of all trade catalog fields',
        ApiVersion::v3
    )]
    public function list(array $select = []): CatalogFieldsResult
    {
        $params = $select !== [] ? ['select' => $select] : [];

        return new CatalogFieldsResult($this->core->call('catalog.catalog.field.list', $params, ApiVersion::v3));
    }
}
