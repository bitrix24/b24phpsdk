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

namespace Bitrix24\SDK\Tests\Integration\Services\Task\Service;

use Bitrix24\SDK\Services\Task\Service\Task;
use Bitrix24\SDK\Services\Task\TaskField\Service\TaskField;
use Bitrix24\SDK\Services\Task\Service\TaskItemBuilder;
use Bitrix24\SDK\Services\User\Service\User;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Throwable;

#[CoversMethod(Task::class, 'get')]
#[CoversMethod(TaskField::class, 'list')]
#[CoversClass(Task::class)]
class TaskGetSelectFieldsCoverageTest extends TestCase
{
    private Task $taskService;
    private TaskField $taskFieldService;
    private User $userService;

    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Factory::getServiceBuilder(false);

        $this->taskService = $serviceBuilder->getTaskScope()->task();
        $this->taskFieldService = $serviceBuilder->getTaskScope()->taskField();
        $this->userService = $serviceBuilder->getUserScope()->user();
    }

    #[TestDox('Task get returns selected keys for every field from task field metadata')]
    public function testGetTaskReturnsSelectedKeysForEveryFieldFromMetadata(): void
    {
        $userItemResult = $this->userService->current()->user();
        $taskId = $this->taskService->add(
            new TaskItemBuilder(
                sprintf('Task get select coverage %s', time()),
                $userItemResult->ID,
                $userItemResult->ID
            )
        )->task()->id;

        try {
            $taskFields = $this->taskFieldService->list(['name'])->getTaskFields();
            $errors = [];

            foreach ($taskFields as $taskField) {
                $fieldName = $taskField->name;
                if (!is_string($fieldName) || $fieldName === '') {
                    $errors[] = 'TaskField.list returned a field without a valid name';

                    continue;
                }

                $select = $fieldName === 'id' ? ['id'] : ['id', $fieldName];

                try {
                    $responseItem = $this->taskService
                        ->get($taskId, $select)
                        ->getCoreResponse()
                        ->getResponseData()
                        ->getResult()['item'] ?? null;
                } catch (Throwable $exception) {
                    $errors[] = sprintf(
                        'field "%s": request failed for select [%s] with error "%s"',
                        $fieldName,
                        implode(', ', $select),
                        $exception->getMessage()
                    );

                    continue;
                }

                if (!is_array($responseItem)) {
                    $errors[] = sprintf(
                        'field "%s": response item payload is not an array for select [%s]',
                        $fieldName,
                        implode(', ', $select)
                    );

                    continue;
                }

                $missingKeys = array_values(array_filter(
                    $select,
                    static fn (string $selectedField): bool => !array_key_exists($selectedField, $responseItem)
                ));

                if ($missingKeys !== []) {
                    $errors[] = sprintf(
                        'field "%s": requested [%s], missing keys [%s], response keys [%s]',
                        $fieldName,
                        implode(', ', $select),
                        implode(', ', $missingKeys),
                        implode(', ', array_keys($responseItem))
                    );
                }
            }

            self::assertSame(
                [],
                $errors,
                "Task::get did not return some selected fields:\n" . implode("\n", $errors)
            );
        } finally {
            $this->taskService->delete($taskId);
        }
    }
}
