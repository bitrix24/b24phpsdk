<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Category\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\Category\Result\CategoriesResult;
use Bitrix24\SDK\Services\CRM\Category\Result\CategoryResult;

#[ApiServiceMetadata(new Scope(['crm']))]
class Category extends AbstractService
{
    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata('crm.category.fields', 'https://apidocs.bitrix24.com/api-reference/crm/universal/category/crm-category-fields.html', 'Returns category field descriptions for a CRM entity type.')]
    public function fields(int $entityTypeId): FieldsResult
    {
        return new FieldsResult($this->core->call('crm.category.fields', ['entityTypeId' => $entityTypeId]));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata('crm.category.get', 'https://apidocs.bitrix24.com/api-reference/crm/universal/category/crm-category-get.html', 'Returns a category for a CRM entity type.')]
    public function get(int $entityTypeId, int $id): CategoryResult
    {
        return new CategoryResult($this->core->call('crm.category.get', ['entityTypeId' => $entityTypeId, 'id' => $id]));
    }

    /**
     * Returns one page. Read the response pagination before requesting the next offset.
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata('crm.category.list', 'https://apidocs.bitrix24.com/api-reference/crm/universal/category/crm-category-list.html', 'Returns categories for a CRM entity type.')]
    public function list(int $entityTypeId, int $start = 0): CategoriesResult
    {
        return new CategoriesResult($this->core->call('crm.category.list', ['entityTypeId' => $entityTypeId, 'start' => $start]));
    }
}
