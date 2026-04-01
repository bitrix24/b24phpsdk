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

namespace Bitrix24\SDK\Tests\Integration\Services\Task\Result;

use Bitrix24\SDK\Core\Fields\FieldsFilter;
use Bitrix24\SDK\Services\Task\Result\TaskItemResult;
use Bitrix24\SDK\Services\Task\TaskField\Result\TaskFieldItemResult;
use Bitrix24\SDK\Services\Task\TaskField\Service\TaskField;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversClass(TaskItemResult::class)]
#[CoversMethod(TaskField::class, 'list')]
class TaskItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private const array OBJECT_FIELDS_EXPOSED_AS_ARRAYS = [
        'creator',
        'responsible',
        'group',
        'stage',
        'flow',
        'accomplices',
        'auditors',
        'parent',
        'chat',
        'changedBy',
        'statusChangedBy',
        'closedBy',
        'forkedByTemplate',
        'tags',
        'userFields',
        'elapsedTime',
        'email',
        'source',
    ];

    private const array OBJECT_FIELDS_EXPOSED_AS_STRINGS = [
        'created',
        'deadline',
        'startPlan',
        'endPlan',
        'statusChanged',
        'started',
        'changed',
        'closed',
        'activity',
        'maxDeadlineChangeDate',
    ];

    protected TaskField $taskFieldService;

    #[\Override]
    protected function setUp(): void
    {
        $this->taskFieldService = Factory::getServiceBuilder()->getTaskScope()->taskField();
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $fields = $this->getTaskFieldsMetadata();
        $propListFromApi = (new FieldsFilter())->filterSystemFields(array_keys($fields));

        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, TaskItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $allFields = $this->getTaskFieldsMetadata();
        $systemFieldCodes = (new FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter(
            $allFields,
            static fn(string $code): bool => in_array($code, $systemFieldCodes, true),
            ARRAY_FILTER_USE_KEY
        );

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation($systemFields, TaskItemResult::class);
    }

    /**
     * @return array<string, array{type: string}>
     */
    private function getTaskFieldsMetadata(): array
    {
        $result = [];

        foreach ($this->taskFieldService->list(['name', 'type'])->getTaskFields() as $taskFieldItemResult) {
            if (!$taskFieldItemResult instanceof TaskFieldItemResult) {
                continue;
            }

            if ($taskFieldItemResult->name === null) {
                continue;
            }

            if ($taskFieldItemResult->type === null) {
                continue;
            }

            $result[$taskFieldItemResult->name] = [
                'type' => $this->normalizeFieldType((string)$taskFieldItemResult->name, (string)$taskFieldItemResult->type),
            ];
        }

        return $result;
    }

    private function normalizeFieldType(string $fieldName, string $fieldType): string
    {
        if (in_array($fieldName, self::OBJECT_FIELDS_EXPOSED_AS_ARRAYS, true)) {
            return 'array';
        }

        if (in_array($fieldName, self::OBJECT_FIELDS_EXPOSED_AS_STRINGS, true)) {
            return 'string';
        }

        if ($fieldType === 'bool') {
            return 'char';
        }

        return $fieldType;
    }
}
