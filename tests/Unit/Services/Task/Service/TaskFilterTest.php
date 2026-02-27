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

namespace Bitrix24\SDK\Tests\Unit\Services\Task\Service;

use Bitrix24\SDK\Services\Task\Service\TaskFilter;
use DateTime;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TaskFilter::class)]
class TaskFilterTest extends TestCase
{
    #[Test]
    public function testSimpleConditionEquals(): void
    {
        $taskFilter = new TaskFilter();
        $taskFilter->changedDate()->eq(new DateTime('2025-01-01', new \DateTimeZone('UTC')));
        $taskFilter->title()->eq('ASAP');

        $this->assertEquals(
            [
                ['changedDate', '=', '2025-01-01T00:00:00+00:00'],
                ['title', '=', 'ASAP']
            ],
            $taskFilter->toArray()
        );
    }

    #[Test]
    public function testMultipleAndConditions(): void
    {
        $taskFilter = new TaskFilter();
        $taskFilter->title()->eq('ASAP');
        $taskFilter->priority()->eq(2);
        $taskFilter->status()->eq(5);

        $expected = [
            ['title', '=', 'ASAP'],
            ['priority', '=', 2],
            ['status', '=', 5],
        ];

        $this->assertEquals($expected, $taskFilter->toArray());
    }

    #[Test]
    #[DataProvider('operatorsDataProvider')]
    public function testAllOperators(string $operator, string $method, array $expected): void
    {
        $filter = match ($method) {
            'eq', 'neq', 'gt', 'gte', 'lt', 'lte' => (new TaskFilter())->id()->$method(100),
            'in' => (new TaskFilter())->id()->in([1, 2, 3]),
            'between' => (new TaskFilter())->id()->between(1, 100),
            default => throw new \InvalidArgumentException('Unknown method: ' . $method)
        };

        $this->assertEquals([$expected], $filter->toArray());
    }

    public static function operatorsDataProvider(): Generator
    {
        yield 'equals' => ['=', 'eq', ['id', '=', 100]];
        yield 'not equals' => ['!=', 'neq', ['id', '!=', 100]];
        yield 'greater than' => ['>', 'gt', ['id', '>', 100]];
        yield 'greater than or equal' => ['>=', 'gte', ['id', '>=', 100]];
        yield 'less than' => ['<', 'lt', ['id', '<', 100]];
        yield 'less than or equal' => ['<=', 'lte', ['id', '<=', 100]];
        yield 'in' => ['in', 'in', ['id', 'in', [1, 2, 3]]];
        yield 'between' => ['between', 'between', ['id', 'between', [1, 100]]];
    }

    #[Test]
    public function testBetweenOperator(): void
    {
        $filterBuilder = (new TaskFilter())
            ->createdDate()->between('2025-01-01', '2025-12-31');

        $expected = [
            ['createdDate', 'between', ['2025-01-01', '2025-12-31']],
        ];

        $this->assertEquals($expected, $filterBuilder->toArray());
    }

    #[Test]
    public function testOrLogic(): void
    {
        $filter = new TaskFilter();
        $filter->status()->eq(2);
        $filter->or(function (TaskFilter $taskFilter): void {
            $taskFilter->id()->in([1, 2]);
            $taskFilter->priority()->gt(5);
        });

        $expected = [
            ['status', '=', 2],
            [
                'logic' => 'or',
                'conditions' => [
                    ['id', 'in', [1, 2]],
                    ['priority', '>', 5],
                ],
            ],
        ];

        $this->assertEquals($expected, $filter->toArray());
    }

    #[Test]
    public function testMultipleOrGroups(): void
    {
        $filter = new TaskFilter();
        $filter->status()->eq(2);
        $filter->or(function (TaskFilter $taskFilter): void {
            $taskFilter->id()->in([1, 2]);
        });
        $filter->or(function (TaskFilter $taskFilter): void {
            $taskFilter->priority()->eq(5);
        });

        $expected = [
            ['status', '=', 2],
            [
                'logic' => 'or',
                'conditions' => [
                    ['id', 'in', [1, 2]],
                ],
            ],
            [
                'logic' => 'or',
                'conditions' => [
                    ['priority', '=', 5],
                ],
            ],
        ];

        $this->assertEquals($expected, $filter->toArray());
    }

