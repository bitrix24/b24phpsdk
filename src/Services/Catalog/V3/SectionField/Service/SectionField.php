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

namespace Bitrix24\SDK\Services\Catalog\V3\SectionField\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\V3\SectionField\Result\SectionFieldResult;
use Bitrix24\SDK\Services\Catalog\V3\SectionField\Result\SectionFieldsResult;

#[ApiServiceMetadata(new Scope(['catalog']))]
class SectionField extends AbstractService
{
    /**
     * @param non-empty-string $name
     * @param string[]         $select
     *
     * @throws BaseException
     * @throws TransportException
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-field-get.html
     */
    #[ApiEndpointMetadata(
        'catalog.section.field.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-field-get.html',
        'Returns metadata of a product section field by name',
        ApiVersion::v3
    )]
    public function get(string $name, array $select = []): SectionFieldResult
    {
        $this->guardNonEmptyString($name, 'field name must not be empty');

        $params = ['name' => $name];
        if ($select !== []) {
            $params['select'] = $select;
        }

        return new SectionFieldResult($this->core->call('catalog.section.field.get', $params, ApiVersion::v3));
    }

    /**
     * @param string[] $select
     *
     * @throws BaseException
     * @throws TransportException
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-field-list.html
     */
    #[ApiEndpointMetadata(
        'catalog.section.field.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/section/catalog-section-field-list.html',
        'Returns metadata of all product section fields',
        ApiVersion::v3
    )]
    public function list(array $select = []): SectionFieldsResult
    {
        $params = $select !== [] ? ['select' => $select] : [];

        return new SectionFieldsResult($this->core->call('catalog.section.field.list', $params, ApiVersion::v3));
    }
}
