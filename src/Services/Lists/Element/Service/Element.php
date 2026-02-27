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

namespace Bitrix24\SDK\Services\Lists\Element\Service;

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
use Bitrix24\SDK\Services\Lists\Element\Result\ElementsResult;
use Bitrix24\SDK\Services\Lists\Element\Result\FileUrlsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['lists']))]
class Element extends AbstractService
{
    /**
     * Element constructor.
     */
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Creates a list element.
     *
     * @link https://apidocs.bitrix24.com/api-reference/lists/elements/lists-element-add.html
     *
     * @param string $iblockTypeId Information block type identifier (lists, lists_socnet, bitrix_processes)
     * @param int|string $iblock Information block ID or symbolic code
     * @param string $elementCode Symbolic code of the element
     * @param array $fields Array of element fields
     * @param int|null $sectionId Section identifier (default: 0 - root level)
     * @param string|null $listElementUrl Template address with replacements
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'lists.element.add',
        'https://apidocs.bitrix24.com/api-reference/lists/elements/lists-element-add.html',
        'Creates a list element.'
    )]
    public function add(
        string $iblockTypeId,
        int|string $iblock,
        string $elementCode,
        array $fields,
        ?int $sectionId = null,
        ?string $listElementUrl = null
    ): AddedItemResult {
        $params = [
            'IBLOCK_TYPE_ID' => $iblockTypeId,
            'ELEMENT_CODE' => $elementCode,
            'FIELDS' => $fields,
        ];

        if (is_int($iblock)) {
            $params['IBLOCK_ID'] = $iblock;
        } else {
            $params['IBLOCK_CODE'] = $iblock;
        }

        if ($sectionId !== null) {
            $params['IBLOCK_SECTION_ID'] = $sectionId;
        }

        if ($listElementUrl !== null) {
            $params['LIST_ELEMENT_URL'] = $listElementUrl;
        }

        return new AddedItemResult(
            $this->core->call('lists.element.add', $params)
        );
    }

    /**
     * Updates a list element.
     *
     * @link https://apidocs.bitrix24.com/api-reference/lists/elements/lists-element-update.html
     *
     * @param string $iblockTypeId Information block type identifier
     * @param int|string $iblock Information block ID or symbolic code
     * @param int|string $element Element ID or symbolic code
     * @param array $fields Array of element fields to update
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'lists.element.update',
        'https://apidocs.bitrix24.com/api-reference/lists/elements/lists-element-update.html',
        'Updates a list element.'
    )]
    public function update(
        string $iblockTypeId,
        int|string $iblock,
        int|string $element,
        array $fields
    ): UpdatedItemResult {
        $params = [
            'IBLOCK_TYPE_ID' => $iblockTypeId,
            'FIELDS' => $fields,
        ];

        if (is_int($iblock)) {
            $params['IBLOCK_ID'] = $iblock;
        } else {
            $params['IBLOCK_CODE'] = $iblock;
        }

        if (is_int($element)) {
            $params['ELEMENT_ID'] = $element;
        } else {
            $params['ELEMENT_CODE'] = $element;
        }

        return new UpdatedItemResult(
            $this->core->call('lists.element.update', $params)
        );
    }

    /**
     * Returns data of an element or a list of elements.
     *
     * @link https://apidocs.bitrix24.com/api-reference/lists/elements/lists-element-get.html
     *
     * @param string $iblockTypeId Information block type identifier
     * @param int|string $iblock Information block ID or symbolic code
     * @param int|string|null $element Element ID or symbolic code (null for list of elements)
     * @param array $select Fields to select
     * @param array $filter Filter conditions
     * @param array $order Sorting configuration
     * @param int $start Pagination offset (50 records per page)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'lists.element.get',
        'https://apidocs.bitrix24.com/api-reference/lists/elements/lists-element-get.html',
        'Returns data of an element or a list of elements.'
    )]
    public function get(
        string $iblockTypeId,
        int|string $iblock,
        int|string|null $element = null,
        array $select = [],
        array $filter = [],
        array $order = [],
        int $start = 0
    ): ElementsResult {
        $params = [
            'IBLOCK_TYPE_ID' => $iblockTypeId,
        ];

        if (is_int($iblock)) {
            $params['IBLOCK_ID'] = $iblock;
        } else {
            $params['IBLOCK_CODE'] = $iblock;
        }

        if ($element !== null) {
            if (is_int($element)) {
                $params['ELEMENT_ID'] = $element;
            } else {
                $params['ELEMENT_CODE'] = $element;
            }
        }

        if ($select !== []) {
            $params['SELECT'] = $select;
        }

        if ($filter !== []) {
            $params['FILTER'] = $filter;
        }

        if ($order !== []) {
            $params['ELEMENT_ORDER'] = $order;
        }

        if ($start > 0) {
            $params['start'] = $start;
        }

        return new ElementsResult(
            $this->core->call('lists.element.get', $params)
        );
    }

    /**
     * Deletes a list element.
     *
     * @link https://apidocs.bitrix24.com/api-reference/lists/elements/lists-element-delete.html
     *
     * @param string $iblockTypeId Information block type identifier
     * @param int|string $iblock Information block ID or symbolic code
     * @param int|string $element Element ID or symbolic code
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'lists.element.delete',
        'https://apidocs.bitrix24.com/api-reference/lists/elements/lists-element-delete.html',
        'Deletes a list element.'
    )]
    public function delete(
        string $iblockTypeId,
        int|string $iblock,
        int|string $element
    ): DeletedItemResult {
        $params = [
            'IBLOCK_TYPE_ID' => $iblockTypeId,
        ];

        if (is_int($iblock)) {
            $params['IBLOCK_ID'] = $iblock;
        } else {
            $params['IBLOCK_CODE'] = $iblock;
        }

        if (is_int($element)) {
            $params['ELEMENT_ID'] = $element;
        } else {
            $params['ELEMENT_CODE'] = $element;
        }

        return new DeletedItemResult(
            $this->core->call('lists.element.delete', $params)
        );
    }

    /**
     * Returns file download paths for File or File (Drive) properties.
     *
     * @link https://apidocs.bitrix24.com/api-reference/lists/elements/lists-element-get-file-url.html
     *
     * @param string $iblockTypeId Information block type identifier
     * @param int|string $iblock Information block ID or symbolic code
     * @param int|string $element Element ID or symbolic code
     * @param int $fieldId File property identifier (without PROPERTY_ prefix)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'lists.element.get.file.url',
        'https://apidocs.bitrix24.com/api-reference/lists/elements/lists-element-get-file-url.html',
        'Returns file download paths for File or File (Drive) properties.'
    )]
    public function getFileUrl(
        string $iblockTypeId,
        int|string $iblock,
        int|string $element,
        int $fieldId
    ): FileUrlsResult {
        $params = [
            'IBLOCK_TYPE_ID' => $iblockTypeId,
            'FIELD_ID' => $fieldId,
        ];

        if (is_int($iblock)) {
            $params['IBLOCK_ID'] = $iblock;
        } else {
            $params['IBLOCK_CODE'] = $iblock;
        }

        if (is_int($element)) {
            $params['ELEMENT_ID'] = $element;
        } else {
            $params['ELEMENT_CODE'] = $element;
        }

        return new FileUrlsResult(
            $this->core->call('lists.element.get.file.url', $params)
        );
    }
}
