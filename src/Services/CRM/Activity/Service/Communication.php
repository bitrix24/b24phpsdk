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

namespace Bitrix24\SDK\Services\CRM\Activity\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['crm']))]
class Communication extends AbstractService
{
    public Batch $batch;

    /**
     * Contact constructor.
     *
     * @param Batch $batch
     * @param CoreInterface $core
     * @param LoggerInterface $log
     */
    public function __construct(Batch $batch, CoreInterface $core, LoggerInterface $log)
    {
        parent::__construct($core, $log);
        $this->batch = $batch;
    }

    /**
     * Get a description of the communication.
     *
     * @link https://apidocs.bitrix24.ru/api-reference/crm/timeline/activities/activity-base/crm-activity-communication-fields.html
     *
     * @return FieldsResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.activity.communication.fields',
        'https://apidocs.bitrix24.ru/api-reference/crm/timeline/activities/activity-base/crm-activity-communication-fields.html',
        'Get a description of the communication.'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('crm.activity.communication.fields'));
    }
}