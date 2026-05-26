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

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Documentgenerator\Template\Result\AddedTemplateBatchResult;
use Bitrix24\SDK\Services\Documentgenerator\Template\Result\DeletedTemplateBatchResult;
use Bitrix24\SDK\Services\Documentgenerator\Template\Result\TemplateItemResult;
use Bitrix24\SDK\Services\Documentgenerator\Template\Result\UpdatedTemplateBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['documentgenerator']))]
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
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/templates/document-generator-template-list.html
     *
     * @return Generator<int, TemplateItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'documentgenerator.template.list',
        'https://apidocs.bitrix24.com/api-reference/document-generator/templates/document-generator-template-list.html',
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
            'documentgenerator.template.list',
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
     *     fileId?: int,
     *     file?: string,
     *     numeratorId: int,
     *     region: string,
     *     code?: string,
     *     users?: array,
     *     active?: string,
     *     withStamps?: string,
     *     sort?: int
     *   }> $templates
     *
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/templates/document-generator-template-add.html
     *
     * @return Generator<int, AddedTemplateBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'documentgenerator.template.add',
        'https://apidocs.bitrix24.com/api-reference/document-generator/templates/document-generator-template-add.html',
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

        foreach ($this->batch->addEntityItems('documentgenerator.template.add', $items) as $key => $item) {
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
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/templates/document-generator-template-update.html
     *
     * @return Generator<int, UpdatedTemplateBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'documentgenerator.template.update',
        'https://apidocs.bitrix24.com/api-reference/document-generator/templates/document-generator-template-update.html',
        'Update in batch mode a list of templates'
    )]
    public function update(array $entityItems): Generator
    {
        foreach (
            $this->batch->updateEntityItems(
                'documentgenerator.template.update',
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
     * @link https://apidocs.bitrix24.com/api-reference/document-generator/templates/document-generator-template-delete.html
     *
     * @return Generator<int, DeletedTemplateBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'documentgenerator.template.delete',
        'https://apidocs.bitrix24.com/api-reference/document-generator/templates/document-generator-template-delete.html',
        'Batch delete templates'
    )]
    public function delete(array $templateId): Generator
    {
        foreach (
            $this->batch->deleteEntityItems(
                'documentgenerator.template.delete',
                $templateId
            ) as $key => $item
        ) {
            yield $key => new DeletedTemplateBatchResult($item);
        }
    }
}

