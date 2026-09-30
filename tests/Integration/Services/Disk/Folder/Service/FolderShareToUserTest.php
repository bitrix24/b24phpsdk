<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Disk\Folder\Service;

use Bitrix24\SDK\Services\Disk\Folder\Service\Folder;
use Bitrix24\SDK\Tests\Integration\Services\Disk\TemporaryDiskFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Folder::class)]
final class FolderShareToUserTest extends TestCase
{
    public function testShareDisposableFolderToConfiguredRecipient(): void
    {
        $recipientId = (int)($_ENV['BITRIX24_DISK_TEST_RECIPIENT_ID'] ?? 0);
        if ($recipientId <= 0) {
            self::markTestSkipped('Set BITRIX24_DISK_TEST_RECIPIENT_ID to an active test user to verify folder sharing.');
        }

        $core = TemporaryDiskFixture::core();
        $owner = $core->call('user.current')->getResponseData()->getResult();
        self::assertNotSame((int)$owner['ID'], $recipientId, 'The recipient must differ from the fixture owner.');
        $users = $core->call('user.get', ['filter' => ['ID' => $recipientId, 'ACTIVE' => true]])->getResponseData()->getResult();
        self::assertCount(1, $users, 'The configured recipient must be an active test user.');
        self::assertSame($recipientId, (int)$users[0]['ID']);

        $fixture = TemporaryDiskFixture::create();
        try {
            $folder = new Folder($fixture->core, new NullLogger());
            $result = $folder->shareToUser($fixture->folderId, $recipientId, 'disk_access_read');
            self::assertTrue($result->isSuccess());
            self::assertSame([true], $result->getCoreResponse()->getResponseData()->getResult());
        } finally {
            $fixture->delete();
        }
    }
}
