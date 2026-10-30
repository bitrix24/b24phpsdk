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

namespace Bitrix24\SDK\Tests\Unit\Services\CRM\V3\FormScenario\Service;

use Bitrix24\SDK\Services\CRM\V3\FormScenario\Service\FormScenario;
use Bitrix24\SDK\Services\CRM\V3\FormScenarioCategory\Service\FormScenarioCategory;
use Bitrix24\SDK\Tests\Unit\Services\CRM\V3\V3ServiceTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(FormScenario::class)]
#[CoversClass(FormScenarioCategory::class)]
class FormScenarioTest extends TestCase
{
    use V3ServiceTestTrait;

    #[Test]
    public function testScenarioListMapsScenarios(): void
    {
        $core = $this->v3Core('crm.form.scenario.list', [], ['items' => [[
            'id' => 'callback', 'title' => 'Callback', 'description' => 'Get a phone number', 'categoryId' => 'crm',
            'iconUrl' => '/bitrix/images/crm/webform/icons/revertcall.svg', 'sections' => ['fields', 'callback'],
            'expertSections' => ['fields'], 'defaultSection' => 'callback', 'openable' => true, 'soon' => false, 'actions' => [['showHelp']],
        ]]]);

        $scenario = (new FormScenario($core, new NullLogger()))->list()->getScenarios()[0];

        $this->assertSame('callback', $scenario->id);
        $this->assertSame('crm', $scenario->categoryId);
        $this->assertSame(['fields', 'callback'], $scenario->sections);
        $this->assertTrue($scenario->openable);
        $this->assertFalse($scenario->soon);
        $this->assertSame([['showHelp']], $scenario->actions);
    }

    #[Test]
    public function testScenarioCategoryListMapsCategories(): void
    {
        $core = $this->v3Core('crm.form.scenariocategory.list', [], ['items' => [['id' => 'crm', 'title' => 'Customer communications']]]);

        $category = (new FormScenarioCategory($core, new NullLogger()))->list()->getScenarioCategories()[0];

        $this->assertSame('crm', $category->id);
        $this->assertSame('Customer communications', $category->title);
    }

    #[Test]
    public function testMetadataTargetsRestV3(): void
    {
        $base = 'https://apidocs.bitrix24.com/api-reference/crm/form/setup/';
        $this->assertRestV3Endpoints(FormScenario::class, ['list' => 'crm.form.scenario.list'], $base);
        $this->assertRestV3Endpoints(FormScenarioCategory::class, ['list' => 'crm.form.scenariocategory.list'], $base);
    }
}
