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

namespace Bitrix24\SDK\Services\Landing\Site\Service;

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
use Bitrix24\SDK\Services\Landing\Site\Result\SitesResult;
use Bitrix24\SDK\Services\Landing\Site\Result\SiteUrlResult;
use Bitrix24\SDK\Services\Landing\Site\Result\SitePublishedResult;
use Bitrix24\SDK\Services\Landing\Site\Result\SiteUnpublishedResult;
use Bitrix24\SDK\Services\Landing\Site\Result\SiteMarkedDeletedResult;
use Bitrix24\SDK\Services\Landing\Site\Result\SiteMarkedUnDeletedResult;
use Bitrix24\SDK\Services\Landing\Site\Result\FoldersResult;
use Bitrix24\SDK\Services\Landing\Site\Result\FolderUpdatedResult;
use Bitrix24\SDK\Services\Landing\Site\Result\FolderPublishedResult;
use Bitrix24\SDK\Services\Landing\Site\Result\FolderUnpublishedResult;
use Bitrix24\SDK\Services\Landing\Site\Result\SiteAdditionalFieldsResult;
use Bitrix24\SDK\Services\Landing\Site\Result\SiteExportResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['landing']))]
class Site extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a site.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-add.html
     *
     * @param array $fields Field values for creating a site
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.site.add',
        'https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-add.html',
        'Method creates a new site.'
    )]
    public function add(array $fields): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call('landing.site.add', ['fields' => $fields])
        );
    }

    /**
     * Retrieves a list of sites.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-get-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter conditions
     * @param array $order Sort order
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.site.getList',
        'https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-get-list.html',
        'Method retrieves a list of sites.'
    )]
    public function getList(array $select = [], array $filter = [], array $order = []): SitesResult
    {
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

        return new SitesResult(
            $this->core->call('landing.site.getList', ['params' => $params])
        );
    }

    /**
     * Updates site parameters.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-update.html
     *
     * @param int $id Site identifier
     * @param array $fields Editable fields of the entity
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.site.update',
        'https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-update.html',
        'Method makes changes to the site.'
    )]
    public function update(int $id, array $fields): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('landing.site.update', [
                'id' => $id,
                'fields' => $fields
            ])
        );
    }

    /**
     * Deletes a site.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-delete.html
     *
     * @param int $id Site identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.site.delete',
        'https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-delete.html',
        'Method deletes a site.'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('landing.site.delete', ['id' => $id])
        );
    }

    /**
     * Returns the full URL of the site(s).
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-get-public-url.html
     *
     * @param int|array $id Site identifier or array of identifiers
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.site.getPublicUrl',
        'https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-get-public-url.html',
        'Method returns the full URL of the site(s).'
    )]
    public function getPublicUrl($id): SiteUrlResult
    {
        return new SiteUrlResult(
            $this->core->call('landing.site.getPublicUrl', ['id' => $id])
        );
    }

    /**
     * Returns the preview image URL of the site.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-get-preview.html
     *
     * @param int $id Site identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.site.getPreview',
        'https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-get-preview.html',
        'Method returns the preview image URL of the site.'
    )]
    public function getPreview(int $id): SiteUrlResult
    {
        return new SiteUrlResult(
            $this->core->call('landing.site.getPreview', ['id' => $id])
        );
    }

    /**
     * Publishes the site and all its pages.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-publication.html
     *
     * @param int $id Site identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.site.publication',
        'https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-publication.html',
        'Method publishes the site and all its pages.'
    )]
    public function publication(int $id): SitePublishedResult
    {
        return new SitePublishedResult(
            $this->core->call('landing.site.publication', ['id' => $id])
        );
    }

    /**
     * Unpublishes the site and all its pages.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-unpublic.html
     *
     * @param int $id Site identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.site.unpublic',
        'https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-unpublic.html',
        'Method unpublishes the site and all its pages.'
    )]
    public function unpublic(int $id): SiteUnpublishedResult
    {
        return new SiteUnpublishedResult(
            $this->core->call('landing.site.unpublic', ['id' => $id])
        );
    }

    /**
     * Marks the site as deleted.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-mark-delete.html
     *
     * @param int $id Site identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.site.markDelete',
        'https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-mark-delete.html',
        'Method marks the site as deleted.'
    )]
    public function markDelete(int $id): SiteMarkedDeletedResult
    {
        return new SiteMarkedDeletedResult(
            $this->core->call('landing.site.markDelete', ['id' => $id])
        );
    }

    /**
     * Restores the site from the trash.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-mark-undelete.html
     *
     * @param int $id Site identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.site.markUnDelete',
        'https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-mark-undelete.html',
        'Method restores the site from the trash.'
    )]
    public function markUnDelete(int $id): SiteMarkedUnDeletedResult
    {
        return new SiteMarkedUnDeletedResult(
            $this->core->call('landing.site.markUnDelete', ['id' => $id])
        );
    }

    /**
     * Returns additional fields of the site.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-getadditionalfields.html
     *
     * @param int $id Site identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.site.getAdditionalFields',
        'https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-getadditionalfields.html',
        'Method returns additional fields of the site.'
    )]
    public function getAdditionalFields(int $id): SiteAdditionalFieldsResult
    {
        return new SiteAdditionalFieldsResult(
            $this->core->call('landing.site.getAdditionalFields', ['id' => $id])
        );
    }

    /**
     * Exports the site to ZIP archive.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-fullexport.html
     *
     * @param int $id Site identifier
     * @param array $params Optional export parameters
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.site.fullExport',
        'https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-fullexport.html',
        'Method exports the site to ZIP archive.'
    )]
    public function fullExport(int $id, array $params = []): SiteExportResult
    {
        $requestParams = ['id' => $id];
        if ($params !== []) {
            $requestParams['params'] = $params;
        }

        return new SiteExportResult(
            $this->core->call('landing.site.fullExport', $requestParams)
        );
    }

    /**
     * Retrieves the site folders.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-get-folders.html
     *
     * @param int $siteId Site identifier
     * @param array $filter Optional filter
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.site.getFolders',
        'https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-get-folders.html',
        'Method retrieves the site folders.'
    )]
    public function getFolders(int $siteId, array $filter = []): FoldersResult
    {
        return new FoldersResult(
            $this->core->call('landing.site.getFolders', [
                'siteId' => $siteId,
                'filter' => $filter
            ])
        );
    }

    /**
     * Adds a folder to the site.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-add-folder.html
     *
     * @param int $siteId Site identifier
     * @param array $fields Folder fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.site.addFolder',
        'https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-add-folder.html',
        'Method adds a folder to the site.'
    )]
    public function addFolder(int $siteId, array $fields): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call('landing.site.addFolder', [
                'siteId' => $siteId,
                'fields' => $fields
            ])
        );
    }

    /**
     * Updates folder parameters.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-update-folder.html
     *
     * @param int $siteId Site identifier
     * @param int $id Folder identifier
     * @param array $fields Folder fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.site.updateFolder',
        'https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-update-folder.html',
        'Method updates folder parameters.'
    )]
    public function updateFolder(int $siteId, int $id, array $fields): FolderUpdatedResult
    {
        return new FolderUpdatedResult(
            $this->core->call('landing.site.updateFolder', [
                'siteId' => $siteId,
                'folderId' => $id,
                'fields' => $fields
            ])
        );
    }

    /**
     * Publishes the site's folder.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-publication-folder.html
     *
     * @param int $id Folder identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.site.publicationFolder',
        'https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-publication-folder.html',
        "Method publishes the site's folder."
    )]
    public function publicationFolder(int $id): FolderPublishedResult
    {
        return new FolderPublishedResult(
            $this->core->call('landing.site.publicationFolder', ['folderId' => $id])
        );
    }

    /**
     * Unpublishes the site's folder.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-unpublic-folder.html
     *
     * @param int $id Folder identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.site.unPublicFolder',
        'https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-unpublic-folder.html',
        "Method unpublishes the site's folder."
    )]
    public function unPublicFolder(int $id): FolderUnpublishedResult
    {
        return new FolderUnpublishedResult(
            $this->core->call('landing.site.unPublicFolder', ['folderId' => $id])
        );
    }

    /**
     * Marks the folder as deleted.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-mark-folder-delete.html
     *
     * @param int $id Folder identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.site.markFolderDelete',
        'https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-mark-folder-delete.html',
        'Method marks the folder as deleted.'
    )]
    public function markFolderDelete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('landing.site.markFolderDelete', ['id' => $id])
        );
    }

    /**
     * Restores the folder from the trash.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-mark-folder-undelete.html
     *
     * @param int $id Folder identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.site.markFolderUnDelete',
        'https://apidocs.bitrix24.com/api-reference/landing/site/landing-site-mark-folder-undelete.html',
        'Method restores the folder from the trash.'
    )]
    public function markFolderUnDelete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('landing.site.markFolderUnDelete', ['id' => $id])
        );
    }
}