    #[Test]
    public function testUserFieldWithPrefix(): void
    {
        $filterBuilder = (new TaskFilter())
            ->userField('UF_CRM_TASK')->eq('yes');

        $this->assertEquals(
            [['UF_CRM_TASK', '=', 'yes']],
            $filterBuilder->toArray()
        );
    }

    #[Test]
    public function testUserFieldWithoutPrefix(): void
    {
        $filterBuilder = (new TaskFilter())
            ->userField('CRM_TASK')->eq('yes');

        $this->assertEquals(
            [['UF_CRM_TASK', '=', 'yes']],
            $filterBuilder->toArray()
        );
    }

    #[Test]
    public function testUserFieldWithOperators(): void
    {
        $filterBuilder = (new TaskFilter())
            ->userField('UF_MAIL_MESSAGE')->in([100, 200, 300]);

        $this->assertEquals(
            [['UF_MAIL_MESSAGE', 'in', [100, 200, 300]]],
            $filterBuilder->toArray()
        );
    }

    #[Test]
    public function testRawFallback(): void
    {
        $taskFilter = (new TaskFilter())
            ->setRaw([['STAGE_ID', '>=', '100']]);

        $this->assertEquals(
            [['STAGE_ID', '>=', '100']],
            $taskFilter->toArray()
        );
    }

    #[Test]
    public function testRawWithMultipleConditions(): void
    {
        $taskFilter = (new TaskFilter())
            ->setRaw([
                ['STAGE_ID', '>=', '100'],
                ['FLOW_ID', '=', '50'],
            ]);

        $expected = [
            ['STAGE_ID', '>=', '100'],
            ['FLOW_ID', '=', '50'],
        ];

        $this->assertEquals($expected, $taskFilter->toArray());
    }

    #[Test]
    public function testMixedFilterAndRaw(): void
    {
        $taskFilter = new TaskFilter();
        $taskFilter->status()->eq(2);
        $taskFilter->setRaw([['STAGE_ID', '>=', '100']]);

        $expected = [
            ['status', '=', 2],
            ['STAGE_ID', '>=', '100'],
        ];

        $this->assertEquals($expected, $taskFilter->toArray());
    }

    #[Test]
    public function testComplexFilterWithAllFeatures(): void
    {
        $filter = new TaskFilter();
        $filter->title()->eq('Important Task');
        $filter->priority()->gte(2);
        $filter->responsibleId()->in([1, 2, 3]);
        $filter->createdDate()->between('2025-01-01', '2025-12-31');
        $filter->or(function (TaskFilter $taskFilter): void {
            $taskFilter->status()->eq(5);
            $taskFilter->closedDate()->lt('2025-01-01');
        });
        $filter->userField('UF_CRM_TASK')->eq('yes');
        $filter->setRaw([['FLOW_ID', '!=', '0']]);

        $expected = [
            ['title', '=', 'Important Task'],
            ['priority', '>=', 2],
            ['responsibleId', 'in', [1, 2, 3]],
            ['createdDate', 'between', ['2025-01-01', '2025-12-31']],
            ['UF_CRM_TASK', '=', 'yes'],
            ['FLOW_ID', '!=', '0'],
            [
                'logic' => 'or',
                'conditions' => [
                    ['status', '=', 5],
                    ['closedDate', '<', '2025-01-01'],
                ],
            ],
        ];

        $this->assertEquals($expected, $filter->toArray());
    }

    #[Test]
    public function testEmptyFilter(): void
    {
        $taskFilter = new TaskFilter();

        $this->assertEquals([], $taskFilter->toArray());
    }

