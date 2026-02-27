<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Task\Service;

use Bitrix24\SDK\Infrastructure\Filesystem\Base64Encoder;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Services\Task\Service\Task;
use Bitrix24\SDK\Services\Task\Service\TaskFile;
use Bitrix24\SDK\Services\Task\Service\TaskItemBuilder;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Filesystem\Filesystem;

#[CoversMethod(TaskFile::class, 'attachExists')]
class TaskFileTest extends TestCase
{
    use CustomBitrix24Assertions;

    protected Task $taskService;

    protected TaskFile $taskFileService;

    protected ServiceBuilder $serviceBuilder;

    protected Base64Encoder $base64Encoder;

    #[\Override]
    protected function setUp(): void
    {
        $this->taskService = Factory::getServiceBuilder(false)->getTaskScope()->task();
        $this->taskFileService = Factory::getServiceBuilder(false)->getTaskScope()->taskFile();
        $this->serviceBuilder = Factory::getServiceBuilder();
        $this->base64Encoder = new Base64Encoder(
            new Filesystem(),
            new \Symfony\Component\Mime\Encoder\Base64Encoder(),
            new NullLogger()
        );
    }

    #[TestDox('Upload existing file to task')]
    public function uploadExistingFileToTask(): void
    {
        $userItemResult = $this->serviceBuilder->getUserScope()->user()->current()->user();
        $taskResult = $this->taskService->add(
            new TaskItemBuilder(
                sprintf('Test task %s', time()),
                $userItemResult->ID,
                $userItemResult->ID
            )
        );

        $rootStorageId = (int)$this->serviceBuilder->getDiskScope()->storage()->list(
            [
                'ID' => $userItemResult->ID,
                'ENTITY_TYPE' => 'user'
            ]
        )->storages()[0]->ID;

        $testContent = 'Test file content - ' . time();
        $base64Content = $this->base64Encoder->encodeString($testContent);
        $fileData = [
            'NAME' => 'test_file_' . time() . '.txt'
        ];
        $uploadedFileResult = $this->serviceBuilder->getDiskScope()->folder()->uploadFile(
            $rootStorageId,
            $fileData,
            $base64Content,
            true
        );

        $this->assertTrue($this->taskFileService->attachExists($taskResult->task()->id, [$uploadedFileResult->getId()])->isSuccess());
        $this->taskService->delete($taskResult->task()->id);
    }
}
