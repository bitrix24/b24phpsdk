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

namespace Bitrix24\SDK\Services\Documentgenerator\Template\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Documentgenerator\Template\Result\AddedTemplateResult;
use Bitrix24\SDK\Services\Documentgenerator\Template\Result\DeletedTemplateResult;
use Bitrix24\SDK\Services\Documentgenerator\Template\Result\TemplateFieldsResult;
use Bitrix24\SDK\Services\Documentgenerator\Template\Result\TemplateResult;
use Bitrix24\SDK\Services\Documentgenerator\Template\Result\TemplatesResult;
use Bitrix24\SDK\Services\Documentgenerator\Template\Result\UpdatedTemplateResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['documentgenerator']))]
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
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/templates/document-generator-template-add.html
     *
     * @param array{
     *   name: string,
     *   numeratorId: int,
     *   region: string,
     *   fileId?: int,
     *   file?: string,
     *   code?: string,
     *   users?: array,
     *   active?: string,
     *   withStamps?: string,
     *   sort?: int
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'documentgenerator.template.add',
        'https://apidocs.bitrix24.com/api-reference/document-generator/templates/document-generator-template-add.html',
        'Adds a new template'
    )]
    public function add(array $fields): AddedTemplateResult
    {
        return new AddedTemplateResult(
            $this->core->call(
                'documentgenerator.template.add',
                [
                    'fields' => $fields,
                ]
            )
        );
    }

    /**
     * Updates an existing template
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/templates/document-generator-template-update.html
     *
     * @param int $id Template identifier
     * @param array{
     *   name?: string,
     *   file?: string,
     *   numeratorId?: int,
     *   region?: string,
     *   code?: string,
     *   users?: array,
     *   providers?: array,
     *   active?: string,
     *   withStamps?: string,
     *   sort?: int
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'documentgenerator.template.update',
        'https://apidocs.bitrix24.com/api-reference/document-generator/templates/document-generator-template-update.html',
        'Updates an existing template'
    )]
    public function update(int $id, array $fields): UpdatedTemplateResult
    {
        return new UpdatedTemplateResult(
            $this->core->call(
                'documentgenerator.template.update',
                [
                    'id' => $id,
                    'fields' => $fields,
                ]
            )
        );
    }

    /**
     * Returns information about the template by its identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/templates/document-generator-template-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'documentgenerator.template.get',
        'https://apidocs.bitrix24.com/api-reference/document-generator/templates/document-generator-template-get.html',
        'Returns information about the template by its identifier'
    )]
    public function get(int $id): TemplateResult
    {
        return new TemplateResult(
            $this->core->call(
                'documentgenerator.template.get',
                ['id' => $id]
            )
        );
    }

    /**
     * Returns a list of templates
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/templates/document-generator-template-list.html
     *
     * @param array $filter Filter parameters
     * @param array $order  Order parameters
     * @param array $select Fields to select
     * @param int   $start  Offset for pagination
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'documentgenerator.template.list',
        'https://apidocs.bitrix24.com/api-reference/document-generator/templates/document-generator-template-list.html',
        'Returns a list of templates'
    )]
    public function list(array $filter = [], array $order = [], array $select = [], int $start = 0): TemplatesResult
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

        if ($select !== []) {
            $params['select'] = $select;
        }

        return new TemplatesResult(
            $this->core->call(
                'documentgenerator.template.list',
                $params
            )
        );
    }

    /**
     * Deletes a template
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/templates/document-generator-template-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'documentgenerator.template.delete',
        'https://apidocs.bitrix24.com/api-reference/document-generator/templates/document-generator-template-delete.html',
        'Deletes a template'
    )]
    public function delete(int $id): DeletedTemplateResult
    {
        return new DeletedTemplateResult(
            $this->core->call(
                'documentgenerator.template.delete',
                ['id' => $id]
            )
        );
    }

    /**
     * Returns the description of template fields
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/templates/document-generator-template-get-fields.html
     *
     * @param int $id Template identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'documentgenerator.template.getfields',
        'https://apidocs.bitrix24.com/api-reference/document-generator/templates/document-generator-template-get-fields.html',
        'Returns the description of template fields'
    )]
    public function getFields(int $id): TemplateFieldsResult
    {
        return new TemplateFieldsResult(
            $this->core->call(
                'documentgenerator.template.getfields',
                ['id' => $id]
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

