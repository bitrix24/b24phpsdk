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

use Bitrix24\SDK\Services\HumanResources\EmployeeField\Service\EmployeeField;
use Bitrix24\SDK\Services\HumanResources\Result\EmployeeItemResult;
use Bitrix24\SDK\Services\HumanResources\Result\NodeItemResult;
use Bitrix24\SDK\Services\HumanResources\Service\Employee;
use Bitrix24\SDK\Services\HumanResources\Service\Node;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(EmployeeItemResult::class)]
class EmployeeItemResultAnnotationsTest extends AbstractHumanResourcesAnnotations
{
    private Employee $employeeService;

    private EmployeeField $employeeFieldService;

    private Node $nodeService;

    #[\Override]
    protected function setUp(): void
    {
        $humanResourcesScope = Factory::getServiceBuilder()->getHumanResourcesScope();
        $this->employeeService = $humanResourcesScope->employee();
        $this->employeeFieldService = $humanResourcesScope->employeeField();
        $this->nodeService = $humanResourcesScope->node();
    }

    #[Test]
    #[TestDox('all system fields in EmployeeItemResult are annotated')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $fields = $this->callOrSkipIfHumanResourcesUnavailable(
            fn(): array => $this->employeeFieldService->list()->getFieldsDescription()
        );

        $this->assertHumanResourcesFieldsAnnotated($fields, EmployeeItemResult::class);

        $rawItem = $this->callOrSkipIfHumanResourcesUnavailable(fn(): array => $this->getSampleEmployeeRawItem());
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItem), EmployeeItemResult::class);
    }

    #[Test]
    #[TestDox('all system fields in EmployeeItemResult have valid type annotations')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $fields = $this->callOrSkipIfHumanResourcesUnavailable(
            fn(): array => $this->employeeFieldService->list()->getFieldsDescription()
        );

        $this->assertHumanResourcesFieldsHaveValidTypeAnnotations($fields, EmployeeItemResult::class);

        $employeeItemResult = $this->callOrSkipIfHumanResourcesUnavailable(
            fn(): EmployeeItemResult => $this->getSampleEmployeeItem()
        );
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($employeeItemResult, EmployeeItemResult::class);
    }

    /**
     * @return array<string, mixed>
     */
    private function getSampleEmployeeRawItem(): array
    {
        $employeeSearchResult = $this->employeeService->search(
            $this->getSampleEmployeeName(),
            null,
            array_keys($this->employeeFieldService->list()->getFieldsDescription())
        );

        $items = $employeeSearchResult->getCoreResponse()->getResponseData()->getResult()['items'] ?? [];
        if ($items === []) {
            self::markTestSkipped('No humanresources employee.search items available to validate annotations.');
        }

        return $items[0];
    }

    private function getSampleEmployeeItem(): EmployeeItemResult
    {
        $employees = $this->employeeService->search(
            $this->getSampleEmployeeName(),
            null,
            array_keys($this->employeeFieldService->list()->getFieldsDescription())
        )->getEmployees();

        if ($employees === []) {
            self::markTestSkipped('No humanresources employee.search items available to validate type casting.');
        }

        return $employees[0];
    }

    private function getSampleEmployeeName(): string
    {
        $node = $this->nodeService->list('DEPARTMENT', ['id'], ['limit' => 10])->getNodes()[0] ?? null;
        if (!$node instanceof NodeItemResult) {
            self::markTestSkipped('No humanresources nodes available to resolve a sample employee name.');
        }

        $nodeResult = $this->nodeService->get((int)$node->id, ['members']);
        $rawNode = $nodeResult->getCoreResponse()->getResponseData()->getResult()['item'] ?? [];
        $memberName = $rawNode['members'][0]['name'] ?? null;
        if (!is_string($memberName) || $memberName === '') {
            self::markTestSkipped('No humanresources node members available to resolve a sample employee name.');
        }

        return $memberName;
    }
}
