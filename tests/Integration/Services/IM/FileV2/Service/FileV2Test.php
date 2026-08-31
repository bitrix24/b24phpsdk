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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\FileV2\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\FileV2\Service\FileV2;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileV2::class)]
class FileV2Test extends TestCase
{
    private FileV2 $fileService;

    /**
     * ID of the group chat created for the test run; cleaned up in tearDown.
     */
    private int $chatId = 0;

    #[\Override]
    protected function setUp(): void
    {
        $this->fileService = Fabric::getServiceBuilder()->getIMScope()->fileV2();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function tearDown(): void
    {
        if ($this->chatId > 0) {
            try {
                $this->fileService->core->call('im.chat.leave', ['CHAT_ID' => $this->chatId]);
            } catch (BaseException) {
                // ignore — chat may already be gone
            }
        }
    }

    /**
     * Creates a temporary group chat for the current API user and returns its dialog ID (e.g. "chat5").
     *
     * @throws BaseException
     * @throws TransportException
     */
    private function createChat(): string
    {
        $userId = (int)$this->fileService->core->call('PROFILE')->getResponseData()->getResult()['ID'];

        $this->chatId = (int)$this->fileService->core->call(
            'im.chat.add',
            [
                'USERS' => [$userId],
                'TYPE'  => 'CHAT',
                'TITLE' => sprintf('IM FileV2 Test %s', time()),
            ]
        )->getResponseData()->getResult()[0];

        return 'chat' . $this->chatId;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.v2.File.upload sends a file to a chat and returns FileV2UploadResult')]
    public function testUpload(): void
    {
        $dialogId = $this->createChat();

        $fileV2UploadResult = $this->fileService->upload(
            dialogId: $dialogId,
            name: 'test.txt',
            content: base64_encode('IM FileV2 integration test ' . time()),
            message: 'Test file upload'
        );

        $file = $fileV2UploadResult->file();

        $this->assertGreaterThan(0, $file->id);
        $this->assertNotEmpty($file->name);
        $this->assertGreaterThan(0, $file->size);
        $this->assertGreaterThan(0, $fileV2UploadResult->getMessageId());
        $this->assertGreaterThan(0, $fileV2UploadResult->getChatId());
        $this->assertNotEmpty($fileV2UploadResult->getDialogId());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.v2.File.download returns a one-time download URL for an uploaded file')]
    public function testDownload(): void
    {
        $dialogId = $this->createChat();

        // First upload a file to get a file ID
        $fileV2UploadResult = $this->fileService->upload(
            dialogId: $dialogId,
            name: 'download_test.txt',
            content: base64_encode('IM FileV2 download test ' . time())
        );

        $fileId = $fileV2UploadResult->file()->id;
        $this->assertGreaterThan(0, $fileId);

        // Now get a download URL
        $fileV2DownloadResult = $this->fileService->download($fileId);

        $this->assertNotEmpty($fileV2DownloadResult->getDownloadUrl());
        $this->assertStringContainsString('http', $fileV2DownloadResult->getDownloadUrl());
    }
}
