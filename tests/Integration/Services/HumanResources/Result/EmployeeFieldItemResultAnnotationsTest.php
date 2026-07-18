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

namespace Bitrix24\SDK\Tests\Integration\Services\HumanResources\Result;

use Bitrix24\SDK\Services\HumanResources\EmployeeField\Result\EmployeeFieldItemResult;
use Bitrix24\SDK\Services\HumanResources\EmployeeField\Service\EmployeeField;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(EmployeeFieldItemResult::class)]
class EmployeeFieldItemResultAnnotationsTest extends AbstractHumanResourcesAnnotations
{
    private EmployeeField $employeeFieldService;

    #[\Override]
    protected function setUp(): void
    {
        $this->employeeFieldService = Factory::getServiceBuilder()->getHumanResourcesScope()->employeeField();
    }

    #[Test]
    #[TestDox('all system fields in EmployeeFieldItemResult are annotated')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawItem = $this->callOrSkipIfHumanResourcesUnavailable(fn(): array => $this->getRawFieldItem());

        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItem), EmployeeFieldItemResult::class);
    }

    #[Test]
    #[TestDox('all system fields in EmployeeFieldItemResult have valid type annotations')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $fieldItem = $this->callOrSkipIfHumanResourcesUnavailable(
            fn(): EmployeeFieldItemResult => $this->employeeFieldService->list()->getEmployeeFields()[0]
        );

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($fieldItem, EmployeeFieldItemResult::class);
    }

    /**
     * @return array<string, mixed>
     */
    private function getRawFieldItem(): array
    {
        $result = $this->employeeFieldService->list()->getCoreResponse()->getResponseData()->getResult();
        $items = $result['items'] ?? $result;

        return $items[0];
    }
}
