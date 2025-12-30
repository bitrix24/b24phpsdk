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

namespace Bitrix24\SDK\Services\Landing\Demos\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Landing\Demos\Result\DemosGetListResult;
use Bitrix24\SDK\Services\Landing\Demos\Result\DemoResult;
use Bitrix24\SDK\Services\Landing\Demos\Result\SiteTemplateResult;
use Bitrix24\SDK\Services\Landing\Demos\Result\PageTemplateResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['landing']))]
class Demos extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Registers a template in the site and page creation wizard.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/demos/landing-demos-register.html
     *
     * @param array $data The result of the method landing.site.fullExport as is
     * @param array $params May contain keys (only for on-premise versions): site_template_id, lang, lang_original
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.demos.register',
        'https://apidocs.bitrix24.com/api-reference/landing/demos/landing-demos-register.html',
        'Method registers a template in the site and page creation wizard. Returns an array of identifiers for the created templates.'
    )]
    public function register(array $data, array $params = []): DemoResult
    {
        $callParams = ['data' => $data];

        if ($params !== []) {
            $callParams['params'] = $params;
        }

        return new DemoResult(
            $this->core->call('landing.demos.register', $callParams)
        );
    }

    /**
     * Deletes the registered partner template.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/demos/landing-demos-unregister.html
     *
     * @param string $code Symbolic code of the template
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.demos.unregister',
        'https://apidocs.bitrix24.com/api-reference/landing/demos/landing-demos-unregister.html',
        'Method deletes the registered partner template. Returns true or an error. If the template has already been deleted or not found, it will return false.'
    )]
    public function unregister(string $code): DemoResult
    {
        return new DemoResult(
            $this->core->call('landing.demos.unregister', ['code' => $code])
        );
    }

    /**
     * Retrieves a list of available partner templates for the current application.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/demos/landing-demos-get-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter conditions
     * @param array $order Sort order
     * @param array $group Group fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.demos.getList',
        'https://apidocs.bitrix24.com/api-reference/landing/demos/landing-demos-get-list.html',
        'Method retrieves a list of available partner templates for the current application.'
    )]
    public function getList(
        array $select = [],
        array $filter = [],
        array $order = [],
        array $group = []
    ): DemosGetListResult {
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

        $callParams = [];
        if ($params !== []) {
            $callParams['params'] = $params;
        }

        return new DemosGetListResult(
            $this->core->call('landing.demos.getList', $callParams)
        );
    }

    /**
     * Retrieves a list of available templates for creating sites.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/demos/landing-demos-get-site-list.html
     *
     * @param string $type Template type (page: regular sites, store: stores)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.demos.getSiteList',
        'https://apidocs.bitrix24.com/api-reference/landing/demos/landing-demos-get-site-list.html',
        'Method retrieves a list of available templates for creating sites, both partner and system templates.'
    )]
    public function getSiteList(string $type): SiteTemplateResult
    {
        return new SiteTemplateResult(
            $this->core->call('landing.demos.getSiteList', ['type' => $type])
        );
    }

    /**
     * Retrieves a list of available templates for creating pages.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/demos/landing-demos-get-page-list.html
     *
     * @param string $type Template type (page: regular sites, store: stores)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.demos.getPageList',
        'https://apidocs.bitrix24.com/api-reference/landing/demos/landing-demos-get-page-list.html',
        'Method retrieves a list of available templates for creating pages, both partner and system templates.'
    )]
    public function getPageList(string $type): PageTemplateResult
    {
        return new PageTemplateResult(
            $this->core->call('landing.demos.getPageList', ['type' => $type])
        );
    }
}
