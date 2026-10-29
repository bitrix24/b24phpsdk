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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\V3\FormEntityField\Result;

use Bitrix24\SDK\Services\CRM\V3\CRMV3ServiceBuilder;
use Bitrix24\SDK\Services\CRM\V3\FormEntityField\Result\EntityFieldItemResult;
use Bitrix24\SDK\Services\CRM\V3\FormEntityField\Result\EntityFieldOptionItemResult;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use Bitrix24\SDK\Tests\Integration\Services\CRM\V3\NestedItemAnnotationsTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(EntityFieldItemResult::class)]
class EntityFieldItemResultAnnotationsTest extends TestCase
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
        $fields = $this->crm->formEntityFieldField()->list()->getFieldsDescription();
        self::assertNotEmpty($fields);
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($fields), EntityFieldItemResult::class);
    }

    #[Test]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $fields = $this->crm->formEntityFieldField()->list()->getFieldsDescription();
        self::assertNotEmpty($fields);
        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $this->withoutNestedObjectFields($fields, EntityFieldItemResult::class),
            EntityFieldItemResult::class
        );
    }

    #[Test]
    public function testItemsAndOptionsAreCast(): void
    {
        $result = $this->crm->formEntityField()->list(['LEAD']);
        $raw = $result->getCoreResponse()->getResponseData()->getResult()['items'];
        $fields = $result->getEntityFields();
        $this->assertNestedItems($raw, $fields, EntityFieldItemResult::class);

        $rawOptions = [];
        $options = [];
        foreach ($raw as $index => $field) {
            $rawOptions = [...$rawOptions, ...$field['items']];
            $options = [...$options, ...($fields[$index]->items ?? [])];
        }

        $this->assertNestedItems($rawOptions, $options, EntityFieldOptionItemResult::class);
    }
}