    #[Test]
    #[DataProvider('allFieldsDataProvider')]
    public function testAllFieldAccessors(string $fieldMethod, string $expectedFieldName, mixed $testValue, mixed $expectedValue): void
    {
        $filter = (new TaskFilter())->$fieldMethod()->eq($testValue);

        $this->assertEquals(
            [[$expectedFieldName, '=', $expectedValue]],
            $filter->toArray()
        );
    }

    public static function allFieldsDataProvider(): Generator
    {
        // Identifiers (int)
        yield 'id' => ['id', 'id', 1, 1];
        yield 'parentId' => ['parentId', 'parentId', 1, 1];
        yield 'groupId' => ['groupId', 'groupId', 1, 1];
        yield 'stageId' => ['stageId', 'stageId', 1, 1];
        yield 'forumTopicId' => ['forumTopicId', 'forumTopicId', 1, 1];
        yield 'sprintId' => ['sprintId', 'sprintId', 1, 1];

        // Text fields (string)
        yield 'title' => ['title', 'title', 'test', 'test'];
        yield 'description' => ['description', 'description', 'test', 'test'];
        yield 'xmlId' => ['xmlId', 'xmlId', 'test', 'test'];
        yield 'guid' => ['guid', 'guid', 'test', 'test'];

        // Status fields (int)
        yield 'status' => ['status', 'status', 1, 1];
        yield 'priority' => ['priority', 'priority', 1, 1];
        yield 'mark' => ['mark', 'mark', 1, 1];

        // People fields (int - user IDs)
        yield 'createdBy' => ['createdBy', 'createdBy', 1, 1];
        yield 'responsibleId' => ['responsibleId', 'responsibleId', 1, 1];
        yield 'changedBy' => ['changedBy', 'changedBy', 1, 1];
        yield 'closedBy' => ['closedBy', 'closedBy', 1, 1];

        // Date fields (DateTime|string)
        yield 'createdDate' => ['createdDate', 'createdDate', '2025-01-01', '2025-01-01'];
        yield 'changedDate' => ['changedDate', 'changedDate', '2025-01-01', '2025-01-01'];
        yield 'closedDate' => ['closedDate', 'closedDate', '2025-01-01', '2025-01-01'];
        yield 'deadline' => ['deadline', 'deadline', '2025-01-01', '2025-01-01'];
        yield 'dateStart' => ['dateStart', 'dateStart', '2025-01-01', '2025-01-01'];
        yield 'startDatePlan' => ['startDatePlan', 'startDatePlan', '2025-01-01', '2025-01-01'];
        yield 'endDatePlan' => ['endDatePlan', 'endDatePlan', '2025-01-01', '2025-01-01'];

        // Boolean fields (bool -> Y/N)
        yield 'multitask' => ['multitask', 'multitask', true, 'Y'];
        yield 'taskControl' => ['taskControl', 'taskControl', true, 'Y'];
        yield 'subordinate' => ['subordinate', 'subordinate', true, 'Y'];
        yield 'favorite' => ['favorite', 'favorite', true, 'Y'];
        yield 'isMuted' => ['isMuted', 'isMuted', true, 'Y'];

        // Number fields (int)
        yield 'timeEstimate' => ['timeEstimate', 'timeEstimate', 1, 1];
        yield 'commentsCount' => ['commentsCount', 'commentsCount', 1, 1];
        yield 'durationPlan' => ['durationPlan', 'durationPlan', 1, 1];
    }

    // Type Safety Tests

    #[Test]
    public function testIntFieldTypeEnforcement(): void
    {
        $taskFilter = new TaskFilter();
        $taskFilter->id()->eq(100);
        $taskFilter->priority()->gte(2);
        $taskFilter->responsibleId()->in([1, 2, 3]);

        $expected = [
            ['id', '=', 100],
            ['priority', '>=', 2],
            ['responsibleId', 'in', [1, 2, 3]],
        ];

        $this->assertEquals($expected, $taskFilter->toArray());
    }

    #[Test]
    public function testIntFieldBetween(): void
    {
        $filterBuilder = (new TaskFilter())
            ->id()->between(1, 100);

        $expected = [
            ['id', 'between', [1, 100]],
        ];

        $this->assertEquals($expected, $filterBuilder->toArray());
    }

