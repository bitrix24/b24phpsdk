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
}
