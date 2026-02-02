<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Lists\Lists\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Lists\Lists\Result\ListsResult;
use Bitrix24\SDK\Services\Lists\Lists\Result\IBlockTypeIdResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['lists']))]
class Lists extends AbstractService
{
    /**
     * Lists constructor.
     */
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Creates a universal list.
     *
     * @link https://apidocs.bitrix24.com/api-reference/lists/lists/lists-add.html
     *
     * @param string $iblockTypeId Information block type identifier (lists, lists_socnet, bitrix_processes)
     * @param string $iblockCode   Symbolic code of the information block
     * @param array  $fields       Array of list fields
     * @param array  $messages     Array of labels for list items and sections
     * @param array  $rights       Access permission settings for the list
     * @param int|null $socnetGroupId Group identifier for adding list to a group
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'lists.add',
        'https://apidocs.bitrix24.com/api-reference/lists/lists/lists-add.html',
        'Creates a universal list.'
    )]
    public function add(
        string $iblockTypeId,
        string $iblockCode,
        array $fields,
        array $messages = [],
        array $rights = [],
        ?int $socnetGroupId = null
    ): AddedItemResult {
        $params = [
            'IBLOCK_TYPE_ID' => $iblockTypeId,
            'IBLOCK_CODE' => $iblockCode,
            'FIELDS' => $fields,
        ];

        if ($messages !== []) {
            $params['MESSAGES'] = $messages;
        }

        if ($rights !== []) {
            $params['RIGHTS'] = $rights;
        }

        if ($socnetGroupId !== null) {
            $params['SOCNET_GROUP_ID'] = $socnetGroupId;
        }

        return new AddedItemResult(
            $this->core->call('lists.add', $params)
        );
    }

    /**
     * Updates a universal list.
     *
     * @link https://apidocs.bitrix24.com/api-reference/lists/lists/lists-update.html
     *
     * @param string $iblockTypeId Information block type identifier
     * @param int|null $iblockId   Information block identifier
     * @param string|null $iblockCode Symbolic code of the information block
     * @param array $fields        Array of list fields to update
     * @param array $messages      Array of labels for list items and sections
     * @param array $rights        Access permission settings for the list
     * @param int|null $socnetGroupId Group identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'lists.update',
        'https://apidocs.bitrix24.com/api-reference/lists/lists/lists-update.html',
        'Updates a universal list.'
    )]
    public function update(
        string $iblockTypeId,
        array $fields,
        ?int $iblockId = null,
        ?string $iblockCode = null,
        array $messages = [],
        array $rights = [],
        ?int $socnetGroupId = null
    ): UpdatedItemResult {
        $params = [
            'IBLOCK_TYPE_ID' => $iblockTypeId,
            'FIELDS' => $fields,
        ];

        if ($iblockId !== null) {
            $params['IBLOCK_ID'] = $iblockId;
        }

        if ($iblockCode !== null) {
            $params['IBLOCK_CODE'] = $iblockCode;
        }

        if ($messages !== []) {
            $params['MESSAGES'] = $messages;
        }

        if ($rights !== []) {
            $params['RIGHTS'] = $rights;
        }

        if ($socnetGroupId !== null) {
            $params['SOCNET_GROUP_ID'] = $socnetGroupId;
        }

        return new UpdatedItemResult(
            $this->core->call('lists.update', $params)
        );
    }

    /**
     * Returns data of a universal list or an array of lists.
     *
     * @link https://apidocs.bitrix24.com/api-reference/lists/lists/lists-get.html
     *
     * @param string $iblockTypeId Information block type identifier
     * @param int|null $iblockId   Information block identifier
     * @param string|null $iblockCode Symbolic code of the information block
     * @param int|null $socnetGroupId Group identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'lists.get',
        'https://apidocs.bitrix24.com/api-reference/lists/lists/lists-get.html',
        'Returns data of a universal list or an array of lists.'
    )]
    public function get(
        string $iblockTypeId,
        ?int $iblockId = null,
        ?string $iblockCode = null,
        ?int $socnetGroupId = null
    ): ListsResult {
        $params = [
            'IBLOCK_TYPE_ID' => $iblockTypeId,
        ];

        if ($iblockId !== null) {
            $params['IBLOCK_ID'] = $iblockId;
        }

        if ($iblockCode !== null) {
            $params['IBLOCK_CODE'] = $iblockCode;
        }

        if ($socnetGroupId !== null) {
            $params['SOCNET_GROUP_ID'] = $socnetGroupId;
        }

        return new ListsResult(
            $this->core->call('lists.get', $params)
        );
    }

    /**
     * Deletes a universal list.
     *
     * @link https://apidocs.bitrix24.com/api-reference/lists/lists/lists-delete.html
     *
     * @param string $iblockTypeId Information block type identifier
     * @param int|null $iblockId   Information block identifier
     * @param string|null $iblockCode Symbolic code of the information block
     * @param int|null $socnetGroupId Group identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'lists.delete',
        'https://apidocs.bitrix24.com/api-reference/lists/lists/lists-delete.html',
        'Deletes a universal list.'
    )]
    public function delete(
        string $iblockTypeId,
        ?int $iblockId = null,
        ?string $iblockCode = null,
        ?int $socnetGroupId = null
    ): DeletedItemResult {
        $params = [
            'IBLOCK_TYPE_ID' => $iblockTypeId,
        ];

        if ($iblockId !== null) {
            $params['IBLOCK_ID'] = $iblockId;
        }

        if ($iblockCode !== null) {
            $params['IBLOCK_CODE'] = $iblockCode;
        }

        if ($socnetGroupId !== null) {
            $params['SOCNET_GROUP_ID'] = $socnetGroupId;
        }

        return new DeletedItemResult(
            $this->core->call('lists.delete', $params)
        );
    }

    /**
     * Returns the identifier of the information block type.
     *
     * @link https://apidocs.bitrix24.com/api-reference/lists/lists/lists-get-iblock-type-id.html
     *
     * @param int|null $iblockId Information block identifier
     * @param string|null $iblockCode Symbolic code of the information block
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'lists.get.iblock.type.id',
        'https://apidocs.bitrix24.com/api-reference/lists/lists/lists-get-iblock-type-id.html',
        'Returns the identifier of the information block type.'
    )]
    public function getIBlockTypeId(?int $iblockId = null, ?string $iblockCode = null): IBlockTypeIdResult
    {
        $params = [];

        if ($iblockId !== null) {
            $params['IBLOCK_ID'] = $iblockId;
        }

        if ($iblockCode !== null) {
            $params['IBLOCK_CODE'] = $iblockCode;
        }

        if ($params === []) {
            throw new BaseException('Either IBLOCK_ID or IBLOCK_CODE parameter must be provided');
        }

        return new IBlockTypeIdResult(
            $this->core->call('lists.get.iblock.type.id', $params)
        );
    }
}
