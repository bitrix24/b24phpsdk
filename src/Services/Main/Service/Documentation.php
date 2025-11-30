<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Main\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Exceptions\WrongSecuritySignatureException;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Main\Result\ApplicationInfoResult;
use Bitrix24\SDK\Services\Main\Result\DocumentationResult;
use Bitrix24\SDK\Services\Main\Result\IsUserAdminResult;
use Bitrix24\SDK\Services\Main\Result\MethodAffordabilityResult;
use Bitrix24\SDK\Services\Main\Result\ServerTimeResult;
use Bitrix24\SDK\Services\Main\Result\UserProfileResult;

#[ApiServiceMetadata(new Scope([]))]
class Documentation extends AbstractService
{
    /**
     * Method returns documentation in openapi format
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'documentation',
        '',
        'Get documentation in Open API format',
        ApiVersion::v3
    )]
    public function getSchema(): DocumentationResult
    {
        return new DocumentationResult($this->core->call('documentation', version: ApiVersion::v3));
    }
}