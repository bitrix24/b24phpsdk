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

namespace Bitrix24\SDK\Services\Sign\B2e\PersonalTail\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Sign\B2e\PersonalTail\Result\PersonalTailResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['sign.b2e']))]
class PersonalTail extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Returns the list of signed documents for the current user from the КЭДО section.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sign/sign-b2e-personal-tail.html
     *
     * @param int $limit Number of records per page (1–50, default: 20)
     * @param int $offset Pagination offset (default: 0)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sign.b2e.personal.tail',
        'https://apidocs.bitrix24.com/api-reference/sign/sign-b2e-personal-tail.html',
        'Returns the list of signed documents for the current user from the КЭДО section. Requires application context.'
    )]
    public function tail(int $limit = 20, int $offset = 0): PersonalTailResult
    {
        return new PersonalTailResult(
            $this->core->call('sign.b2e.personal.tail', [
                'limit' => $limit,
                'offset' => $offset,
            ])
        );
    }
}
