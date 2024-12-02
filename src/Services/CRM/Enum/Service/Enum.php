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

namespace Bitrix24\SDK\Services\CRM\Enum\Service;

use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\Enum\Result\ActivityDirectionResult;
use Bitrix24\SDK\Services\CRM\Enum\Result\ActivityNotifyTypeResult;
use Bitrix24\SDK\Services\CRM\Enum\Result\ActivityPriorityTypeResult;
use Bitrix24\SDK\Services\CRM\Enum\Result\ActivityStatusResult;
use Bitrix24\SDK\Services\CRM\Enum\Result\ActivityTypeResult;
use Bitrix24\SDK\Services\CRM\Enum\Result\AddressTypeFieldsResult;
use Bitrix24\SDK\Services\CRM\Enum\Result\CrmSettingsModeResult;
use Bitrix24\SDK\Services\CRM\Enum\Result\OrderOwnerTypesResult;
use Bitrix24\SDK\Services\CRM\Enum\Result\OwnerTypesResult;
use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;

#[ApiServiceMetadata(new Scope(['crm']))]
class Enum extends AbstractService
{
    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.enum.ownertype',
        'https://apidocs.bitrix24.com/api-reference/crm/auxiliary/enum/crm-enum-owner-type.html',
        'This method returns the identifiers of CRM entity types and SPAs.'
    )]
    public function ownerType(): OwnerTypesResult
    {
        return new OwnerTypesResult($this->core->call('crm.enum.ownertype'));
    }

    #[ApiEndpointMetadata(
        'crm.enum.activityStatus',
        'https://training.bitrix24.com/rest_help/crm/mode/crm_settings_mode_get.php',
        'The method returns activity status list'
    )]
    public function activityStatus(): ActivityStatusResult
    {
        return new ActivityStatusResult($this->core->call('crm.enum.activityStatus'));
    }

    #[ApiEndpointMetadata(
        'crm.enum.addressType',
        'https://apidocs.bitrix24.com/api-reference/crm/auxiliary/enum/crm-enum-address-type.html',
        'Returns the enumeration items for "Address Type".'
    )]
    public function addressType(): AddressTypeFieldsResult
    {
        return new AddressTypeFieldsResult($this->core->call('crm.enum.addressType'));
    }

    #[ApiEndpointMetadata(
        'crm.enum.activitynotifytype',
        'https://apidocs.bitrix24.com/api-reference/crm/auxiliary/enum/crm-enum-activity-notify-type.html',
        'Returns the enumeration items "Activity Notification Type" (for meetings and calls).'
    )]
    public function activityNotifyType(): ActivityNotifyTypeResult
    {
        return new ActivityNotifyTypeResult($this->core->call('crm.enum.activitynotifytype'));
    }

    #[ApiEndpointMetadata(
        'crm.enum.activitypriority',
        'https://apidocs.bitrix24.com/api-reference/crm/auxiliary/enum/crm-enum-activity-priority.html',
        'Returns the enumeration items "Activity Priority".'
    )]
    public function activityPriority(): ActivityPriorityTypeResult
    {
        return new ActivityPriorityTypeResult($this->core->call('crm.enum.activitypriority'));
    }

    #[ApiEndpointMetadata(
        'crm.enum.activitydirection',
        'https://apidocs.bitrix24.com/api-reference/crm/auxiliary/enum/crm-enum-activity-direction.html',
        'The method returns activity direction list'
    )]
    public function activityDirection(): ActivityDirectionResult
    {
        return new ActivityDirectionResult($this->core->call('crm.enum.activitydirection'));
    }

    #[ApiEndpointMetadata(
        'crm.enum.activitytype',
        'https://apidocs.bitrix24.com/api-reference/crm/auxiliary/enum/crm-enum-activity-type.html',
        'Returns the enumeration elements "Activity Type".'
    )]
    public function activityType(): ActivityTypeResult
    {
        return new ActivityTypeResult($this->core->call('crm.enum.activitytype'));
    }

    #[ApiEndpointMetadata(
        'crm.enum.settings.mode',
        'https://apidocs.bitrix24.com/api-reference/crm/auxiliary/enum/crm-enum-settings-mode.html',
        'Returns a description of the CRM operating modes.'
    )]
    public function settingsMode(): CrmSettingsModeResult
    {
        return new CrmSettingsModeResult($this->core->call('crm.enum.settings.mode'));
    }

    #[ApiEndpointMetadata(
        'crm.enum.contentType',
        'https://apidocs.bitrix24.com/api-reference/crm/auxiliary/enum/crm-enum-content-type.html',
        'Returns the enumeration items for "Content Type".'
    )]
    public function contentType(): CrmSettingsModeResult
    {
        return new CrmSettingsModeResult($this->core->call('crm.enum.contentType'));
    }

    #[ApiEndpointMetadata(
        'crm.enum.getorderownertypes',
        'https://apidocs.bitrix24.com/api-reference/crm/auxiliary/enum/crm-enum-get-order-owner-types.html',
        'This method returns the identifiers of the entity types to which an order can be linked.'
    )]
    public function orderOwnerTypes(): OrderOwnerTypesResult
    {
        return new OrderOwnerTypesResult($this->core->call('crm.enum.getorderownertypes'));
    }

    #[ApiEndpointMetadata(
        'crm.enum.fields',
        'https://apidocs.bitrix24.com/api-reference/crm/auxiliary/enum/crm-enum-fields.html',
        'The method returns the description of enumeration fields.'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('crm.enum.fields'));
    }
}
