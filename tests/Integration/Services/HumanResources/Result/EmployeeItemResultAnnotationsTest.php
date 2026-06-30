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
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(EmployeeItemResult::class)]
class EmployeeItemResultAnnotationsTest extends AbstractHumanResourcesAnnotations
{
    private EmployeeField $employeeFieldService;

    #[\Override]
    protected function setUp(): void
    {
        $this->employeeFieldService = Factory::getServiceBuilder()->getHumanResourcesScope()->employeeField();
    }

    #[Test]
    #[TestDox('all system fields in EmployeeItemResult are annotated')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $fields = $this->callOrSkipIfHumanResourcesUnavailable(
            fn(): array => $this->employeeFieldService->list()->getFieldsDescription()
        );

        $this->assertHumanResourcesFieldsAnnotated($fields, EmployeeItemResult::class);
    }

    #[Test]
    #[TestDox('all system fields in EmployeeItemResult have valid type annotations')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $fields = $this->callOrSkipIfHumanResourcesUnavailable(
            fn(): array => $this->employeeFieldService->list()->getFieldsDescription()
        );

        $this->assertHumanResourcesFieldsHaveValidTypeAnnotations($fields, EmployeeItemResult::class);
    }
}
