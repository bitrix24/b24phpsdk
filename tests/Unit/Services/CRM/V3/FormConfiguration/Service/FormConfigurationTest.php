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

namespace Bitrix24\SDK\Tests\Unit\Services\CRM\V3\FormConfiguration\Service;

use Bitrix24\SDK\Services\CRM\V3\FormConfiguration\Result\ConfigurationEntitySchemeItemResult;
use Bitrix24\SDK\Services\CRM\V3\FormConfiguration\Result\ConfigurationSmartProcessItemResult;
use Bitrix24\SDK\Services\CRM\V3\FormConfiguration\Result\NamedCodeItemResult;
use Bitrix24\SDK\Services\CRM\V3\FormConfiguration\Result\NamedIdItemResult;
use Bitrix24\SDK\Services\CRM\V3\FormConfiguration\Service\FormConfiguration;
use Bitrix24\SDK\Tests\Unit\Services\CRM\V3\V3ServiceTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(FormConfiguration::class)]
class FormConfigurationTest extends TestCase
{
    use V3ServiceTestTrait;

    #[Test]
    public function testGetMapsAllDirectoryBlocks(): void
    {
        $core = $this->v3Core('crm.form.configuration.get', [], ['item' => [
            'entitySchemes' => [['id' => 1, 'name' => 'Lead', 'description' => 'lead', 'entityNames' => ['LEAD', 'COMPANY', 'CONTACT'], 'mainEntityName' => 'LEAD', 'hasInvoice' => false, 'dynamic' => false]],
            'duplicateModes' => [['id' => 'MERGE', 'name' => 'Merge']],
            'lead' => ['enabled' => true],
            'dealCategories' => [['id' => 664, 'name' => 'Sales']],
            'smartProcesses' => [['entityTypeId' => 1032, 'entityName' => 'DYNAMIC_1032', 'name' => 'Smart', 'categories' => [['id' => 14, 'name' => 'General']]]],
            'languages' => [['id' => 'en', 'name' => 'English']],
            'currencies' => [['id' => 'RUB', 'fullName' => 'Russian ruble', 'formatString' => '# &#8381;']],
            'fieldTypes' => [['id' => 'section', 'name' => 'Section', 'parameters' => ['label']]],
            'paySystems' => [['id' => 2, 'name' => 'Bank transfer']],
            'permissions' => ['canRead' => true, 'canEdit' => false],
            'limits' => ['activeForms' => ['current' => 2, 'maximum' => 99999]],
            'product' => ['isCloud' => true, 'isRegionRussian' => true],
            'region' => 'ru',
        ]]);

        $configuration = (new FormConfiguration($core, new NullLogger()))->get()->configuration();

        $this->assertInstanceOf(ConfigurationEntitySchemeItemResult::class, $configuration->entitySchemes[0]);
        $this->assertSame(1, $configuration->entitySchemes[0]->id);
        $this->assertSame(['LEAD', 'COMPANY', 'CONTACT'], $configuration->entitySchemes[0]->entityNames);
        $this->assertInstanceOf(NamedCodeItemResult::class, $configuration->duplicateModes[0]);
        $this->assertSame('MERGE', $configuration->duplicateModes[0]->id);
        $this->assertTrue($configuration->lead?->enabled);
        $this->assertInstanceOf(NamedIdItemResult::class, $configuration->dealCategories[0]);
        $this->assertSame(664, $configuration->dealCategories[0]->id);
        $this->assertInstanceOf(ConfigurationSmartProcessItemResult::class, $configuration->smartProcesses[0]);
        $this->assertSame(14, $configuration->smartProcesses[0]->categories[0]->id);
        $this->assertSame('en', $configuration->languages[0]->id);
        $this->assertSame('RUB', $configuration->currencies[0]->id);
        $this->assertSame(['label'], $configuration->fieldTypes[0]->parameters);
        $this->assertSame(2, $configuration->paySystems[0]->id);
        $this->assertFalse($configuration->permissions?->canEdit);
        $this->assertSame(2, $configuration->limits?->activeForms?->current);
        $this->assertSame(99999, $configuration->limits->activeForms->maximum);
        $this->assertTrue($configuration->product?->isCloud);
        $this->assertSame('ru', $configuration->region);
    }

    #[Test]
    public function testMetadataTargetsRestV3(): void
    {
        $this->assertRestV3Endpoints(FormConfiguration::class, ['get' => 'crm.form.configuration.get'], 'https://apidocs.bitrix24.com/api-reference/crm/form/setup/');
    }
}
