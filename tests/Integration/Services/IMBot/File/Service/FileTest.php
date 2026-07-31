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

namespace Bitrix24\SDK\Tests\Integration\Services\IMBot\File\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IMBot\Bot\BotEventMode;
use Bitrix24\SDK\Services\IMBot\Bot\BotType;
use Bitrix24\SDK\Services\IMBot\Bot\Service\Bot;
use Bitrix24\SDK\Services\IMBot\Chat\Service\Chat;
use Bitrix24\SDK\Services\IMBot\File\Service\File;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(File::class)]
class FileTest extends TestCase
{
    private Bot $botService;

    private Chat $chatService;

    private File $fileService;

    private int $botId;

    private string $dialogId;

    private int $chatId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $scope = Factory::getServiceBuilder(true)->getIMBotScope();
        $this->botService = $scope->bot();
        $this->chatService = $scope->chat();
        $this->fileService = $scope->file();

        // Register a test bot
        $code = sprintf('test_file_bot_%s', uniqid('', true));
        $botResult = $this->botService->register(
            code: $code,
            properties: ['name' => 'Test File Bot'],
            type: BotType::bot,
            eventMode: BotEventMode::fetch
        );
        $this->botId = $botResult->bot()->id;

        // Create a group chat on behalf of the bot
        $chatResult = $this->chatService->add(
            botId: $this->botId,
            userIds: [],
            title: sprintf('File Test Chat %s', uniqid('', true))
        );
        $chat = $chatResult->chat();
        $this->chatId = $chat->id;
        $this->dialogId = $chat->dialogId;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function tearDown(): void
    {
        try {
            $this->chatService->leave($this->botId, $this->chatId);
        } catch (BaseException) {
            // chat may already be gone
        }

        try {
            $this->botService->unregister($this->botId);
        } catch (BaseException) {
            // bot may already be deleted
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('imbot.v2.File.upload sends a file to a chat and returns FileUploadResult')]
    public function testUpload(): void
    {
        $result = $this->fileService->upload(
            botId: $this->botId,
            dialogId: $this->dialogId,
            name: 'test.txt',
            content: base64_encode('IMBot File integration test ' . time()),
            message: 'Test file upload'
        );

        $file = $result->file();

        $this->assertGreaterThan(0, $file->id);
        $this->assertNotEmpty($file->name);
        $this->assertGreaterThan(0, $file->size);
        $this->assertGreaterThan(0, $result->getMessageId());
        $this->assertGreaterThan(0, $result->getChatId());
        $this->assertNotEmpty($result->getDialogId());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('imbot.v2.File.download returns a one-time download URL for an uploaded file')]
    public function testDownload(): void
    {
        // First upload a file to get a file ID
        $uploadResult = $this->fileService->upload(
            botId: $this->botId,
            dialogId: $this->dialogId,
            name: 'download_test.txt',
            content: base64_encode('IMBot File download test ' . time())
        );

        $fileId = $uploadResult->file()->id;
        $this->assertGreaterThan(0, $fileId);

        // Now get a download URL
        $downloadResult = $this->fileService->download(
            botId: $this->botId,
            fileId: $fileId
        );

        $this->assertNotEmpty($downloadResult->getDownloadUrl());
        $this->assertStringContainsString('http', $downloadResult->getDownloadUrl());
    }
}
