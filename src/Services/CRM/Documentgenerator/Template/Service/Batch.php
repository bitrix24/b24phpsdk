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

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Template\Result\AddedTemplateBatchResult;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Template\Result\UpdatedTemplateBatchResult;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Template\Result\DeletedTemplateBatchResult;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Template\Result\TemplateItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['crm']))]
class Batch
{
    /**
     * Batch constructor
     */
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list method for templates
     *
     * @return Generator<int, TemplateItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.documentgenerator.template.list',
        'https://apidocs.bitrix24.com/api-reference/crm/document-generator/templates/crm-document-generator-template-list.html',
        'Batch list method for templates'
    )]
    public function list(?int $limit = null): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'limit' => $limit,
            ]
        );

        // Use pagination-based traversable to avoid dependency on element ID field name
        $templateListGenerator = $this->batch->getTraversableListWithCount(
            'crm.documentgenerator.template.list',
            [],
            [],
            [],
            $limit
        );
        foreach ($templateListGenerator as $key => $value) {
            yield $key => new TemplateItemResult($value);
        }
    }

    /**
     * Batch adding templates
     *
     * @param array<int, array{
     *     name: string,
     *     file: string,
     *     numeratorId: int,
     *     region: string,
     *     entityTypeId: string|int[],
     *     users?: array,
     *     active?: string,
     *     withStamps?: string,
     *     sort?: int
     *   }> $templates
     *
     * @return Generator<int, AddedTemplateBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.documentgenerator.template.add',
        'https://apidocs.bitrix24.com/api-reference/crm/document-generator/templates/crm-document-generator-template-add.html',
        'Batch adding templates'
    )]
    public function add(array $templates): Generator
    {
        $items = [];
        foreach ($templates as $item) {
            $items[] = [
                'fields' => $item,
            ];
        }

        foreach ($this->batch->addEntityItems('crm.documentgenerator.template.add', $items) as $key => $item) {
            yield $key => new AddedTemplateBatchResult($item);
        }
    }

    /**
     * Batch update templates
     *
     * Update elements in array with structure
     * id => [  // Template id
     *     'fields' => [] // Template fields to update
     * ]
     *
     * @param array<int, array> $entityItems
     *
     * @return Generator<int, UpdatedTemplateBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.documentgenerator.template.update',
        'https://apidocs.bitrix24.com/api-reference/crm/document-generator/templates/crm-document-generator-template-update.html',
        'Update in batch mode a list of templates'
    )]
    public function update(array $entityItems): Generator
    {
        foreach (
            $this->batch->updateEntityItems(
                'crm.documentgenerator.template.update',
                $entityItems
            ) as $key => $item
        ) {
            yield $key => new UpdatedTemplateBatchResult($item);
        }
    }

    /**
     * Batch delete templates
     *
     * @param int[] $templateId
     *
     * @return Generator<int, DeletedTemplateBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.documentgenerator.template.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/document-generator/templates/crm-document-generator-template-delete.html',
        'Batch delete templates'
    )]
    public function delete(array $templateId): Generator
    {
        foreach (
            $this->batch->deleteEntityItems(
                'crm.documentgenerator.template.delete',
                $templateId
            ) as $key => $item
        ) {
            yield $key => new DeletedTemplateBatchResult($item);
        }
    }
}
