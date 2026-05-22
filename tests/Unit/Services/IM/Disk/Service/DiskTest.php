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

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Core\Response\DTO\Pagination;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Bitrix24\SDK\Core\Response\DTO\Time;
use Bitrix24\SDK\Services\IM\Disk\Result\FolderIdResult;
use Bitrix24\SDK\Services\IM\Disk\Service\Disk;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Disk::class)]
final class DiskTest extends TestCase
{
    public function testGetFolderIdMapsChatIdAndDialogId(): void
    {
        $response = $this->createStub(Response::class);
        $response
            ->method('getResponseData')
            ->willReturn(new ResponseData(['ID' => 5153], Time::initWithZeroValues(), new Pagination()));

        $core = $this->createMock(CoreInterface::class);
        $core
            ->expects($this->once())
            ->method('call')
            ->with(
                'im.disk.folder.get',
                [
                    'CHAT_ID' => 17,
                    'DIALOG_ID' => 'chat17',
                ]
            )
            ->willReturn($response);

        $folderIdResult = (new Disk($core, new NullLogger()))->getFolderId(17, 'chat17');

        $this->assertInstanceOf(FolderIdResult::class, $folderIdResult);
        $this->assertSame(5153, $folderIdResult->getId());
    }
}
