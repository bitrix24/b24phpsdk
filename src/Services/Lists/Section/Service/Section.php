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

namespace Bitrix24\SDK\Services\Lists\Section\Service;

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
use Bitrix24\SDK\Services\Lists\Section\Result\SectionsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['lists']))]
class Section extends AbstractService
{
    /**
     * Section constructor.
     */
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Creates a list section.
     *
     * @link https://apidocs.bitrix24.com/api-reference/lists/sections/lists-section-add.html
     *
     * @param string $iblockTypeId Information block type identifier (lists, bitrix_processes, lists_socnet)
     * @param int|string $iblockId Information block identifier or code
     * @param string $sectionCode Symbolic code of the section
     * @param array $fields Array of section fields
     * @param int|null $parentSectionId Parent section identifier
     * @param string|null $iblockCode Information block code (alternative to iblockId)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'lists.section.add',
        'https://apidocs.bitrix24.com/api-reference/lists/sections/lists-section-add.html',
        'Creates a list section.'
    )]
    public function add(
        string $iblockTypeId,
        int|string $iblockId,
        string $sectionCode,
        array $fields,
        ?int $parentSectionId = null,
        ?string $iblockCode = null
    ): AddedItemResult {
        $params = [
            'IBLOCK_TYPE_ID' => $iblockTypeId,
            'SECTION_CODE' => $sectionCode,
            'FIELDS' => $fields,
        ];

        if (is_int($iblockId)) {
            $params['IBLOCK_ID'] = $iblockId;
        } else {
            $params['IBLOCK_CODE'] = $iblockId;
        }

        if ($iblockCode !== null) {
            $params['IBLOCK_CODE'] = $iblockCode;
        }

        if ($parentSectionId !== null) {
            $params['IBLOCK_SECTION_ID'] = $parentSectionId;
        }

        return new AddedItemResult(
            $this->core->call('lists.section.add', $params)
        );
    }

    /**
     * Updates a list section.
     *
     * @link https://apidocs.bitrix24.com/api-reference/lists/sections/lists-section-update.html
     *
     * @param string $iblockTypeId Information block type identifier
     * @param int|string $iblockId Information block identifier or code
     * @param int|string $sectionId Section identifier or code
     * @param array $fields Array of section fields to update
     * @param string|null $iblockCode Information block code
     * @param string|null $sectionCode Section code
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'lists.section.update',
        'https://apidocs.bitrix24.com/api-reference/lists/sections/lists-section-update.html',
        'Updates a list section.'
    )]
    public function update(
        string $iblockTypeId,
        int|string $iblockId,
        int|string $sectionId,
        array $fields,
        ?string $iblockCode = null,
        ?string $sectionCode = null
    ): UpdatedItemResult {
        $params = [
            'IBLOCK_TYPE_ID' => $iblockTypeId,
            'FIELDS' => $fields,
        ];

        if (is_int($iblockId)) {
            $params['IBLOCK_ID'] = $iblockId;
        } else {
            $params['IBLOCK_CODE'] = $iblockId;
        }

        if ($iblockCode !== null) {
            $params['IBLOCK_CODE'] = $iblockCode;
        }

        if (is_int($sectionId)) {
            $params['SECTION_ID'] = $sectionId;
        } else {
            $params['SECTION_CODE'] = $sectionId;
        }

        if ($sectionCode !== null) {
            $params['SECTION_CODE'] = $sectionCode;
        }

        return new UpdatedItemResult(
            $this->core->call('lists.section.update', $params)
        );
    }

    /**
     * Returns a section or a list of sections.
     *
     * @link https://apidocs.bitrix24.com/api-reference/lists/sections/lists-section-get.html
     *
     * @param string $iblockTypeId Information block type identifier
     * @param int|string $iblockId Information block identifier or code
     * @param array $filter Filter for sections
     * @param array $select Fields to select
     * @param string|null $iblockCode Information block code
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'lists.section.get',
        'https://apidocs.bitrix24.com/api-reference/lists/sections/lists-section-get.html',
        'Returns a section or a list of sections.'
    )]
    public function get(
        string $iblockTypeId,
        int|string $iblockId,
        array $filter = [],
        array $select = [],
        ?string $iblockCode = null
    ): SectionsResult {
        $params = [
            'IBLOCK_TYPE_ID' => $iblockTypeId,
        ];

        if (is_int($iblockId)) {
            $params['IBLOCK_ID'] = $iblockId;
        } else {
            $params['IBLOCK_CODE'] = $iblockId;
        }

        if ($iblockCode !== null) {
            $params['IBLOCK_CODE'] = $iblockCode;
        }

        if ($filter !== []) {
            $params['FILTER'] = $filter;
        }

        if ($select !== []) {
            $params['SELECT'] = $select;
        }

        return new SectionsResult(
            $this->core->call('lists.section.get', $params)
        );
    }

    /**
     * Deletes a list section.
     *
     * @link https://apidocs.bitrix24.com/api-reference/lists/sections/lists-section-delete.html
     *
     * @param string $iblockTypeId Information block type identifier
     * @param int|string $iblockId Information block identifier or code
     * @param int|string $sectionId Section identifier or code
     * @param string|null $iblockCode Information block code
     * @param string|null $sectionCode Section code
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'lists.section.delete',
        'https://apidocs.bitrix24.com/api-reference/lists/sections/lists-section-delete.html',
        'Deletes a list section.'
    )]
    public function delete(
        string $iblockTypeId,
        int|string $iblockId,
        int|string $sectionId,
        ?string $iblockCode = null,
        ?string $sectionCode = null
    ): DeletedItemResult {
        $params = [
            'IBLOCK_TYPE_ID' => $iblockTypeId,
        ];

        if (is_int($iblockId)) {
            $params['IBLOCK_ID'] = $iblockId;
        } else {
            $params['IBLOCK_CODE'] = $iblockId;
        }

        if ($iblockCode !== null) {
            $params['IBLOCK_CODE'] = $iblockCode;
        }

        if (is_int($sectionId)) {
            $params['SECTION_ID'] = $sectionId;
        } else {
            $params['SECTION_CODE'] = $sectionId;
        }

        if ($sectionCode !== null) {
            $params['SECTION_CODE'] = $sectionCode;
        }

        return new DeletedItemResult(
            $this->core->call('lists.section.delete', $params)
        );
    }
}
