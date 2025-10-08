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

namespace Bitrix24\SDK\Services\Disk\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Disk\Result\VersionItemResult;
use Bitrix24\SDK\Services\Disk\Result\AttachedObjectItemResult;
use Bitrix24\SDK\Services\Disk\Result\RightsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['disk']))]
class Disk extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Returns the version by identifier.
     *
     * @link https://apidocs.bitrix24.com/api-reference/disk/version/disk-version-get.html
     *
     * @param int $id Version identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'disk.version.get',
        'https://apidocs.bitrix24.com/api-reference/disk/version/disk-version-get.html',
        'Returns the version by identifier.'
    )]
    public function getVersion(int $id): VersionItemResult
    {
        return new VersionItemResult(
            $this->core->call(
                'disk.version.get',
                ['id' => $id]
            )->getResponseData()->getResult()
        );
    }

    /**
     * Returns information about the attached file.
     *
     * @link https://apidocs.bitrix24.com/api-reference/disk/attached-object/disk-attached-object-get.html
     *
     * @param int $id Attachment binding identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'disk.attachedObject.get',
        'https://apidocs.bitrix24.com/api-reference/disk/attached-object/disk-attached-object-get.html',
        'Returns information about the attached file.'
    )]
    public function getAttachedObject(int $id): AttachedObjectItemResult
    {
        return new AttachedObjectItemResult(
            $this->core->call(
                'disk.attachedObject.get',
                ['id' => $id]
            )->getResponseData()->getResult()
        );
    }

    /**
     * Returns a list of available access levels that can be used for assigning permissions.
     *
     * @link https://apidocs.bitrix24.com/api-reference/disk/rights/disk-rights-get-tasks.html
     *
     * @param int|null $start The ordinal number of the list item from which to return the next items
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'disk.rights.getTasks',
        'https://apidocs.bitrix24.com/api-reference/disk/rights/disk-rights-get-tasks.html',
        'Returns a list of available access levels that can be used for assigning permissions.'
    )]
    public function getRightsTasks(?int $start = null): RightsResult
    {
        $params = [];
        if ($start !== null) {
            $params['start'] = $start;
        }

        return new RightsResult(
            $this->core->call(
                'disk.rights.getTasks',
                $params
            )
        );
    }
}
