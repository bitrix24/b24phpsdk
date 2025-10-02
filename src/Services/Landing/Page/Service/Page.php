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

namespace Bitrix24\SDK\Services\Landing\Page\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Landing\Page\Result\PagesResult;
use Bitrix24\SDK\Services\Landing\Page\Result\PageAdditionalFieldsResult;
use Bitrix24\SDK\Services\Landing\Page\Result\PagePreviewResult;
use Bitrix24\SDK\Services\Landing\Page\Result\PagePublicUrlResult;
use Bitrix24\SDK\Services\Landing\Page\Result\PageIdByUrlResult;
use Bitrix24\SDK\Services\Landing\Page\Result\MarkPageDeletedResult;
use Bitrix24\SDK\Services\Landing\Page\Result\MarkPageUnDeletedResult;
use Bitrix24\SDK\Services\Landing\Page\Result\BlockMovedResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['landing']))]
class Page extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a page.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-add.html
     *
     * @param array $fields Field values for creating a page (TITLE, CODE, SITE_ID required, ADDITIONAL_FIELDS optional)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.landing.add',
        'https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-add.html',
        'Method for adding a page.'
    )]
    public function add(array $fields): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call('landing.landing.add', ['fields' => $fields])
        );
    }

    /**
     * Adds a page by template.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-add-by-template.html
     *
     * @param int $siteId ID of the site where the page needs to be created
     * @param string $code Identifier of the template to be used for creation
     * @param array $fields Optional array of fields for the created page (TITLE, DESCRIPTION)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.landing.addByTemplate',
        'https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-add-by-template.html',
        'Method for adding a page by template.'
    )]
    public function addByTemplate(int $siteId, string $code, array $fields = []): AddedItemResult
    {
        $params = [
            'siteId' => $siteId,
            'code' => $code,
        ];

        if ($fields !== []) {
            $params['fields'] = $fields;
        }

        return new AddedItemResult(
            $this->core->call('landing.landing.addByTemplate', $params)
        );
    }

    /**
     * Copies the specified page.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-copy.html
     *
     * @param int $lid Page identifier
     * @param int|null $toSiteId Optional site identifier to copy to
     * @param int|null $toFolderId Optional folder identifier to copy to
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.landing.copy',
        'https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-copy.html',
        'Method copies the specified page.'
    )]
    public function copy(int $lid, ?int $toSiteId = null, ?int $toFolderId = null): AddedItemResult
    {
        $params = ['lid' => $lid];

        if ($toSiteId !== null) {
            $params['toSiteId'] = $toSiteId;
        }

        if ($toFolderId !== null) {
            $params['toFolderId'] = $toFolderId;
        }

        return new AddedItemResult(
            $this->core->call('landing.landing.copy', $params)
        );
    }

    /**
     * Deletes a page.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-delete.html
     *
     * @param int $lid Entity identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.landing.delete',
        'https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-delete.html',
        'Method for deleting a page.'
    )]
    public function delete(int $lid): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('landing.landing.delete', ['lid' => $lid])
        );
    }

    /**
     * Updates a page.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-update.html
     *
     * @param int $lid Entity identifier
     * @param array $fields Editable fields of the entity
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.landing.update',
        'https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-update.html',
        'Method for modifying a page.'
    )]
    public function update(int $lid, array $fields): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('landing.landing.update', [
                'lid' => $lid,
                'fields' => $fields
            ])
        );
    }

    /**
     * Retrieves a list of pages.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-get-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter conditions
     * @param array $order Sort order
     * @param array $group Group fields
     * @param bool $getPreview Return page previews
     * @param bool $getUrls Return public addresses of pages
     * @param bool $checkArea Return flag IS_AREA indicating whether the page is an included area
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.landing.getList',
        'https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-get-list.html',
        'Method for retrieving a list of pages.'
    )]
    public function getList(
        array $select = [],
        array $filter = [],
        array $order = [],
        array $group = [],
        bool $getPreview = false,
        bool $getUrls = false,
        bool $checkArea = false
    ): PagesResult {
        $params = [];

        if ($select !== []) {
            $params['select'] = $select;
        }

        if ($filter !== []) {
            $params['filter'] = $filter;
        }

        if ($order !== []) {
            $params['order'] = $order;
        }

        if ($group !== []) {
            $params['group'] = $group;
        }

        if ($getPreview) {
            $params['get_preview'] = 1;
        }

        if ($getUrls) {
            $params['get_urls'] = 1;
        }

        if ($checkArea) {
            $params['check_area'] = 1;
        }

        return new PagesResult(
            $this->core->call('landing.landing.getList', ['params' => $params])
        );
    }

    /**
     * Retrieves additional fields of the page.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-get-additional-fields.html
     *
     * @param int $lid Page identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.landing.getadditionalfields',
        'https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-get-additional-fields.html',
        'Method for obtaining additional fields of the page.'
    )]
    public function getAdditionalFields(int $lid): PageAdditionalFieldsResult
    {
        return new PageAdditionalFieldsResult(
            $this->core->call('landing.landing.getadditionalfields', ['lid' => $lid])
        );
    }

    /**
     * Returns the path to the page preview.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-get-preview.html
     *
     * @param int $lid Page identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.landing.getpreview',
        'https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-get-preview.html',
        'Method returns the path to the page preview.'
    )]
    public function getPreview(int $lid): PagePreviewResult
    {
        return new PagePreviewResult(
            $this->core->call('landing.landing.getpreview', ['lid' => $lid])
        );
    }

    /**
     * Returns the web address of the page.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-get-public-url.html
     *
     * @param int $lid Page identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.landing.getpublicurl',
        'https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-get-public-url.html',
        'Method returns the web address of the page.'
    )]
    public function getPublicUrl(int $lid): PagePublicUrlResult
    {
        return new PagePublicUrlResult(
            $this->core->call('landing.landing.getpublicurl', ['lid' => $lid])
        );
    }

    /**
     * Returns the page identifier by the provided relative URL.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-resolve-id-by-public-url.html
     *
     * @param string $landingUrl Relative URL of the page
     * @param int $siteId Site ID
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.landing.resolveIdByPublicUrl',
        'https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-resolve-id-by-public-url.html',
        'Method returns the page identifier by the provided relative URL of the page.'
    )]
    public function resolveIdByPublicUrl(string $landingUrl, int $siteId): PageIdByUrlResult
    {
        return new PageIdByUrlResult(
            $this->core->call('landing.landing.resolveIdByPublicUrl', [
                'landingUrl' => $landingUrl,
                'siteId' => $siteId
            ])
        );
    }

    /**
     * Publishes a page.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-publication.html
     *
     * @param int $lid Page identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.landing.publication',
        'https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-publication.html',
        'Method for publishing the page.'
    )]
    public function publish(int $lid): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('landing.landing.publication', ['lid' => $lid])
        );
    }

    /**
     * Unpublishes a page.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-unpublic.html
     *
     * @param int $lid Page identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.landing.unpublic',
        'https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-unpublic.html',
        'Method for unpublishing the page.'
    )]
    public function unpublish(int $lid): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('landing.landing.unpublic', ['lid' => $lid])
        );
    }

    /**
     * Marks the page as deleted.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-mark-delete.html
     *
     * @param int $lid Page identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.landing.markDelete',
        'https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-mark-delete.html',
        'Method marks the page as deleted.'
    )]
    public function markDeleted(int $lid): MarkPageDeletedResult
    {
        return new MarkPageDeletedResult(
            $this->core->call('landing.landing.markDelete', ['lid' => $lid])
        );
    }

    /**
     * Marks the page as not deleted.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-mark-undelete.html
     *
     * @param int $lid Page identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.landing.markUnDelete',
        'https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-mark-undelete.html',
        'Method marks the page as not deleted.'
    )]
    public function markUnDeleted(int $lid): MarkPageUnDeletedResult
    {
        return new MarkPageUnDeletedResult(
            $this->core->call('landing.landing.markUnDelete', ['lid' => $lid])
        );
    }

    /**
     * Moves the page to another site and/or folder.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-move.html
     *
     * @param int $lid Identifier of the page to be moved
     * @param int $toSiteId Identifier of the site to which the page should be moved
     * @param int|null $toFolderId Optional identifier of the site folder to which the page should be moved
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.landing.move',
        'https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-move.html',
        'Method moves the page to another site and/or folder.'
    )]
    public function move(int $lid, int $toSiteId, ?int $toFolderId = null): UpdatedItemResult
    {
        $params = [
            'lid' => $lid,
            'toSiteId' => $toSiteId,
        ];

        if ($toFolderId !== null) {
            $params['toFolderId'] = $toFolderId;
        }

        return new UpdatedItemResult(
            $this->core->call('landing.landing.move', $params)
        );
    }

    /**
     * Removes related landing entities.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-remove-entities.html
     *
     * @param int $lid Identifier of the landing
     * @param array $data Associative array where key 'blocks' contains blocks to be deleted,
     *                    and key 'images' contains block-image pairs for which images need to be deleted
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.landing.removeEntities',
        'https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-remove-entities.html',
        'Method removes related landing entities.'
    )]
    public function removeEntities(int $lid, array $data): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('landing.landing.removeEntities', [
                'lid' => $lid,
                'data' => $data
            ])
        );
    }

    /**
     * Adds a new block to the page.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/block-methods/landing-landing-add-block.html
     *
     * @param int $lid Page identifier
     * @param array $fields Array of block fields:
     *                     - CODE: Symbolic code of the block (required)
     *                     - AFTER_ID: After which block ID the new block should be added (optional)
     *                     - ACTIVE: Block activity Y/N (optional)
     *                     - CONTENT: Entirely different content of the block (optional)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.landing.addblock',
        'https://apidocs.bitrix24.com/api-reference/landing/page/block-methods/landing-landing-add-block.html',
        'Method for adding a new block to the page.'
    )]
    public function addBlock(int $lid, array $fields): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call('landing.landing.addblock', [
                'lid' => $lid,
                'fields' => $fields
            ])
        );
    }

    /**
     * Copies a block from one page to another.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/block-methods/landing-landing-copy-block.html
     *
     * @param int $lid Identifier of the page where the block should be copied
     * @param int $block Identifier of the block which may be on another page
     * @param array $params Array of parameters, currently supporting AFTER_ID - after which block to insert the new one
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.landing.copyblock',
        'https://apidocs.bitrix24.com/api-reference/landing/page/block-methods/landing-landing-copy-block.html',
        'Method copies a block from one page to another.'
    )]
    public function copyBlock(int $lid, int $block, array $params = []): AddedItemResult
    {
        $callParams = [
            'lid' => $lid,
            'block' => $block,
        ];

        if ($params !== []) {
            $callParams['params'] = $params;
        }

        return new AddedItemResult(
            $this->core->call('landing.landing.copyblock', $callParams)
        );
    }

    /**
     * Completely removes a block from the page.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/block-methods/landing-landing-delete-block.html
     *
     * @param int $lid Page identifier
     * @param int $block Block identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.landing.deleteblock',
        'https://apidocs.bitrix24.com/api-reference/landing/page/block-methods/landing-landing-delete-block.html',
        'Method for deleting a block from the page.'
    )]
    public function deleteBlock(int $lid, int $block): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('landing.landing.deleteblock', [
                'lid' => $lid,
                'block' => $block
            ])
        );
    }

    /**
     * Moves a block down one position on the page.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/block-methods/landing-landing-down-block.html
     *
     * @param int $lid Page identifier
     * @param int $block Block identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.landing.downblock',
        'https://apidocs.bitrix24.com/api-reference/landing/page/block-methods/landing-landing-down-block.html',
        'Method moves a block down one position on the page.'
    )]
    public function moveBlockDown(int $lid, int $block): BlockMovedResult
    {
        return new BlockMovedResult(
            $this->core->call('landing.landing.downblock', [
                'lid' => $lid,
                'block' => $block
            ])
        );
    }

    /**
     * Moves a block up one position on the page.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/block-methods/landing-landing-up-block.html
     *
     * @param int $lid Page identifier
     * @param int $block Block identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.landing.upblock',
        'https://apidocs.bitrix24.com/api-reference/landing/page/block-methods/landing-landing-up-block.html',
        'Method moves a block up one position on the page.'
    )]
    public function moveBlockUp(int $lid, int $block): BlockMovedResult
    {
        return new BlockMovedResult(
            $this->core->call('landing.landing.upblock', [
                'lid' => $lid,
                'block' => $block
            ])
        );
    }

    /**
     * Moves a block from one page to another.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/block-methods/landing-landing-move-block.html
     *
     * @param int $lid Identifier of the page to which the block should be moved
     * @param int $block Identifier of the block which may be on another page
     * @param array $params Array of parameters, currently supporting AFTER_ID - after which block to insert the new one
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.landing.moveblock',
        'https://apidocs.bitrix24.com/api-reference/landing/page/block-methods/landing-landing-move-block.html',
        'Method moves a block from one page to another.'
    )]
    public function moveBlock(int $lid, int $block, array $params = []): BlockMovedResult
    {
        $callParams = [
            'lid' => $lid,
            'block' => $block,
        ];

        if ($params !== []) {
            $callParams['params'] = $params;
        }

        return new BlockMovedResult(
            $this->core->call('landing.landing.moveblock', $callParams)
        );
    }

    /**
     * Hides a block from the page.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/block-methods/landing-landing-hide-block.html
     *
     * @param int $lid Page identifier
     * @param int $block Block identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.landing.hideblock',
        'https://apidocs.bitrix24.com/api-reference/landing/page/block-methods/landing-landing-hide-block.html',
        'Method hides a block from the page.'
    )]
    public function hideBlock(int $lid, int $block): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('landing.landing.hideblock', [
                'lid' => $lid,
                'block' => $block
            ])
        );
    }

    /**
     * Displays a block on the page.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/block-methods/landing-landing-show-block.html
     *
     * @param int $lid Page identifier
     * @param int $block Block identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.landing.showblock',
        'https://apidocs.bitrix24.com/api-reference/landing/page/block-methods/landing-landing-show-block.html',
        'Method displays a block on the page.'
    )]
    public function showBlock(int $lid, int $block): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('landing.landing.showblock', [
                'lid' => $lid,
                'block' => $block
            ])
        );
    }

    /**
     * Marks a block as deleted but does not physically remove it.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/block-methods/landing-landing-mark-deleted-block.html
     *
     * @param int $lid Page identifier
     * @param int $block Block identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.landing.markdeletedblock',
        'https://apidocs.bitrix24.com/api-reference/landing/page/block-methods/landing-landing-mark-deleted-block.html',
        'Method marks a block as deleted but does not physically remove it.'
    )]
    public function markBlockDeleted(int $lid, int $block): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('landing.landing.markdeletedblock', [
                'lid' => $lid,
                'block' => $block
            ])
        );
    }

    /**
     * Restores a block that has been marked as deleted.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/block-methods/landing-landing-mark-undeleted-block.html
     *
     * @param int $lid Page identifier
     * @param int $block Block identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.landing.markundeletedblock',
        'https://apidocs.bitrix24.com/api-reference/landing/page/block-methods/landing-landing-mark-undeleted-block.html',
        'Method restores a block that has been marked as deleted.'
    )]
    public function markBlockUnDeleted(int $lid, int $block): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('landing.landing.markundeletedblock', [
                'lid' => $lid,
                'block' => $block
            ])
        );
    }

    /**
     * Saves an existing block on the page to "My Blocks".
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/block-methods/landing-landing-favorite-block.html
     *
     * @param int $lid Page identifier
     * @param int $block Block identifier
     * @param array $meta Object containing information to save the block:
     *                    - name: Name of the block
     *                    - section: Array of categories to save the block to
     *                    - preview: Image of the block
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.landing.favoriteBlock',
        'https://apidocs.bitrix24.com/api-reference/landing/page/block-methods/landing-landing-favorite-block.html',
        'Method saves an existing block on the page to My Blocks.'
    )]
    public function addBlockToFavorites(int $lid, int $block, array $meta): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call('landing.landing.favoriteBlock', [
                'lid' => $lid,
                'block' => $block,
                'meta' => $meta
            ])
        );
    }

    /**
     * Removes a block that was saved in "My Blocks".
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/block-methods/landing-landing-unfavorite-block.html
     *
     * @param int $blockId Block identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.landing.unFavoriteBlock',
        'https://apidocs.bitrix24.com/api-reference/landing/page/block-methods/landing-landing-unfavorite-block.html',
        'Method removes a block that was saved in My Blocks.'
    )]
    public function removeBlockFromFavorites(int $blockId): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('landing.landing.unFavoriteBlock', [
                'blockId' => $blockId
            ])
        );
    }
}
