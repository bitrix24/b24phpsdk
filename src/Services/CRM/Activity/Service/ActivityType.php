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
     * The method registers a custom case type with a name and icon.
     *
     * @link https://apidocs.bitrix24.ru/api-reference/crm/timeline/activities/types/crm-activity-type-add
     *
     * @param array{
     *   ID?: int,
     *   OWNER_ID?: int,
     *   OWNER_TYPE_ID?: int,
     *   TYPE_ID?: int,
     *   PROVIDER_ID?: string,
     *   PROVIDER_TYPE_ID?: string,
     *   PROVIDER_GROUP_ID?: string,
     *   ASSOCIATED_ENTITY_ID?: int,
     *   SUBJECT?: string,
     *   START_TIME?: string,
     *   END_TIME?: string,
     *   DEADLINE?: string,
     *   COMPLETED?: string,
     *   STATUS?: string,
     *   RESPONSIBLE_ID?: string,
     *   PRIORITY?: string,
     *   NOTIFY_TYPE?: string,
     *   NOTIFY_VALUE?: int,
     *   DESCRIPTION?: string,
     *   DESCRIPTION_TYPE?: string,
     *   DIRECTION?: string,
     *   LOCATION?: string,
     *   CREATED?: string,
     *   AUTHOR_ID?: string,
     *   LAST_UPDATED?: string,
     *   EDITOR_ID?: string,
     *   SETTINGS?: string,
     *   ORIGIN_ID?: string,
     *   ORIGINATOR_ID?: string,
     *   RESULT_STATUS?: int,
     *   RESULT_STREAM?: int,
     *   RESULT_SOURCE_ID?: string,
     *   PROVIDER_PARAMS?: string,
     *   PROVIDER_DATA?: string,
     *   RESULT_MARK?: int,
     *   RESULT_VALUE?: string,
     *   RESULT_SUM?: string,
     *   RESULT_CURRENCY_ID?: string,
     *   AUTOCOMPLETE_RULE?: int,
     *   BINDINGS?: string,
     *   COMMUNICATIONS?: string,
     *   FILES?: string,
     *   WEBDAV_ELEMENTS?: string,
     *   } $fields
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
