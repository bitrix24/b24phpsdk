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

namespace Bitrix24\SDK\Services\Landing\Template\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Landing\Template\Result\TemplatesResult;
use Bitrix24\SDK\Services\Landing\Template\Result\TemplateRefsResult;
use Bitrix24\SDK\Services\Landing\Template\Result\TemplateRefSetResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['landing']))]
class Template extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Retrieves a list of templates
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/template/landing-template-get-list.html
     *
     * @param array $select Optional array of fields to select
     * @param array $filter Optional array of filter conditions
     * @param array $order Optional array of order conditions
     *
     * @return TemplatesResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.template.getlist',
        'https://apidocs.bitrix24.com/api-reference/landing/template/landing-template-get-list.html',
        'Method retrieves a list of templates.'
    )]
    public function getList(array $select = [], array $filter = [], array $order = []): TemplatesResult
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

        $requestParams = [];
        if ($params !== []) {
            $requestParams['params'] = $params;
        }

        return new TemplatesResult(
            $this->core->call('landing.template.getlist', $requestParams)
        );
    }

    /**
     * Retrieves a list of included areas for the page
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/template/landing-template-get-landing-ref.html
     *
     * @param int $id Page identifier
     *
     * @return TemplateRefsResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.template.getLandingRef',
        'https://apidocs.bitrix24.com/api-reference/landing/template/landing-template-get-landing-ref.html',
        'Method retrieves a list of included areas for the page. The keys of the returned array are the identifiers of the included areas, and the values are the identifiers of the pages.'
    )]
    public function getLandingRef(int $id): TemplateRefsResult
    {
        return new TemplateRefsResult(
            $this->core->call('landing.template.getLandingRef', ['id' => $id])
        );
    }

    /**
     * Retrieves a list of included areas for the site
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/template/landing-template-get-site-ref.html
     *
     * @param int $id Site identifier
     *
     * @return TemplateRefsResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.template.getSiteRef',
        'https://apidocs.bitrix24.com/api-reference/landing/template/landing-template-get-site-ref.html',
        'Method retrieves a list of included areas for the site. The keys of the returned array are the identifiers of the included areas, and the values are the page identifiers.'
    )]
    public function getSiteRef(int $id): TemplateRefsResult
    {
        return new TemplateRefsResult(
            $this->core->call('landing.template.getSiteRef', ['id' => $id])
        );
    }

    /**
     * Sets the included areas for the page
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/template/landing-template-set-landing-ref.html
     *
     * @param int $id Identifier of the page
     * @param array $data Array of data to set (if the array is empty or not provided, the included areas will be reset). The keys of the array are the identifiers of the areas, and the values are the identifiers of the pages that need to be set as the area
     *
     * @return TemplateRefSetResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.template.setLandingRef',
        'https://apidocs.bitrix24.com/api-reference/landing/template/landing-template-set-landing-ref.html',
        'Method sets the included areas for the page within a specific template (the page must already be linked to the template via the TPL_ID field). It will return true on success or an error.'
    )]
    public function setLandingRef(int $id, array $data = []): TemplateRefSetResult
    {
        $params = ['id' => $id];
        if ($data !== []) {
            $params['data'] = $data;
        }

        return new TemplateRefSetResult(
            $this->core->call('landing.template.setLandingRef', $params)
        );
    }

    /**
     * Sets the included areas for the site
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/template/landing-template-set-site-ref.html
     *
     * @param int $id Site identifier
     * @param array $data Array of data to set (if the array is empty or not provided, the included areas will be reset). The keys of the array are the area identifiers, and the values are the identifiers of the pages that need to be set as the area
     *
     * @return TemplateRefSetResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.template.setSiteRef',
        'https://apidocs.bitrix24.com/api-reference/landing/template/landing-template-set-site-ref.html',
        'Method sets the included areas for the site within a specific template (the site or page must already be linked to the template via the TPL_ID field). It will return true on success or an error.'
    )]
    public function setSiteRef(int $id, array $data = []): TemplateRefSetResult
    {
        $params = ['id' => $id];
        if ($data !== []) {
            $params['data'] = $data;
        }

        return new TemplateRefSetResult(
            $this->core->call('landing.template.setSiteRef', $params)
        );
    }
}
