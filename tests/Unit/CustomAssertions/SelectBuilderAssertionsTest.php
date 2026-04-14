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

namespace Bitrix24\SDK\Tests\Unit\CustomAssertions;

use Bitrix24\SDK\Attributes\OpenApiEntity;
use Bitrix24\SDK\Services\AbstractSelectBuilder;
use Bitrix24\SDK\Tests\CustomAssertions\SelectBuilderAssertions;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(SelectBuilderAssertions::class)]
class SelectBuilderAssertionsTest extends TestCase
{
    #[Test]
    #[TestDox('passes when builder covers all schema fields declared in #[OpenApiEntity]')]
    public function testPassesWhenBuilderCoversAllSchemaFields(): void
    {
        // Uses real TaskItemSelectBuilder + TaskItemResult which carry
        // a real #[OpenApiEntity] and real OA schema — a full smoke test.
        SelectBuilderAssertions::assertCoversOpenApiSchema(
            new \Bitrix24\SDK\Services\Task\Service\TaskItemSelectBuilder(),
            \Bitrix24\SDK\Services\Task\Result\TaskItemResult::class
        );
    }

    #[Test]
    #[TestDox('fails when result class has no #[OpenApiEntity] attribute')]
    public function testFailsWhenNoOpenApiEntityAttribute(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessageMatches('/has no #\[OpenApiEntity\] attribute/');

        $classWithNoAttr = new class([]) extends \Bitrix24\SDK\Core\Result\AbstractItem {};

        SelectBuilderAssertions::assertCoversOpenApiSchema(
            new \Bitrix24\SDK\Services\Task\Service\TaskItemSelectBuilder(),
            $classWithNoAttr::class
        );
    }

    #[Test]
    #[TestDox('fails when builder does not cover a field from the schema')]
    public function testFailsWhenBuilderMissesAField(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessageMatches('/is not covered by/');

        // Anonymous class with #[OpenApiEntity] pointing to a real entity.
        // The builder returns only 'id' — will be missing all other fields.
        $resultClass = new
        #[OpenApiEntity('bitrix.tasks.taskdto')]
        class([]) extends \Bitrix24\SDK\Core\Result\AbstractItem {};

        $emptyBuilder = new class extends AbstractSelectBuilder {
            public function __construct() {
                $this->select[] = 'id';
            }
        };

        SelectBuilderAssertions::assertCoversOpenApiSchema($emptyBuilder, $resultClass::class);
    }
}
