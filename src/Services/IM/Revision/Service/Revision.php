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

namespace Bitrix24\SDK\Services\IM\Revision\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\IM\Revision\Result\RevisionResult;

#[ApiServiceMetadata(new Scope(['im']))]
class Revision extends AbstractService
{
    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.revision.get',
        'https://apidocs.bitrix24.com/api-reference/chats/im-revision-get.html',
        'Get IM module API revision numbers for client/server compatibility checks'
    )]
    public function get(): RevisionResult
    {
        return new RevisionResult($this->core->call('im.revision.get'));
    }
}
