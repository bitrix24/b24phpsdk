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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Disk\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Disk\Folder\Service\Folder;
use Bitrix24\SDK\Services\IM\Disk\Service\Batch;
use Bitrix24\SDK\Services\IM\Disk\Service\Disk;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private Disk $diskService;

    private Folder $folderService;

    private ?int $chatId = null;

    #[\Override]
    protected function setUp(): void
    {
        $this->diskService = Fabric::getServiceBuilder()->getIMScope()->disk();
        $this->folderService = Fabric::getServiceBuilder()->getDiskScope()->folder();
    }

    #[\Override]
    protected function tearDown(): void
    {
        if ($this->chatId !== null) {
            try {
                $this->diskService->core->call('im.chat.leave', ['CHAT_ID' => $this->chatId]);
            } catch (BaseException) {
                // chat may already be left
            }
        }

        $this->chatId = null;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Batch im.disk.file.delete deletes several files from a chat folder')]
    public function testDeleteFile(): void
    {
        $this->chatId = $this->createChat();
        $dialogId = 'chat' . $this->chatId;
        $folderId = $this->diskService->getFolderId(dialogId: $dialogId)->getId();

        $files = [];
        for ($i = 1; $i <= 2; $i++) {
            $sourceFileId = $this->uploadTinyFileToImFolder($folderId, $i);
            $chatFileId = $this->diskService->commitFile(dialogId: $dialogId, fileId: $sourceFileId)->diskIds()[0];
            $files[] = ['CHAT_ID' => $this->chatId, 'FILE_ID' => $chatFileId];
        }

        $results = iterator_to_array($this->diskService->batch->deleteFile($files), false);

        $this->assertCount(2, $results);
        foreach ($results as $result) {
            $this->assertTrue($result->isSuccess());
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    private function createChat(): int
    {
        $userId = (int)$this->diskService->core->call('PROFILE')->getResponseData()->getResult()['ID'];

        return (int)$this->diskService->core->call(
            'im.chat.add',
            [
                'USERS' => [$userId],
                'TYPE' => 'CHAT',
                'TITLE' => sprintf('IT IM Disk Batch %s', uniqid('', true)),
            ]
        )->getResponseData()->getResult()[0];
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    private function uploadTinyFileToImFolder(int $folderId, int $number): int
    {
        return $this->folderService->uploadFile(
            $folderId,
            ['NAME' => sprintf('im_disk_batch_%d_%s.txt', $number, uniqid())],
            base64_encode('IM Disk batch integration test ' . $number),
            true,
        )->getId();
    }
}
