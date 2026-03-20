<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <titarx@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Documentgenerator\Template\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Template\Result\AddedTemplateResult;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Template\Result\DeletedTemplateResult;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Template\Result\TemplateResult;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Template\Result\TemplatesResult;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Template\Result\UpdatedTemplateResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['crm']))]
class Template extends AbstractService
{
    /**
     * Template constructor
     */
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a new template
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/document-generator/templates/crm-document-generator-template-add.html
     *
     * @param array{
     *   name: string,
     *   file: string,
     *   numeratorId?: int,
     *   region?: string,
     *   users?: array,
     *   providers?: array,
     *   active?: string,
     *   withStamps?: int,
     *   sort?: int
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.documentgenerator.template.add',
        'https://apidocs.bitrix24.com/api-reference/crm/document-generator/templates/crm-document-generator-template-add.html',
        'Adds a new template'
    )]
    public function add(array $fields): AddedTemplateResult
    {
        return new AddedTemplateResult(
            $this->core->call(
                'crm.documentgenerator.template.add',
                [
                    'fields' => $fields
                ]
            )
        );
    }

    /**
     * Deletes a template
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/document-generator/templates/crm-document-generator-template-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.documentgenerator.template.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/document-generator/templates/crm-document-generator-template-delete.html',
        'Deletes a template'
    )]
    public function delete(int $id): DeletedTemplateResult
    {
        $params = [
            'id' => $id,
        ];

        return new DeletedTemplateResult(
            $this->core->call(
                'crm.documentgenerator.template.delete',
                $params
            )
        );
    }

    /**
     * Returns information about the template by its identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/document-generator/templates/crm-document-generator-template-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.documentgenerator.template.get',
        'https://apidocs.bitrix24.com/api-reference/crm/document-generator/templates/crm-document-generator-template-get.html',
        'Returns information about the template by its identifier'
    )]
    public function get(int $id): TemplateResult
    {
        return new TemplateResult($this->core->call('crm.documentgenerator.template.get', ['id' => $id]));
    }

    /**
     * Returns a list of templates
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/document-generator/templates/crm-document-generator-template-list.html
     *
     * @param array $filter Filter parameters
     * @param array $order Order parameters
     * @param int $start Offset for pagination
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.documentgenerator.template.list',
        'https://apidocs.bitrix24.com/api-reference/crm/document-generator/templates/crm-document-generator-template-list.html',
        'Returns a list of templates'
    )]
    public function list(array $filter = [], array $order = [], int $start = 0): TemplatesResult
    {
        $params = [
            'start' => $start,
        ];

        if ($filter !== []) {
            $params['filter'] = $filter;
        }

        if ($order !== []) {
            $params['order'] = $order;
        }

        return new TemplatesResult(
            $this->core->call(
                'crm.documentgenerator.template.list',
                $params
            )
        );
    }

    /**
     * Updates an existing template
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/document-generator/templates/crm-document-generator-template-update.html
     *
     * @param int $id Template identifier
     * @param array{
     *   name?: string,
     *   file?: string,
     *   numeratorId?: int,
     *   region?: string,
     *   users?: array,
     *   providers?: array,
     *   active?: string,
     *   withStamps?: int,
     *   sort?: int
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.documentgenerator.template.update',
        'https://apidocs.bitrix24.com/api-reference/crm/document-generator/templates/crm-document-generator-template-update.html',
        'Updates an existing template'
    )]
    public function update(int $id, array $fields): UpdatedTemplateResult
    {
        $params = [
            'id' => $id,
            'fields' => $fields
        ];

        return new UpdatedTemplateResult(
            $this->core->call(
                'crm.documentgenerator.template.update',
                $params
            )
        );
    }

    /**
     * Returns the description of template fields
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/document-generator/templates/crm-document-generator-template-get-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.documentgenerator.template.getfields',
        'https://apidocs.bitrix24.com/api-reference/crm/document-generator/templates/crm-document-generator-template-get-fields.html',
        'Returns the description of template fields'
    )]
    public function getFields(): FieldsResult
    {
        return new FieldsResult(
            $this->core->call(
                'crm.documentgenerator.template.getfields',
                []
            )
        );
    }

    /**
     * Count templates
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function count(): int
    {
        return $this->list()->getCoreResponse()->getResponseData()->getPagination()->getTotal();
    }
}

