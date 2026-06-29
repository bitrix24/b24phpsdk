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

namespace Bitrix24\SDK\Services\Sign\B2e\MySafeTail\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Sign\B2e\MySafeTail\Result\MySafeTailResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['sign.b2e']))]
class MySafeTail extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Returns the list of signed documents in the company safe.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sign/sign-b2e-mysafe-tail.html
     *
     * @param int $limit Number of records per page (1–50, default: 20)
     * @param int $offset Pagination offset (default: 0)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sign.b2e.mysafe.tail',
        'https://apidocs.bitrix24.com/api-reference/sign/sign-b2e-mysafe-tail.html',
        'Returns the list of signed documents in the company safe. Requires application context.'
    )]
    public function tail(int $limit = 20, int $offset = 0): MySafeTailResult
    {
        return new MySafeTailResult(
            $this->core->call('sign.b2e.mysafe.tail', [
                'limit' => $limit,
                'offset' => $offset,
            ])
        );
    }
}

