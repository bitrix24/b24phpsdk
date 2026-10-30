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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\V3\FormScenarioCategory\Result;

use Bitrix24\SDK\Services\CRM\V3\CRMV3ServiceBuilder;
use Bitrix24\SDK\Services\CRM\V3\FormScenarioCategory\Result\ScenarioCategoryItemResult;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use Bitrix24\SDK\Tests\Integration\Services\CRM\V3\NestedItemAnnotationsTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ScenarioCategoryItemResult::class)]
class ScenarioCategoryItemResultAnnotationsTest extends TestCase
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
        $fields = $this->crm->formScenarioCategoryField()->list()->getFieldsDescription();
        self::assertNotEmpty($fields);
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($fields), ScenarioCategoryItemResult::class);
    }

    #[Test]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $fields = $this->crm->formScenarioCategoryField()->list()->getFieldsDescription();
        self::assertNotEmpty($fields);
        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation($fields, ScenarioCategoryItemResult::class);
    }

    #[Test]
    public function testAllSystemFieldsHasValidTypeCasting(): void
    {
        $result = $this->crm->formScenarioCategory()->list();
        $this->assertNestedItems($result->getCoreResponse()->getResponseData()->getResult()['items'], $result->getScenarioCategories(), ScenarioCategoryItemResult::class);
    }
}