    #[Test]
    public function testDateFieldWithDateTime(): void
    {
        $date = new DateTime('2025-01-01', new \DateTimeZone('UTC'));
        $filterBuilder = (new TaskFilter())
            ->changedDate()->eq($date);

        $expected = [
            ['changedDate', '=', '2025-01-01T00:00:00+00:00'],
        ];

        $this->assertEquals($expected, $filterBuilder->toArray());
    }

    #[Test]
    public function testDateFieldWithString(): void
    {
        $filterBuilder = (new TaskFilter())
            ->createdDate()->eq('2025-01-01');

        $expected = [
            ['createdDate', '=', '2025-01-01'],
        ];

        $this->assertEquals($expected, $filterBuilder->toArray());
    }

    #[Test]
    public function testDateFieldBetweenWithDateTime(): void
    {
        $filterBuilder = (new TaskFilter())
            ->createdDate()->between(
                new DateTime('2025-01-01', new \DateTimeZone('UTC')),
                new DateTime('2025-12-31', new \DateTimeZone('UTC'))
            );

        $expected = [
            ['createdDate', 'between', ['2025-01-01T00:00:00+00:00', '2025-12-31T00:00:00+00:00']],
        ];

        $this->assertEquals($expected, $filterBuilder->toArray());
    }

    #[Test]
    public function testDateFieldComparisonOperators(): void
    {
        $taskFilter = new TaskFilter();
        $taskFilter->deadline()->gt(new DateTime('2025-01-01', new \DateTimeZone('UTC')));
        $taskFilter->closedDate()->lt('2025-12-31');

        $expected = [
            ['deadline', '>', '2025-01-01T00:00:00+00:00'],
            ['closedDate', '<', '2025-12-31'],
        ];

        $this->assertEquals($expected, $taskFilter->toArray());
    }

    #[Test]
    public function testBoolFieldConversionTrue(): void
    {
        $taskFilter = new TaskFilter();
        $taskFilter->multitask()->eq(true);
        $taskFilter->favorite()->eq(true);

        $expected = [
            ['multitask', '=', 'Y'],
            ['favorite', '=', 'Y'],
        ];

        $this->assertEquals($expected, $taskFilter->toArray());
    }

    #[Test]
    public function testBoolFieldConversionFalse(): void
    {
        $taskFilter = new TaskFilter();
        $taskFilter->multitask()->eq(false);
        $taskFilter->favorite()->neq(false);

        $expected = [
            ['multitask', '=', 'N'],
            ['favorite', '!=', 'N'],
        ];

        $this->assertEquals($expected, $taskFilter->toArray());
    }

    #[Test]
    public function testStringFieldOperators(): void
    {
        $taskFilter = new TaskFilter();
        $taskFilter->title()->eq('Task Title');
        $taskFilter->description()->neq('Old Description');
        $taskFilter->guid()->in(['guid-1', 'guid-2', 'guid-3']);

        $expected = [
            ['title', '=', 'Task Title'],
            ['description', '!=', 'Old Description'],
            ['guid', 'in', ['guid-1', 'guid-2', 'guid-3']],
        ];

        $this->assertEquals($expected, $taskFilter->toArray());
    }

    #[Test]
    public function testMixedTypedFields(): void
    {
        $taskFilter = new TaskFilter();
        $taskFilter->id()->eq(100);
        $taskFilter->title()->eq('ASAP');
        $taskFilter->changedDate()->eq(new DateTime('2025-01-01', new \DateTimeZone('UTC')));
        $taskFilter->favorite()->eq(true);
        $taskFilter->priority()->between(1, 5);

        $expected = [
            ['id', '=', 100],
            ['title', '=', 'ASAP'],
            ['changedDate', '=', '2025-01-01T00:00:00+00:00'],
            ['favorite', '=', 'Y'],
            ['priority', 'between', [1, 5]],
        ];

        $this->assertEquals($expected, $taskFilter->toArray());
    }
}
