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

namespace Bitrix24\SDK\Services\Catalog\Enum\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\Enum\Result\RoundTypesResult;
use Bitrix24\SDK\Services\Catalog\Enum\Result\StoreDocumentTypesResult;

#[ApiServiceMetadata(new Scope(['catalog']))]
class CatalogEnum extends AbstractService
{
    /**
     * Returns a list of rounding types available in the catalog.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/enum/catalog-enum-get-round-types.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.enum.getRoundTypes',
        'https://apidocs.bitrix24.com/api-reference/catalog/enum/catalog-enum-get-round-types.html',
        'Returns a list of rounding types available in the catalog.'
    )]
    public function getRoundTypes(): RoundTypesResult
    {
        return new RoundTypesResult($this->core->call('catalog.enum.getRoundTypes'));
    }

    /**
     * Returns the types of store accounting documents available for REST.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/enum/catalog-enum-get-store-document-types.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.enum.getStoreDocumentTypes',
        'https://apidocs.bitrix24.com/api-reference/catalog/enum/catalog-enum-get-store-document-types.html',
        'Returns the types of store accounting documents available for REST.'
    )]
    public function getStoreDocumentTypes(): StoreDocumentTypesResult
    {
        return new StoreDocumentTypesResult($this->core->call('catalog.enum.getStoreDocumentTypes'));
    }
}
