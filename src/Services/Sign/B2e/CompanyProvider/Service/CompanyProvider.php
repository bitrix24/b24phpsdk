<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sign\B2e\CompanyProvider\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Sign\B2e\CompanyProvider\Result\CompanyProvidersResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['sign.b2e']))]
class CompanyProvider extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Returns the list of signature providers for a selected company.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sign/sign-b2e-company-provider-list.html
     *
     * @param string|null $companyUuid Company UUID in HCM Link (required if companyCrmId is not provided)
     * @param int|null $companyCrmId Company CRM identifier (required if companyUuid is not provided)
     * @param string|null $language Language for provider name localisation (default: en)
     * @param int $limit Number of records per page (1–1000, default: 100)
     * @param int $offset Pagination offset (default: 0)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sign.b2e.company.provider.list',
        'https://apidocs.bitrix24.com/api-reference/sign/sign-b2e-company-provider-list.html',
        'Returns the list of signature providers for a selected company.'
    )]
    public function list(
        ?string $companyUuid = null,
        ?int $companyCrmId = null,
        ?string $language = null,
        int $limit = 100,
        int $offset = 0
    ): CompanyProvidersResult {
        $params = [
            'limit' => $limit,
            'offset' => $offset,
        ];
        if ($companyUuid !== null) {
            $params['companyUuid'] = $companyUuid;
        }

        if ($companyCrmId !== null) {
            $params['companyCrmId'] = $companyCrmId;
        }

        if ($language !== null) {
            $params['language'] = $language;
        }

        return new CompanyProvidersResult($this->core->call('sign.b2e.company.provider.list', $params));
    }
}

