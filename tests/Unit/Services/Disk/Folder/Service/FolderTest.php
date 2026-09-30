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

namespace Bitrix24\SDK\Tests\Unit\Services\Disk\Folder\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Core\Response\DTO\Pagination;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Bitrix24\SDK\Core\Response\DTO\Time;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\Disk\Folder\Service\Folder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Folder::class)]
final class FolderTest extends TestCase
{
    #[DataProvider('sharingResults')]
    public function testShareToUserMapsParametersAndBooleanResult(bool $success): void
    {
        $response = $this->createStub(Response::class);
        $response->method('getResponseData')->willReturn(new ResponseData([$success], Time::initWithZeroValues(), new Pagination()));
        $core = $this->createMock(CoreInterface::class);
        $core->expects($this->once())->method('call')
            ->with('disk.folder.sharetouser', ['id' => 42, 'userId' => 7, 'taskName' => 'disk_access_read'])
            ->willReturn($response);

        $result = (new Folder($core, new NullLogger()))->shareToUser(42, 7, 'disk_access_read');
        self::assertInstanceOf(UpdatedItemResult::class, $result);
        self::assertSame($success, $result->isSuccess());
    }

    public static function sharingResults(): array
    {
        return [[true], [false]];
    }

    public function testShareToUserPropagatesPermissionErrors(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects($this->once())->method('call')->willThrowException(new BaseException('ACCESS_DENIED'));
        $this->expectException(BaseException::class);
        $this->expectExceptionMessage('ACCESS_DENIED');
        (new Folder($core, new NullLogger()))->shareToUser(42, 7, 'disk_access_full');
    }
}
