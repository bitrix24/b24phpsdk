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

namespace Bitrix24\SDK\Tests\Integration\Legacy\Services\Task\Result;

use Bitrix24\SDK\Core\Fields\FieldsFilter;
use Bitrix24\SDK\Legacy\Services\Task\Service\Task;
use Bitrix24\SDK\Services\Task\Result\TaskItemResult;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversClass(TaskItemResult::class)]
#[CoversMethod(Task::class, 'fields')]
class TaskItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    protected Task $taskService;

    #[\Override]
    protected function setUp(): void
    {
        $this->taskService = Factory::getServiceBuilder()->getLegacyServiceBuilder()->getTaskScope()->task();
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $fields = $this->normalizeFieldKeys($this->taskService->fields()->getFieldsDescription());
        $propListFromApi = (new FieldsFilter())->filterSystemFields(array_keys($fields));

        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, TaskItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $allFields = $this->normalizeFieldKeys($this->taskService->fields()->getFieldsDescription());
        $systemFieldCodes = (new FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter(
            $allFields,
            static fn(string $code): bool => in_array($code, $systemFieldCodes, true),
            ARRAY_FILTER_USE_KEY
        );

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation($systemFields, TaskItemResult::class);
    }

    /**
     * @param array<string, mixed> $fields
     *
     * @return array<string, mixed>
     */
    private function normalizeFieldKeys(array $fields): array
    {
        $result = [];

        foreach ($fields as $key => $value) {
            if (str_starts_with($key, 'UF_') && !in_array($key, ['UF_CRM_TASK', 'UF_TASK_WEBDAV_FILES', 'UF_MAIL_MESSAGE'], true)) {
                continue;
            }

            $normalizedFieldNameParts = explode('_', strtolower($key));
            $normalizedFieldName = array_shift($normalizedFieldNameParts)
                . implode('', array_map('ucfirst', $normalizedFieldNameParts));

            $result[$normalizedFieldName] = $value;
        }

        return $result;
    }
}
