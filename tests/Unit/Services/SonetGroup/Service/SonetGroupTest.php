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

namespace Bitrix24\SDK\Tests\Unit\Services\SonetGroup\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Response\DTO\Pagination;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Bitrix24\SDK\Core\Response\DTO\Time;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\SonetGroup\Service\SonetGroup;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(SonetGroup::class)]
final class SonetGroupTest extends TestCase
{
    #[DataProvider('contracts')]
    public function testRequestAndResponseContracts(string $method, array $arguments, string $endpoint, array $payload, array $result, string $accessor, mixed $expected): void
    {
        $response = $this->createStub(Response::class);
        $response->method('getResponseData')->willReturn(new ResponseData($result, Time::initWithZeroValues(), new Pagination()));
        $core = $this->createMock(CoreInterface::class);
        $core->expects($this->once())->method('call')->with($endpoint, $this->identicalTo($payload))->willReturn($response);
        $sonetGroup = new SonetGroup($core, new NullLogger());
        self::assertSame($expected, $sonetGroup->$method(...$arguments)->$accessor());
    }

    public static function contracts(): iterable
    {
        foreach ([true, false] as $allowed) {
            yield ['featureAccess', [17, 'tasks', 'view'], 'sonet_group.feature.access', ['GROUP_ID' => 17, 'FEATURE' => 'tasks', 'OPERATION' => 'view'], [$allowed], 'isSuccess', $allowed];
            yield ['requestUser', [17], 'sonet_group.user.request', ['GROUP_ID' => 17], [$allowed], 'isSuccess', $allowed];
        }

        foreach ([null, '', 'Please join'] as $message) {
            $payload = ['GROUP_ID' => 17];
            if ($message !== null) {
                $payload['MESSAGE'] = $message;
            }

            yield ['requestUser', [17, $message], 'sonet_group.user.request', $payload, [true], 'isSuccess', true];
            foreach ([21, [21, 22]] as $users) {
                foreach ([[], ['21', '22']] as $ids) {
                    yield ['inviteUser', [17, $users, $message], 'sonet_group.user.invite', ['GROUP_ID' => 17, 'USER_ID' => $users] + $payload, $ids, 'getUserIds', array_map(intval(...), $ids)];
                }
            }
        }

        foreach (['E', 'K'] as $role) {
            foreach ([21, [21, 22]] as $users) {
                foreach ([[], ['21', 22]] as $ids) {
                    yield ['updateUser', [17, $users, $role], 'sonet_group.user.update', ['GROUP_ID' => 17, 'USER_ID' => $users, 'ROLE' => $role], $ids, 'getUserIds', array_map(intval(...), $ids)];
                }
            }
        }

        yield ['getUsers', [17], 'sonet_group.user.get', ['ID' => 17], [], 'getUsers', []];
    }

    public function testMembersAreTypedAndPreserveRoles(): void
    {
        $response = $this->createStub(Response::class);
        $response->method('getResponseData')->willReturn(new ResponseData([['USER_ID' => '21', 'ROLE' => 'A'], ['USER_ID' => 22, 'ROLE' => 'K']], Time::initWithZeroValues(), new Pagination()));
        $core = $this->createMock(CoreInterface::class);
        $core->expects($this->once())->method('call')->with('sonet_group.user.get', $this->identicalTo(['ID' => 17]))->willReturn($response);
        $users = (new SonetGroup($core, new NullLogger()))->getUsers(17)->getUsers();
        self::assertSame(21, $users[0]->USER_ID);
        self::assertSame('A', $users[0]->ROLE);
        self::assertSame(22, $users[1]->USER_ID);
        self::assertSame('K', $users[1]->ROLE);
    }

    #[DataProvider('errorContracts')]
    public function testApiErrorsPropagate(string $method, array $arguments): void
    {
        $baseException = new BaseException('ACCESS_DENIED');
        $core = $this->createMock(CoreInterface::class);
        $core->expects($this->once())->method('call')->willThrowException($baseException);
        $this->expectExceptionObject($baseException);
        (new SonetGroup($core, new NullLogger()))->$method(...$arguments);
    }

    public static function errorContracts(): iterable
    {
        yield ['featureAccess', [17, 'tasks', 'view']];
        yield ['getUsers', [17]];
        yield ['inviteUser', [17, [21]]];
        yield ['requestUser', [17]];
        yield ['updateUser', [17, [21], 'E']];
    }
}
