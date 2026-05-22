<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Timeman\Result;

use Bitrix24\SDK\Services\Timeman\Result\WorkdayItemResult;
use Bitrix24\SDK\Services\Timeman\Service\Timeman;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(WorkdayItemResult::class)]
class WorkdayItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Timeman $timemanService;

    #[\Override]
    protected function setUp(): void
    {
        $this->timemanService = Factory::getServiceBuilder()->getTimemanScope()->timeman();
    }

    #[Test]
    #[TestDox('all fields in WorkdayItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawResult = $this->timemanService->status()->getCoreResponse()
            ->getResponseData()->getResult();

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawResult),
            WorkdayItemResult::class
        );
    }

    #[Test]
    #[TestDox('all fields in WorkdayItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $workdayItemResult = $this->timemanService->status()->getWorkday();

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $workdayItemResult,
            WorkdayItemResult::class
        );
    }
}

