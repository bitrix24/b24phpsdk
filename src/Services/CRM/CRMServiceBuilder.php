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

namespace Bitrix24\SDK\Services\CRM;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;
use Bitrix24\SDK\Services\CRM\Userfield\Service\UserfieldConstraints;
use Bitrix24\SDK\Services\CRM\Company;
use Bitrix24\SDK\Services\CRM\VatRates\Service\Vat;

#[ApiServiceBuilderMetadata(new Scope(['crm']))]
class CRMServiceBuilder extends AbstractServiceBuilder
{
    public function requisite(): Requisites\Service\Requisite
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Requisites\Service\Requisite(
                new Requisites\Service\Batch($this->batch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function requisitePreset(): Requisites\Service\RequisitePreset
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Requisites\Service\RequisitePreset(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function contactCompany(): Contact\Service\ContactCompany
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Contact\Service\ContactCompany(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function companyDetailsConfiguration(): Company\Service\CompanyDetailsConfiguration
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Company\Service\CompanyDetailsConfiguration(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
    
    public function itemDetailsConfiguration(): Item\Service\ItemDetailsConfiguration
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Item\Service\ItemDetailsConfiguration(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function dealDetailsConfiguration(): Deal\Service\DealDetailsConfiguration
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Deal\Service\DealDetailsConfiguration(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function leadDetailsConfiguration(): Lead\Service\LeadDetailsConfiguration
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Lead\Service\LeadDetailsConfiguration(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function contactDetailsConfiguration(): Contact\Service\ContactDetailsConfiguration
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Contact\Service\ContactDetailsConfiguration(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function vat(): VatRates\Service\Vat
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new VatRates\Service\Vat(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
    
    public function currency(): Currency\Service\Currency
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $batch = new Currency\Batch(
                $this->core,
                $this->log
            );
            $this->serviceCache[__METHOD__] = new Currency\Service\Currency(
                new Currency\Service\Batch($batch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
    
    public function localizations(): Currency\Localizations\Service\Localizations
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $batch = new Currency\Localizations\Batch(
                $this->core,
                $this->log
            );
            $this->serviceCache[__METHOD__] = new Currency\Localizations\Service\Localizations(
                new Currency\Localizations\Service\Batch($batch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function companyContact(): Company\Service\CompanyContact
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Company\Service\CompanyContact(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function companyUserfield(): Company\Service\CompanyUserfield
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Company\Service\CompanyUserfield(
                new UserfieldConstraints(),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function company(): Company\Service\Company
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Company\Service\Company(
                new Company\Service\Batch($this->batch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function enum(): Enum\Service\Enum
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Enum\Service\Enum($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function settings(): Settings\Service\Settings
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Settings\Service\Settings($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function dealContact(): Deal\Service\DealContact
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Deal\Service\DealContact($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function dealCategory(): Deal\Service\DealCategory
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Deal\Service\DealCategory($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }
    
    public function dealRecurring(): Deal\Service\DealRecurring
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Deal\Service\DealRecurring($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function deal(): Deal\Service\Deal
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Deal\Service\Deal(
                new Deal\Service\Batch($this->batch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function dealUserfield(): Deal\Service\DealUserfield
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Deal\Service\DealUserfield(
                new UserfieldConstraints(),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function contact(): Contact\Service\Contact
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Contact\Service\Contact(
                new Contact\Service\Batch($this->batch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function contactUserfield(): Contact\Service\ContactUserfield
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Contact\Service\ContactUserfield(
                new UserfieldConstraints(),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * @return Deal\Service\DealProductRows
     */
    public function dealProductRows(): Deal\Service\DealProductRows
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Deal\Service\DealProductRows($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function dealCategoryStage(): Deal\Service\DealCategoryStage
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Deal\Service\DealCategoryStage($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function product(): Product\Service\Product
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Product\Service\Product(
                new Product\Service\Batch($this->batch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function userfield(): Userfield\Service\Userfield
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Userfield\Service\Userfield(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function lead(): Lead\Service\Lead
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Lead\Service\Lead(
                new Lead\Service\Batch($this->batch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
    
    public function leadContact(): Lead\Service\LeadContact
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Lead\Service\LeadContact(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * @return Lead\Service\LeadProductRows
     */
    public function leadProductRows(): Lead\Service\LeadProductRows
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Lead\Service\LeadProductRows($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }
  
    public function leadUserfield(): Lead\Service\LeadUserfield
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Lead\Service\LeadUserfield(
                new UserfieldConstraints(),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
    
    public function quote(): Quote\Service\Quote
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Quote\Service\Quote(
                new Quote\Service\Batch($this->batch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
    
    public function quoteContact(): Quote\Service\QuoteContact
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Quote\Service\QuoteContact(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
    
    /**
     * @return Quote\Service\QuoteProductRows
     */
    public function quoteProductRows(): Quote\Service\QuoteProductRows
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Quote\Service\QuoteProductRows($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }
    
    public function quoteUserfield(): Quote\Service\QuoteUserfield
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Quote\Service\QuoteUserfield(
              new UserfieldConstraints(),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function activity(): Activity\Service\Activity
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Activity\Service\Activity(
                new Activity\Service\Batch($this->batch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
    
    public function trigger(): Automation\Service\Trigger
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $batch = new Automation\Batch(
                $this->core,
                $this->log
            );
            $this->serviceCache[__METHOD__] = new Automation\Service\Trigger(
                new Automation\Service\Batch($batch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function activityFetcher(): Activity\ActivityFetcherBuilder
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Activity\ActivityFetcherBuilder(
                $this->core,
                $this->batch,
                $this->bulkItemsReader,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
    
    public function address(): Address\Service\Address
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Address\Service\Address(
                new Address\Service\Batch($this->batch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function item(): Item\Service\Item
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Item\Service\Item(
                new Item\Service\Batch($this->batch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
    
    public function itemProductrow(): Item\Productrow\Service\Productrow
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $batch = new Item\Productrow\Batch(
                $this->core,
                $this->log
            );
            $this->serviceCache[__METHOD__] = new Item\Productrow\Service\Productrow(
                new Item\Productrow\Service\Batch($batch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function duplicate(): Duplicates\Service\Duplicate
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Duplicates\Service\Duplicate(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
}
