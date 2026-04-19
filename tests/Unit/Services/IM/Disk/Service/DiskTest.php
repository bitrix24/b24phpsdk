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

namespace Bitrix24\SDK\Tests\Unit\Services\IM\Disk\Service;

use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\IM\Disk\Result\FolderIdResult;
use Bitrix24\SDK\Services\IM\Disk\Service\Disk;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(Disk::class)]
final class DiskTest extends TestCase
{
    public function testGetFolderIdMapsChatIdAndDialogId(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core
            ->expects($this->once())
            ->method('call')
            ->with(
                'im.disk.folder.get',
                [
                    'CHAT_ID' => 17,
                    'DIALOG_ID' => 'chat17',
                ],
                ApiVersion::v1
            )
            ->willReturn(
                new Response(
                    new MockResponse(json_encode(['result' => ['ID' => 5153], 'time' => []], JSON_THROW_ON_ERROR)),
                    new Command('im.disk.folder.get', []),
                    new ApiLevelErrorHandler(new NullLogger()),
                    new NullLogger()
                )
            );

        $result = (new Disk($core, new NullLogger()))->getFolderId(17, 'chat17');

        $this->assertInstanceOf(FolderIdResult::class, $result);
        $this->assertSame(5153, $result->getId());
    }
}
