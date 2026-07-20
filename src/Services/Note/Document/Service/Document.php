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

namespace Bitrix24\SDK\Services\Note\Document\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\SelectBuilderInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Filters\FilterBuilderInterface;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Note\Document\Result\ArchivedDocumentResult;
use Bitrix24\SDK\Services\Note\Document\Result\DeletedDocumentResult;
use Bitrix24\SDK\Services\Note\Document\Result\DocumentFieldResult;
use Bitrix24\SDK\Services\Note\Document\Result\DocumentFieldsResult;
use Bitrix24\SDK\Services\Note\Document\Result\DocumentResult;
use Bitrix24\SDK\Services\Note\Document\Result\DocumentSearchFieldResult;
use Bitrix24\SDK\Services\Note\Document\Result\DocumentSearchFieldsResult;
use Bitrix24\SDK\Services\Note\Document\Result\DocumentSearchResult;
use Bitrix24\SDK\Services\Note\Document\Result\DocumentTreeFieldResult;
use Bitrix24\SDK\Services\Note\Document\Result\DocumentTreeFieldsResult;
use Bitrix24\SDK\Services\Note\Document\Result\DocumentTreeResult;

#[ApiServiceMetadata(new Scope(['note']))]
class Document extends AbstractService
{
    /**
     * Creates a new document inside a knowledge base.
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/note/document/note-document-add.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'note.document.add',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/note/document/note-document-add.html',
        'Creates a new document inside a knowledge base.',
        ApiVersion::v3
    )]
    public function add(int $collectionId, string $title, ?int $parentId = null, ?string $markdown = null): DocumentResult
    {
        return new DocumentResult(
            $this->core->call(
                'note.document.add',
                [
                    'fields' => array_filter(
                        [
                            'collectionId' => $collectionId,
                            'title'        => $title,
                            'parentId'     => $parentId,
                            'markdown'     => $markdown,
                        ],
                        static fn (mixed $value): bool => $value !== null
                    ),
                ],
                ApiVersion::v3
            )
        );
    }

    /**
     * Archives a document.
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/note/document/note-document-archive.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'note.document.archive',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/note/document/note-document-archive.html',
        'Archives a document.',
        ApiVersion::v3
    )]
    public function archive(int $id): ArchivedDocumentResult
    {
        return new ArchivedDocumentResult(
            $this->core->call('note.document.archive', ['id' => $id], ApiVersion::v3)
        );
    }

    /**
     * Deletes a document by id or by filter.
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/note/document/note-document-delete.html
     *
     * @param array|FilterBuilderInterface $filter Filter conditions (REST v3 format)
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'note.document.delete',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/note/document/note-document-delete.html',
        'Deletes a document.',
        ApiVersion::v3
    )]
    public function delete(?int $id = null, array|FilterBuilderInterface $filter = []): DeletedDocumentResult
    {
        if ($filter instanceof FilterBuilderInterface) {
            $filter = $filter->toArray();
        }

        return new DeletedDocumentResult(
            $this->core->call(
                'note.document.delete',
                array_filter(
                    [
                        'id'     => $id,
                        'filter' => $filter,
                    ],
                    static fn (mixed $value): bool => $value !== null && $value !== []
                ),
                ApiVersion::v3
            )
        );
    }

    /**
     * Returns the metadata of a single `note.document` field.
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/note/document/note-document-field-get.html
     *
     * @param array<int,string> $select
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'note.document.field.get',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/note/document/note-document-field-get.html',
        'Returns metadata of a single document field.',
        ApiVersion::v3
    )]
    public function fieldGet(string $name, array $select = []): DocumentFieldResult
    {
        return new DocumentFieldResult(
            $this->core->call(
                'note.document.field.get',
                array_filter(
                    [
                        'name'   => $name,
                        'select' => $select,
                    ],
                    static fn (mixed $value): bool => $value !== []
                ),
                ApiVersion::v3
            )
        );
    }

    /**
     * Returns the metadata of all `note.document` fields.
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/note/document/note-document-field-list.html
     *
     * @param array<int,string> $select
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'note.document.field.list',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/note/document/note-document-field-list.html',
        'Returns metadata of all document fields.',
        ApiVersion::v3
    )]
    public function fieldList(array $select = []): DocumentFieldsResult
    {
        return new DocumentFieldsResult(
            $this->core->call(
                'note.document.field.list',
                array_filter(['select' => $select], static fn (mixed $value): bool => $value !== []),
                ApiVersion::v3
            )
        );
    }

    /**
     * Returns a single document by id.
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/note/document/note-document-get.html
     *
     * @param array<int,string>|DocumentSelectBuilder $select
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'note.document.get',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/note/document/note-document-get.html',
        'Returns a single document by id.',
        ApiVersion::v3
    )]
    public function get(int $id, array|DocumentSelectBuilder $select = []): DocumentResult
    {
        if ($select instanceof SelectBuilderInterface) {
            $select = $select->buildSelect();
        }

        return new DocumentResult(
            $this->core->call(
                'note.document.get',
                array_filter(
                    [
                        'id'     => $id,
                        'select' => $select,
                    ],
                    static fn (mixed $value): bool => $value !== []
                ),
                ApiVersion::v3
            )
        );
    }

    /**
     * Updates a document by id or by filter.
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/note/document/note-document-update.html
     *
     * @param array                        $fields    fields to update, e.g. ['title' => 'New title']
     * @param array|FilterBuilderInterface $filter    Filter conditions (REST v3 format)
     * @param bool|null                    $overwrite when true, replaces markdown instead of merging it
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'note.document.update',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/note/document/note-document-update.html',
        'Updates a document.',
        ApiVersion::v3
    )]
    public function update(
        ?int $id,
        array $fields,
        array|FilterBuilderInterface $filter = [],
        ?bool $overwrite = null
    ): DocumentResult {
        if ($filter instanceof FilterBuilderInterface) {
            $filter = $filter->toArray();
        }

        return new DocumentResult(
            $this->core->call(
                'note.document.update',
                array_filter(
                    [
                        'id'        => $id,
                        'fields'    => $fields,
                        'filter'    => $filter,
                        'overwrite' => $overwrite,
                    ],
                    static fn (mixed $value): bool => $value !== null && $value !== []
                ),
                ApiVersion::v3
            )
        );
    }

    /**
     * Returns the document tree of a knowledge base.
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/note/document/note-document-tree-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'note.document.tree.list',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/note/document/note-document-tree-list.html',
        'Returns the document tree of a knowledge base.',
        ApiVersion::v3
    )]
    public function treeList(int $collectionId): DocumentTreeResult
    {
        return new DocumentTreeResult(
            $this->core->call('note.document.tree.list', ['collectionId' => $collectionId], ApiVersion::v3)
        );
    }

    /**
     * Returns the metadata of a single `note.document.tree` field.
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/note/document/note-document-tree-field-get.html
     *
     * @param array<int,string> $select
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'note.document.tree.field.get',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/note/document/note-document-tree-field-get.html',
        'Returns metadata of a single document tree field.',
        ApiVersion::v3
    )]
    public function treeFieldGet(string $name, array $select = []): DocumentTreeFieldResult
    {
        return new DocumentTreeFieldResult(
            $this->core->call(
                'note.document.tree.field.get',
                array_filter(
                    [
                        'name'   => $name,
                        'select' => $select,
                    ],
                    static fn (mixed $value): bool => $value !== []
                ),
                ApiVersion::v3
            )
        );
    }

    /**
     * Returns the metadata of all `note.document.tree` fields.
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/note/document/note-document-tree-field-list.html
     *
     * @param array<int,string> $select
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'note.document.tree.field.list',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/note/document/note-document-tree-field-list.html',
        'Returns metadata of all document tree fields.',
        ApiVersion::v3
    )]
    public function treeFieldList(array $select = []): DocumentTreeFieldsResult
    {
        return new DocumentTreeFieldsResult(
            $this->core->call(
                'note.document.tree.field.list',
                array_filter(['select' => $select], static fn (mixed $value): bool => $value !== []),
                ApiVersion::v3
            )
        );
    }

    /**
     * Performs a full-text search across documents.
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/note/document/note-document-search-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'note.document.search.list',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/note/document/note-document-search-list.html',
        'Performs a full-text search across documents.',
        ApiVersion::v3
    )]
    public function searchList(string $query, int $limit = 0): DocumentSearchResult
    {
        return new DocumentSearchResult(
            $this->core->call(
                'note.document.search.list',
                array_filter(
                    [
                        'query'      => $query,
                        'pagination' => array_filter(['limit' => $limit], static fn (int $v): bool => $v !== 0),
                    ],
                    static fn (mixed $value): bool => $value !== []
                ),
                ApiVersion::v3
            )
        );
    }

    /**
     * Returns the metadata of a single `note.document.search` field.
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/note/document/note-document-search-field-get.html
     *
     * @param array<int,string> $select
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'note.document.search.field.get',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/note/document/note-document-search-field-get.html',
        'Returns metadata of a single document search field.',
        ApiVersion::v3
    )]
    public function searchFieldGet(string $name, array $select = []): DocumentSearchFieldResult
    {
        return new DocumentSearchFieldResult(
            $this->core->call(
                'note.document.search.field.get',
                array_filter(
                    [
                        'name'   => $name,
                        'select' => $select,
                    ],
                    static fn (mixed $value): bool => $value !== []
                ),
                ApiVersion::v3
            )
        );
    }

    /**
     * Returns the metadata of all `note.document.search` fields.
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/note/document/note-document-search-field-list.html
     *
     * @param array<int,string> $select
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'note.document.search.field.list',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/note/document/note-document-search-field-list.html',
        'Returns metadata of all document search fields.',
        ApiVersion::v3
    )]
    public function searchFieldList(array $select = []): DocumentSearchFieldsResult
    {
        return new DocumentSearchFieldsResult(
            $this->core->call(
                'note.document.search.field.list',
                array_filter(['select' => $select], static fn (mixed $value): bool => $value !== []),
                ApiVersion::v3
            )
        );
    }
}
