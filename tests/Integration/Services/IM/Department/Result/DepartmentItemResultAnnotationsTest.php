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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Department\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Department\Result\DepartmentItemResult;
use Bitrix24\SDK\Services\IM\Department\Service\Department;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(DepartmentItemResult::class)]
final class DepartmentItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Department $departmentService;

    #[\Override]
    protected function setUp(): void
    {
        $this->departmentService = Factory::getServiceBuilder()->getIMScope()->department();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in DepartmentItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawResult = $this->departmentService->get([1], true)
            ->getCoreResponse()
            ->getResponseData()
            ->getResult();

        if ($rawResult === []) {
            $this->markTestSkipped('No department payload available to validate annotations');
        }

        $firstItem = reset($rawResult);
        if (!is_array($firstItem)) {
            $this->markTestSkipped('Unexpected response shape for im.department.get');
        }

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($firstItem),
            DepartmentItemResult::class
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in DepartmentItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $items = $this->departmentService->get([1], true)->items();

        if ($items === []) {
            $this->markTestSkipped('No department payload available to validate type casting');
        }

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $items[0],
            DepartmentItemResult::class
        );
    }
}
