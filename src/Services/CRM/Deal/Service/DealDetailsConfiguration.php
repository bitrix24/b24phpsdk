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

namespace Bitrix24\SDK\Services\CRM\Deal\Service;

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
class DealDetailsConfiguration extends AbstractService
{
    /**
     * The method retrieves the settings of deal cards for personal user.
     *
     * @param non-negative-int|null $userId
     * @link https://apidocs.bitrix24.com/api-reference/crm/deals/custom-form/crm-deal-details-configuration-get.html
     */
    #[ApiEndpointMetadata(
        'crm.deal.details.configuration.get',
        'https://apidocs.bitrix24.com/api-reference/crm/deals/custom-form/crm-deal-details-configuration-get.html',
        'The method crm.deal.details.configuration.get retrieves the settings of deal cards for personal user'
    )]
    public function getPersonal(?int $userId = null): CardConfigurationsResult
    {
        return new CardConfigurationsResult($this->core->call('crm.deal.details.configuration.get', [
            'scope' => 'P',
            'userId' => $userId
        ]));
    }

    /**
     * The method retrieves the settings of deal cards for all users.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/deals/custom-form/crm-deal-details-configuration-get.html
     */
    #[ApiEndpointMetadata(
        'crm.deal.details.configuration.get',
        'https://apidocs.bitrix24.com/api-reference/crm/deals/custom-form/crm-deal-details-configuration-get.html',
        'The method crm.deal.details.configuration.get retrieves the settings of deal cards for all users'
    )]
    public function getGeneral(): CardConfigurationsResult
    {
        return new CardConfigurationsResult($this->core->call('crm.deal.details.configuration.get', [
            'scope' => 'C',
        ]));
    }

    /**
     * The method resets the settings of deal cards for personal user.
     *
     * @param non-negative-int|null $userId
     * @link https://apidocs.bitrix24.com/api-reference/crm/deals/custom-form/crm-deal-details-configuration-reset.html
     */
    #[ApiEndpointMetadata(
        'crm.deal.details.configuration.reset',
        'https://apidocs.bitrix24.com/api-reference/crm/deals/custom-form/crm-deal-details-configuration-reset.html',
        'The method crm.deal.details.configuration.get retrieves the settings of deal cards for personal user'
    )]
    public function resetPersonal(?int $userId = null): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('crm.deal.details.configuration.reset', [
            'scope' => 'P',
            'userId' => $userId
        ]));
    }

    /**
     * The method resets the settings of deal cards for all users.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/deals/custom-form/crm-deal-details-configuration-reset.html
     */
    #[ApiEndpointMetadata(
        'crm.deal.details.configuration.reset',
        'https://apidocs.bitrix24.com/api-reference/crm/deals/custom-form/crm-deal-details-configuration-reset.html',
        'The method crm.deal.details.configuration.get retrieves the settings of deal cards for all users'
    )]
    public function resetGeneral(): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('crm.deal.details.configuration.reset', [
            'scope' => 'C',
        ]));
    }

    /**
     * Set Parameters for Individual CRM Company Detail Card Configuration
     * @param CardSectionConfiguration[] $cardConfiguration
     * @throws InvalidArgumentException
     * @link https://apidocs.bitrix24.com/api-reference/crm/deals/custom-form/crm-deal-details-configuration-set.html
     */
    #[ApiEndpointMetadata(
        'crm.deal.details.configuration.set',
        'https://apidocs.bitrix24.com/api-reference/crm/deals/custom-form/crm-deal-details-configuration-set.html',
        'Set Parameters for Individual CRM Deal Detail Card Configuration'
    )]
    public function setPersonal(array $cardConfiguration, ?int $userId = null): UpdatedItemResult
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

        return new UpdatedItemResult($this->core->call('crm.deal.details.configuration.reset', [
            'scope' => 'P',
            'userId' => $userId,
            'data' => $rawData
        ]));
    }

    /**
     * Set CRM Deal Detail Card Configuration for all users.
     * @param CardSectionConfiguration[] $cardConfiguration
     * @throws InvalidArgumentException
     * @link https://apidocs.bitrix24.com/api-reference/crm/deals/custom-form/crm-deal-details-configuration-set.html
     */
    #[ApiEndpointMetadata(
        'crm.deal.details.configuration.reset',
        'https://apidocs.bitrix24.com/api-reference/crm/deals/custom-form/crm-deal-details-configuration-set.html',
        'Set CRM Deal Detail Card Configuration for all users'
    )]
    public function setGeneral(array $cardConfiguration): UpdatedItemResult
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

        return new UpdatedItemResult($this->core->call('crm.deal.details.configuration.reset', [
            'scope' => 'C',
            'data' => $rawData
        ]));
    }

    /**
     * Set Common Detail Form for All Users
     * @throws InvalidArgumentException
     * @link https://apidocs.bitrix24.com/api-reference/crm/deals/custom-form/crm-deal-details-configuration-force-common-scope-for-all.html
     */
    #[ApiEndpointMetadata(
        'crm.deal.details.configuration.forceCommonScopeForAll',
        'https://apidocs.bitrix24.com/api-reference/crm/deals/custom-form/crm-deal-details-configuration-force-common-scope-for-all.html',
        'Set Common Detail Form for All Users '
    )]
    public function setForceCommonConfigForAll(): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('crm.deal.details.configuration.forceCommonScopeForAll'));
    }
}
