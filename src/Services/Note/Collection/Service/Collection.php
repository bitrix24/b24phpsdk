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

namespace Bitrix24\SDK\Services\Note\Collection\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\SelectBuilderInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Filters\FilterBuilderInterface;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Note\Collection\Result\ArchivedCollectionResult;
use Bitrix24\SDK\Services\Note\Collection\Result\CollectionFieldResult;
use Bitrix24\SDK\Services\Note\Collection\Result\CollectionFieldsResult;
use Bitrix24\SDK\Services\Note\Collection\Result\CollectionResult;
use Bitrix24\SDK\Services\Note\Collection\Result\CollectionsResult;
use Bitrix24\SDK\Services\Note\Collection\Result\DeletedCollectionResult;

#[ApiServiceMetadata(new Scope(['note']))]
class Collection extends AbstractService
{
    /**
     * Creates a new knowledge base (collection).
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/note/collection/note-collection-add.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'note.collection.add',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/note/collection/note-collection-add.html',
        'Creates a new knowledge base.',
        ApiVersion::v3
    )]
    public function add(string $name, ?int $position = null): CollectionResult
    {
        return new CollectionResult(
            $this->core->call(
                'note.collection.add',
                [
                    'fields' => array_filter(
                        [
                            'name'     => $name,
                            'position' => $position,
                        ],
                        static fn (mixed $value): bool => $value !== null
                    ),
                ],
                ApiVersion::v3
            )
        );
    }

    /**
     * Archives a knowledge base.
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/note/collection/note-collection-archive.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'note.collection.archive',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/note/collection/note-collection-archive.html',
        'Archives a knowledge base.',
        ApiVersion::v3
    )]
    public function archive(int $id): ArchivedCollectionResult
    {
        return new ArchivedCollectionResult(
            $this->core->call('note.collection.archive', ['id' => $id], ApiVersion::v3)
        );
    }

    /**
     * Deletes a knowledge base by id or by filter.
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/note/collection/note-collection-delete.html
     *
     * @param array|FilterBuilderInterface $filter Filter conditions (REST v3 format)
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'note.collection.delete',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/note/collection/note-collection-delete.html',
        'Deletes a knowledge base.',
        ApiVersion::v3
    )]
    public function delete(?int $id = null, array|FilterBuilderInterface $filter = []): DeletedCollectionResult
    {
        if ($filter instanceof FilterBuilderInterface) {
            $filter = $filter->toArray();
        }

        return new DeletedCollectionResult(
            $this->core->call(
                'note.collection.delete',
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
     * Returns the metadata of a single `note.collection` field.
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/note/collection/note-collection-field-get.html
     *
     * @param array<int,string> $select
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'note.collection.field.get',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/note/collection/note-collection-field-get.html',
        'Returns metadata of a single collection field.',
        ApiVersion::v3
    )]
    public function fieldGet(string $name, array $select = []): CollectionFieldResult
    {
        return new CollectionFieldResult(
            $this->core->call(
                'note.collection.field.get',
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
     * Returns the metadata of all `note.collection` fields.
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/note/collection/note-collection-field-list.html
     *
     * @param array<int,string> $select
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'note.collection.field.list',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/note/collection/note-collection-field-list.html',
        'Returns metadata of all collection fields.',
        ApiVersion::v3
    )]
    public function fieldList(array $select = []): CollectionFieldsResult
    {
        return new CollectionFieldsResult(
            $this->core->call(
                'note.collection.field.list',
                array_filter(['select' => $select], static fn (mixed $value): bool => $value !== []),
                ApiVersion::v3
            )
        );
    }

    /**
     * Returns a single knowledge base by id.
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/note/collection/note-collection-get.html
     *
     * @param array<int,string>|CollectionSelectBuilder $select
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'note.collection.get',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/note/collection/note-collection-get.html',
        'Returns a single knowledge base by id.',
        ApiVersion::v3
    )]
    public function get(int $id, array|CollectionSelectBuilder $select = []): CollectionResult
    {
        if ($select instanceof SelectBuilderInterface) {
            $select = $select->buildSelect();
        }

        return new CollectionResult(
            $this->core->call(
                'note.collection.get',
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
     * Returns a cursor-paginated list of knowledge bases available to the user.
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/note/collection/note-collection-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'note.collection.list',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/note/collection/note-collection-list.html',
        'Returns a list of knowledge bases available to the user.',
        ApiVersion::v3
    )]
    public function list(?CollectionListPagination $pagination = null): CollectionsResult
    {
        return new CollectionsResult(
            $this->core->call(
                'note.collection.list',
                $pagination !== null ? ['pagination' => $pagination->toArray()] : [],
                ApiVersion::v3
            )
        );
    }

    /**
     * Updates a knowledge base by id or by filter.
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/note/collection/note-collection-update.html
     *
     * @param array                        $fields fields to update, e.g. ['name' => 'New name']
     * @param array|FilterBuilderInterface $filter Filter conditions (REST v3 format)
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'note.collection.update',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/note/collection/note-collection-update.html',
        'Updates a knowledge base.',
        ApiVersion::v3
    )]
    public function update(?int $id, array $fields, array|FilterBuilderInterface $filter = []): CollectionResult
    {
        if ($filter instanceof FilterBuilderInterface) {
            $filter = $filter->toArray();
        }

        return new CollectionResult(
            $this->core->call(
                'note.collection.update',
                array_filter(
                    [
                        'id'     => $id,
                        'fields' => $fields,
                        'filter' => $filter,
                    ],
                    static fn (mixed $value): bool => $value !== null && $value !== []
                ),
                ApiVersion::v3
            )
        );
    }
}
