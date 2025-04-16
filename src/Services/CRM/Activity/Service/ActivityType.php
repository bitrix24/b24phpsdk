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
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\Activity\Result\ActivityTypesResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['crm']))]
class ActivityType extends AbstractService
{
    /**
     * Contact constructor.
     */
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * The method registers a custom activity type with a name and icon.
     *
     * @link https://apidocs.bitrix24.ru/api-reference/crm/timeline/activities/types/crm-activity-type-add
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.activity.type.add',
        'https://apidocs.bitrix24.ru/api-reference/crm/timeline/activities/types/crm-activity-type-add',
        'The method registers a custom activity type with a name and icon.'
    )]
    public function add(array $fields): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call(
                'crm.activity.type.add',
                [
                    'fields' => $fields,
                ]
            )
        );
    }

    /**
     * Delete a custom case type.
     *
     * @link https://apidocs.bitrix24.ru/api-reference/crm/timeline/activities/types/crm-activity-type-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.activity.type.delete',
        'https://apidocs.bitrix24.ru/api-reference/crm/timeline/activities/types/crm-activity-type-delete.html',
        'Delete a custom activity type.'
    )]
    public function delete(string $itemId): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call(
                'crm.activity.type.delete',
                [
                    'TYPE_ID' => $itemId,
                ]
            )
        );
    }

    /**
     * Get a list of custom task types.
     *
     * @link https://apidocs.bitrix24.ru/api-reference/crm/timeline/activities/types/crm-activity-type-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.activity.type.list',
        'https://apidocs.bitrix24.ru/api-reference/crm/timeline/activities/types/crm-activity-type-list.html',
        'Get a list of custom task types.'
    )]
    public function list(): ActivityTypesResult
    {
        return new ActivityTypesResult(
            $this->core->call('crm.activity.type.list')
        );
    }
}
