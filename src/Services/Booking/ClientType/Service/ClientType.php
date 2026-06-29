<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Veronica Akhmetova <264936994+fatestr1ngs@users.noreply.github.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Booking\ClientType\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Booking\ClientType\Result\ClientTypesResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['booking']))]
class ClientType extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Retrieves a list of client types.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/booking-v1-clienttype-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.clienttype.list',
        'https://apidocs.bitrix24.com/api-reference/booking/booking-v1-clienttype-list.html',
        'Retrieves a list of client types.'
    )]
    public function list(): ClientTypesResult
    {
        return new ClientTypesResult(
            $this->core->call('booking.v1.clienttype.list')
        );
    }
}
