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

namespace Bitrix24\SDK\Services\Landing\SysPage\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Landing\SysPage\Result\SysPageResult;
use Bitrix24\SDK\Services\Landing\SysPage\Result\SysPageListResult;
use Bitrix24\SDK\Services\Landing\SysPage\Result\SysPageUrlResult;
use Bitrix24\SDK\Services\Landing\SysPage\SysPageType;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['landing']))]
class SysPage extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Sets a special page for the site.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/special-pages/landing-syspage-set.html
     *
     * @param int $siteId Site ID
     * @param SysPageType|string $type Type of special page
     * @param int|null $pageId Page ID that will be considered of this type within the site. If not provided, the page type will be removed.
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.syspage.set',
        'https://apidocs.bitrix24.com/api-reference/landing/page/special-pages/landing-syspage-set.html',
        'Sets a special page for the site.'
    )]
    public function set(int $siteId, SysPageType|string $type, ?int $pageId = null): SysPageResult
    {
        $typeValue = $type instanceof SysPageType ? $type->value : $type;

        $params = [
            'id' => $siteId,
            'type' => $typeValue,
        ];

        if ($pageId !== null) {
            $params['lid'] = $pageId;
        }

        return new SysPageResult(
            $this->core->call('landing.syspage.set', $params)
        );
    }

    /**
     * Retrieves the list of special pages.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/special-pages/landing-syspage-get.html
     *
     * @param int $siteId Site ID
     * @param bool|null $active If true, only active site pages will be returned (default is all)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.syspage.get',
        'https://apidocs.bitrix24.com/api-reference/landing/page/special-pages/landing-syspage-get.html',
        'Returns a list of site pages that are set as special.'
    )]
    public function get(int $siteId, ?bool $active = null): SysPageListResult
    {
        $params = [
            'id' => $siteId,
        ];

        if ($active !== null) {
            $params['active'] = $active;
        }

        return new SysPageListResult(
            $this->core->call('landing.syspage.get', $params)
        );
    }

    /**
     * Retrieves the address of the special page on the site.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/special-pages/landing-syspage-get-special-page.html
     *
     * @param int $siteId Site ID
     * @param SysPageType|string $type Type of special page
     * @param array|null $additional Optional array of additional parameters to be added to the URL
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.syspage.getSpecialPage',
        'https://apidocs.bitrix24.com/api-reference/landing/page/special-pages/landing-syspage-get-special-page.html',
        'Returns the address of a special page on the site.'
    )]
    public function getSpecialPage(int $siteId, SysPageType|string $type, ?array $additional = null): SysPageUrlResult
    {
        $typeValue = $type instanceof SysPageType ? $type->value : $type;

        $params = [
            'siteId' => $siteId,
            'type' => $typeValue,
        ];

        if ($additional !== null) {
            $params['additional'] = $additional;
        }

        return new SysPageUrlResult(
            $this->core->call('landing.syspage.getSpecialPage', $params)
        );
    }

    /**
     * Deletes all mentions of the page as a special one.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/special-pages/landing-syspage-delete-for-landing.html
     *
     * @param int $pageId Page ID
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.syspage.deleteForLanding',
        'https://apidocs.bitrix24.com/api-reference/landing/page/special-pages/landing-syspage-delete-for-landing.html',
        'Deletes all mentions of the page as a special one.'
    )]
    public function deleteForLanding(int $pageId): SysPageResult
    {
        return new SysPageResult(
            $this->core->call('landing.syspage.deleteForLanding', ['id' => $pageId])
        );
    }

    /**
     * Deletes all special pages.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/special-pages/landing-syspage-delete-for-site.html
     *
     * @param int $siteId Site ID
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.syspage.deleteForSite',
        'https://apidocs.bitrix24.com/api-reference/landing/page/special-pages/landing-syspage-delete-for-site.html',
        'Deletes all special pages of the site.'
    )]
    public function deleteForSite(int $siteId): SysPageResult
    {
        return new SysPageResult(
            $this->core->call('landing.syspage.deleteForSite', ['id' => $siteId])
        );
    }
}
