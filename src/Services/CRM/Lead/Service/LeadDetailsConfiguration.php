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

namespace Bitrix24\SDK\Services\CRM\Lead\Service;

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
class LeadDetailsConfiguration extends AbstractService
{
    /**
     * The method retrieves the settings of lead cards for personal user.
     *
     * @param non-negative-int $userId
     * @param array $extras
     * @link https://apidocs.bitrix24.com/api-reference/crm/leads/custom-form/crm-lead-details-configuration-get.html
     */
    #[ApiEndpointMetadata(
        'crm.lead.details.configuration.get',
        'https://apidocs.bitrix24.com/api-reference/crm/leads/custom-form/crm-lead-details-configuration-get.html',
        'The method crm.lead.details.configuration.get retrieves the settings of lead cards for personal user.'
    )]
    public function getPersonal(int $userId, ?array $extras = []): CardConfigurationsResult
    {
        return new CardConfigurationsResult($this->core->call('crm.lead.details.configuration.get', [
            'scope' => 'P',
            'userId' => $userId,
            'extras' => $extras
        ]));
    }

    /**
     * The method retrieves the settings of lead cards for all users.
     *
     * @param array $extras
     * @link https://apidocs.bitrix24.com/api-reference/crm/leads/custom-form/crm-lead-details-configuration-get.html
     */
    #[ApiEndpointMetadata(
        'crm.lead.details.configuration.get',
        'https://apidocs.bitrix24.com/api-reference/crm/leads/custom-form/crm-lead-details-configuration-get.html',
        'The method crm.lead.details.configuration.get retrieves the settings of lead cards for all users.'
    )]
    public function getGeneral(?array $extras = []): CardConfigurationsResult
    {
        return new CardConfigurationsResult($this->core->call('crm.lead.details.configuration.get', [
            'scope' => 'C',
            'extras' => $extras
        ]));
    }

    /**
     * The method resets the settings of lead cards for personal user.
     *
     * @param non-negative-int $userId
     * @param array $extras
     * @link https://apidocs.bitrix24.com/api-reference/crm/leads/custom-form/crm-lead-details-configuration-reset.html
     */
    #[ApiEndpointMetadata(
        'crm.lead.details.configuration.reset',
        'https://apidocs.bitrix24.com/api-reference/crm/leads/custom-form/crm-lead-details-configuration-reset.html',
        'The method crm.lead.details.configuration.get retrieves the settings of lead cards for personal user.'
    )]
    public function resetPersonal(int $userId, ?array $extras = []): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('crm.lead.details.configuration.reset', [
            'scope' => 'P',
            'userId' => $userId,
            'extras' => $extras
        ]));
    }

    /**
     * The method resets the settings of lead cards for all users.
     *
     * @param array $extras
     * @link https://apidocs.bitrix24.com/api-reference/crm/leads/custom-form/crm-lead-details-configuration-reset.html
     */
    #[ApiEndpointMetadata(
        'crm.lead.details.configuration.reset',
        'https://apidocs.bitrix24.com/api-reference/crm/leads/custom-form/crm-lead-details-configuration-reset.html',
        'The method crm.lead.details.configuration.get retrieves the settings of lead cards for all users.'
    )]
    public function resetGeneral(?array $extras = []): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('crm.lead.details.configuration.reset', [
            'scope' => 'C',
            'extras' => $extras
        ]));
    }

    /**
     * Set Parameters for Individual CRM Lead Detail Card Configuration
     * @param CardSectionConfiguration[] $cardConfiguration
     * @param non-negative-int $userId
     * @param array $extras
     * @throws InvalidArgumentException
     * @link https://apidocs.bitrix24.com/api-reference/crm/leads/custom-form/crm-lead-details-configuration-set.html
     */
    #[ApiEndpointMetadata(
        'crm.lead.details.configuration.set',
        'https://apidocs.bitrix24.com/api-reference/crm/leads/custom-form/crm-lead-details-configuration-set.html',
        'Set Parameters for Individual CRM Lead Detail Card Configuration.'
    )]
    public function setPersonal(array $cardConfiguration, int $userId, ?array $extras = []): UpdatedItemResult
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

        return new UpdatedItemResult($this->core->call('crm.lead.details.configuration.set', [
            'scope' => 'P',
            'userId' => $userId,
            'data' => $rawData,
            'extras' => $extras
        ]));
    }

    /**
     * Set CRM Lead Detail Card Configuration for all users.
     * @param CardSectionConfiguration[] $cardConfiguration
     * @param array $extras
     * @throws InvalidArgumentException
     * @link https://apidocs.bitrix24.com/api-reference/crm/leads/custom-form/crm-lead-details-configuration-set.html
     */
    #[ApiEndpointMetadata(
        'crm.lead.details.configuration.set',
        'https://apidocs.bitrix24.com/api-reference/crm/leads/custom-form/crm-lead-details-configuration-set.html',
        'Set CRM Lead Detail Card Configuration for all users.'
    )]
    public function setGeneral(array $cardConfiguration, ?array $extras = []): UpdatedItemResult
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

        return new UpdatedItemResult($this->core->call('crm.lead.details.configuration.set', [
            'scope' => 'C',
            'data' => $rawData,
            'extras' => $extras
        ]));
    }

    /**
     * Set Common Detail Form for All Users
     * 
     * @param array $extras
     * @throws InvalidArgumentException
     * @link https://apidocs.bitrix24.com/api-reference/crm/leads/custom-form/crm-lead-details-configuration-force-common-scope-for-all.html
     */
    #[ApiEndpointMetadata(
        'crm.lead.details.configuration.forceCommonScopeForAll',
        'https://apidocs.bitrix24.com/api-reference/crm/leads/custom-form/crm-lead-details-configuration-force-common-scope-for-all.html',
        'Set Common Detail Form for All Users.'
    )]
    public function setForceCommonConfigForAll(?array $extras = []): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('crm.lead.details.configuration.forceCommonScopeForAll', [
            'extras' => $extras
        ]));
    }
}
