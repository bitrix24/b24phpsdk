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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\V3\FormScenario\Service;

use Bitrix24\SDK\Services\CRM\V3\FormScenario\Service\FormScenario;
use Bitrix24\SDK\Services\CRM\V3\FormScenarioCategory\Service\FormScenarioCategory;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FormScenario::class)]
#[CoversClass(FormScenarioCategory::class)]
class FormScenarioTest extends TestCase
{
    #[Test]
    public function testEveryScenarioBelongsToAListedCategory(): void
    {
        $crm = Factory::getServiceBuilder()->getCRMScope()->v3();
        $categoryIds = array_map(static fn ($category): ?string => $category->id, $crm->formScenarioCategory()->list()->getScenarioCategories());
        $scenarios = $crm->formScenario()->list()->getScenarios();

        $this->assertNotEmpty($categoryIds);
        $this->assertNotEmpty($scenarios);
        foreach ($scenarios as $scenario) {
            $this->assertContains($scenario->categoryId, $categoryIds, sprintf('scenario «%s»', $scenario->id));
        }
    }
}
