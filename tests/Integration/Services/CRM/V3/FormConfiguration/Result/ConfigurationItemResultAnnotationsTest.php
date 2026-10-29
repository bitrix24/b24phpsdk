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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\V3\FormConfiguration\Result;

use Bitrix24\SDK\Services\CRM\V3\CRMV3ServiceBuilder;
use Bitrix24\SDK\Services\CRM\V3\FormConfiguration\Result\ConfigurationActiveFormsLimitItemResult;
use Bitrix24\SDK\Services\CRM\V3\FormConfiguration\Result\ConfigurationCurrencyItemResult;
use Bitrix24\SDK\Services\CRM\V3\FormConfiguration\Result\ConfigurationEntitySchemeItemResult;
use Bitrix24\SDK\Services\CRM\V3\FormConfiguration\Result\ConfigurationFieldTypeItemResult;
use Bitrix24\SDK\Services\CRM\V3\FormConfiguration\Result\ConfigurationItemResult;
use Bitrix24\SDK\Services\CRM\V3\FormConfiguration\Result\ConfigurationLeadItemResult;
use Bitrix24\SDK\Services\CRM\V3\FormConfiguration\Result\ConfigurationLimitsItemResult;
use Bitrix24\SDK\Services\CRM\V3\FormConfiguration\Result\ConfigurationPermissionsItemResult;
use Bitrix24\SDK\Services\CRM\V3\FormConfiguration\Result\ConfigurationProductItemResult;
use Bitrix24\SDK\Services\CRM\V3\FormConfiguration\Result\ConfigurationSmartProcessItemResult;
use Bitrix24\SDK\Services\CRM\V3\FormConfiguration\Result\NamedCodeItemResult;
use Bitrix24\SDK\Services\CRM\V3\FormConfiguration\Result\NamedIdItemResult;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use Bitrix24\SDK\Tests\Integration\Services\CRM\V3\NestedItemAnnotationsTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfigurationItemResult::class)]
class ConfigurationItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;
    use NestedItemAnnotationsTrait;

    private CRMV3ServiceBuilder $crm;

    #[\Override]
    protected function setUp(): void
    {
        $this->crm = Factory::getServiceBuilder()->getCRMScope()->v3();
    }

    #[Test]
    public function testAllSystemFieldsAnnotated(): void
    {
        $fields = $this->crm->formConfigurationField()->list()->getFieldsDescription();
        self::assertNotEmpty($fields);
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($fields), ConfigurationItemResult::class);
    }

    #[Test]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $fields = $this->crm->formConfigurationField()->list()->getFieldsDescription();
        self::assertNotEmpty($fields);
        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $this->withoutNestedObjectFields($fields, ConfigurationItemResult::class),
            ConfigurationItemResult::class
        );
    }

    #[Test]
    public function testNestedDirectoriesAreAnnotatedAndCast(): void
    {
        $result = $this->crm->formConfiguration()->get();
        $raw = $result->getCoreResponse()->getResponseData()->getResult()['item'];
        $configuration = $result->configuration();
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($configuration, ConfigurationItemResult::class);

        $this->assertNestedItems($raw['entitySchemes'], $configuration->entitySchemes, ConfigurationEntitySchemeItemResult::class);
        $this->assertNestedItems([...$raw['duplicateModes'], ...$raw['languages']], [...$configuration->duplicateModes, ...$configuration->languages], NamedCodeItemResult::class);
        $this->assertNestedItems([...$raw['dealCategories'], ...$raw['paySystems']], [...$configuration->dealCategories, ...$configuration->paySystems], NamedIdItemResult::class);
        $this->assertNestedItems($raw['currencies'], $configuration->currencies, ConfigurationCurrencyItemResult::class);
        $this->assertNestedItems($raw['fieldTypes'], $configuration->fieldTypes, ConfigurationFieldTypeItemResult::class);
        $this->assertNestedItems([$raw['lead']], [$configuration->lead], ConfigurationLeadItemResult::class);
        $this->assertNestedItems([$raw['permissions']], [$configuration->permissions], ConfigurationPermissionsItemResult::class);
        $this->assertNestedItems([$raw['limits']], [$configuration->limits], ConfigurationLimitsItemResult::class);
        $this->assertNestedItems([$raw['limits']['activeForms']], [$configuration->limits?->activeForms], ConfigurationActiveFormsLimitItemResult::class);
        $this->assertNestedItems([$raw['product']], [$configuration->product], ConfigurationProductItemResult::class);

        if ($raw['smartProcesses'] === []) {
            $this->markTestIncomplete('test portal has no smart processes to check ConfigurationSmartProcessItemResult');
        }

        $this->assertNestedItems($raw['smartProcesses'], $configuration->smartProcesses, ConfigurationSmartProcessItemResult::class);
    }
}
