<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Item\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\Common\CardFieldConfiguration;
use Bitrix24\SDK\Services\CRM\Common\CardSectionConfiguration;
use Bitrix24\SDK\Services\CRM\Common\Result\ElementCardConfiguration\CardConfigurationsResult;

#[ApiServiceMetadata(new Scope(['crm']))]
class ItemDetailsConfiguration extends AbstractService
{
    /**
     * Get Parameters of CRM Item Detail Configuration for personal user.
     *
     * @param non-negative-int $entityTypeId
     * @param non-negative-int $userId
     * @link https://apidocs.bitrix24.com/api-reference/crm/universal/item-details-configuration/index.html
     */
    #[ApiEndpointMetadata(
        'crm.item.details.configuration.get',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/item-details-configuration/index.html',
        'Get Parameters of CRM Item Detail Configuration for personal user'
    )]
    public function getPersonal(int $entityTypeId, int $userId, array $extras = []): CardConfigurationsResult
    {
        return new CardConfigurationsResult($this->core->call('crm.item.details.configuration.get', [
            'scope' => 'P',
            'userId' => $userId,
            'entityTypeId' => $entityTypeId,
            'extras' => $extras
        ]));
    }

    /**
     * Get Parameters of CRM Item Detail Configuration for all users.
     *
     * @param non-negative-int $entityTypeId
     * @link https://apidocs.bitrix24.com/api-reference/crm/universal/item-details-configuration/index.html
     */
    #[ApiEndpointMetadata(
        'crm.item.details.configuration.get',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/item-details-configuration/index.html',
        'Get Parameters of CRM Item Detail Configuration for all users'
    )]
    public function getGeneral(int $entityTypeId, array $extras = []): CardConfigurationsResult
    {
        return new CardConfigurationsResult($this->core->call('crm.item.details.configuration.get', [
            'scope' => 'C',
            'entityTypeId' => $entityTypeId,
            'extras' => $extras
        ]));
    }

    /**
     * Reset Item Card Parameters for personal user.
     *
     * @param non-negative-int $entityTypeId
     * @param non-negative-int $userId
     * @link https://apidocs.bitrix24.com/api-reference/crm/universal/item-details-configuration/crm-item-details-configuration-reset.html
     */
    #[ApiEndpointMetadata(
        'crm.item.details.configuration.reset',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/item-details-configuration/crm-item-details-configuration-reset.html',
        'Reset Item Card Parameters for personal user'
    )]
    public function resetPersonal(int $entityTypeId, int $userId, array $extras = []): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('crm.item.details.configuration.reset', [
            'scope' => 'P',
            'userId' => $userId,
            'entityTypeId' => $entityTypeId,
            'extras' => $extras
        ]));
    }

    /**
     * Reset Item Card Parameters for all users.
     *
     * @param non-negative-int $entityTypeId
     * @link https://apidocs.bitrix24.com/api-reference/crm/universal/item-details-configuration/crm-item-details-configuration-reset.html
     */
    #[ApiEndpointMetadata(
        'crm.item.details.configuration.reset',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/item-details-configuration/crm-item-details-configuration-reset.html',
        'Reset Item Card Parameters for all users'
    )]
    public function resetGeneral(int $entityTypeId, array $extras = []): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('crm.item.details.configuration.reset', [
            'scope' => 'C',
            'entityTypeId' => $entityTypeId,
            'extras' => $extras
        ]));
    }

    /**
     * Set Parameters for Individual CRM Item Detail Card Configuration
     *
     * @param CardSectionConfiguration[] $cardConfiguration
     * @param non-negative-int $entityTypeId
     * @param non-negative-int $userId
     * @throws InvalidArgumentException
     * @link https://apidocs.bitrix24.com/api-reference/crm/universal/item-details-configuration/crm-item-details-configuration-set.html
     */
    #[ApiEndpointMetadata(
        'crm.item.details.configuration.set',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/item-details-configuration/crm-item-details-configuration-set.html',
        'Set Parameters for Individual CRM Item Detail Card Configuration'
    )]
    public function setPersonal(array $cardConfiguration, int $entityTypeId, int $userId, array $extras = []): UpdatedItemResult
    {
        $rawData = [];
        foreach ($cardConfiguration as $sectionItem) {
            if (!$sectionItem instanceof CardSectionConfiguration) {
                throw new InvalidArgumentException(
                    sprintf(
                        'card configuration section mus be «%s» type, current type «%s»',
                        CardFieldConfiguration::class,
                        gettype($sectionItem)
                    )
                );
            }

            $rawData[] = $sectionItem->toArray();
        }

        return new UpdatedItemResult($this->core->call('crm.item.details.configuration.set', [
            'scope' => 'P',
            'userId' => $userId,
            'data' => $rawData,
            'entityTypeId' => $entityTypeId,
            'extras' => $extras
        ]));
    }

    /**
     * Set CRM Item Detail Card Configuration for all users.
     *
     * @param CardSectionConfiguration[] $cardConfiguration
     * @param non-negative-int $entityTypeId
     * @throws InvalidArgumentException
     * @link https://apidocs.bitrix24.com/api-reference/crm/universal/item-details-configuration/crm-item-details-configuration-set.html
     */
    #[ApiEndpointMetadata(
        'crm.item.details.configuration.set',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/item-details-configuration/crm-item-details-configuration-set.html',
        'Set CRM Item Detail Card Configuration for all users'
    )]
    public function setGeneral(array $cardConfiguration, int $entityTypeId, array $extras = []): UpdatedItemResult
    {
        $rawData = [];
        foreach ($cardConfiguration as $sectionItem) {
            if (!$sectionItem instanceof CardSectionConfiguration) {
                throw new InvalidArgumentException(
                    sprintf(
                        'card configuration section mus be «%s» type, current type «%s»',
                        CardFieldConfiguration::class,
                        gettype($sectionItem)
                    )
                );
            }

            $rawData[] = $sectionItem->toArray();
        }

        return new UpdatedItemResult($this->core->call('crm.item.details.configuration.set', [
            'scope' => 'C',
            'data' => $rawData,
            'entityTypeId' => $entityTypeId,
            'extras' => $extras
        ]));
    }

    /**
     * Set Common Detail Form for All Users
     *
     * @param non-negative-int $entityTypeId
     * @throws InvalidArgumentException
     * @link https://apidocs.bitrix24.com/api-reference/crm/deals/custom-form/crm-deal-details-configuration-force-common-scope-for-all.html
     */
    #[ApiEndpointMetadata(
        'crm.item.details.configuration.forceCommonScopeForAll',
        'https://apidocs.bitrix24.com/api-reference/crm/deals/custom-form/crm-deal-details-configuration-force-common-scope-for-all.html',
        'Set Common Detail Form for All Users '
    )]
    public function setForceCommonConfigForAll(int $entityTypeId, array $extras = []): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('crm.item.details.configuration.forceCommonScopeForAll', [
            'entityTypeId' => $entityTypeId,
            'extras' => $extras
        ]));
    }
}
