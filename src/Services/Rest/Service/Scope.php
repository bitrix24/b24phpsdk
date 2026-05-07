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

namespace Bitrix24\SDK\Services\Rest\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope as ScopeCredential;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Rest\Result\ScopeMethodsResult;

#[ApiServiceMetadata(new ScopeCredential(['rest']))]
class Scope extends AbstractService
{
    /**
     * Returns the list of available REST API methods grouped by module/controller/method.
     *
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/rest/rest-scope-list.html
     */
    #[ApiEndpointMetadata(
        'rest.scope.list',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/rest/rest-scope-list.html',
        'Returns the list of available REST API methods grouped by module/controller/method',
        ApiVersion::v3
    )]
    public function list(
        ?string $filterModule = null,
        ?string $filterController = null,
        ?string $filterMethod = null,
    ): ScopeMethodsResult {
        return new ScopeMethodsResult(
            $this->core->call('rest.scope.list', [
                'filterModule'     => $filterModule,
                'filterController' => $filterController,
                'filterMethod'     => $filterMethod,
            ], ApiVersion::v3)
        );
    }
}
