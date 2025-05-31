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

namespace Bitrix24\SDK\Tests\Application\Contracts\Bitrix24Accounts\Entity;

use Bitrix24\SDK\Application\ApplicationStatus;
use Bitrix24\SDK\Application\Contracts\Bitrix24Accounts\Entity\Bitrix24AccountInterface;
use Bitrix24\SDK\Application\Contracts\Bitrix24Accounts\Entity\Bitrix24AccountStatus;
use Bitrix24\SDK\Core\Credentials\AuthToken;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\UnknownScopeCodeException;
use Bitrix24\SDK\Core\Response\DTO\RenewedAuthToken;
use Carbon\CarbonImmutable;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Throwable;

#[CoversClass(Bitrix24AccountInterface::class)]
abstract class Bitrix24AccountInterfaceTest extends TestCase
{
    abstract protected function createBitrix24AccountImplementation(
        Uuid $uuid,
        int $bitrix24UserId,
        bool $isBitrix24UserAdmin,
        bool $isMasterAccount,
        string $memberId,
        string $domainUrl,
        AuthToken $authToken,
        int $applicationVersion,
        Scope $applicationScope
    ): Bitrix24AccountInterface;

    #[Test]
    #[DataProvider('bitrix24AccountDataProvider')]
    #[TestDox('test getId method')]
    final public function testGetId(
        Uuid $uuid,
        int $bitrix24UserId,
        bool $isBitrix24UserAdmin,
        bool $isMasterAccount,
        string $memberId,
        string $domainUrl,
        AuthToken $authToken,
        int $applicationVersion,
        Scope $applicationScope
    ): void {
        $bitrix24Account = $this->createBitrix24AccountImplementation(
            $uuid,
            $bitrix24UserId,
            $isBitrix24UserAdmin,
            $isMasterAccount,
            $memberId,
            $domainUrl,
            $authToken,
            $applicationVersion,
            $applicationScope
        );
        $this->assertEquals($uuid, $bitrix24Account->getId());
    }

    #[Test]
    #[DataProvider('bitrix24AccountDataProvider')]
    #[TestDox('test getBitrix24UserId method')]
    final public function testGetBitrix24UserId(
        Uuid $uuid,
        int $bitrix24UserId,
        bool $isBitrix24UserAdmin,
        bool $isMasterAccount,
        string $memberId,
        string $domainUrl,
        AuthToken $authToken,
        int $applicationVersion,
        Scope $applicationScope
    ): void {
        $bitrix24Account = $this->createBitrix24AccountImplementation(
            $uuid,
            $bitrix24UserId,
            $isBitrix24UserAdmin,
            $isMasterAccount,
            $memberId,
            $domainUrl,
            $authToken,
            $applicationVersion,
            $applicationScope
        );
        $this->assertEquals($bitrix24UserId, $bitrix24Account->getBitrix24UserId());
    }

    #[Test]
    #[DataProvider('bitrix24AccountDataProvider')]
    #[TestDox('test isBitrix24UserAdmin method')]
    final public function testisBitrix24UserAdmin(
        Uuid $uuid,
        int $bitrix24UserId,
        bool $isBitrix24UserAdmin,
        bool $isMasterAccount,
        string $memberId,
        string $domainUrl,
        AuthToken $authToken,
        int $applicationVersion,
        Scope $applicationScope
    ): void {
        $bitrix24Account = $this->createBitrix24AccountImplementation(
            $uuid,
            $bitrix24UserId,
            $isBitrix24UserAdmin,
            $isMasterAccount,
            $memberId,
            $domainUrl,
            $authToken,
            $applicationVersion,
            $applicationScope
        );
        $this->assertEquals($isBitrix24UserAdmin, $bitrix24Account->isBitrix24UserAdmin());
    }

    #[Test]
    #[DataProvider('bitrix24AccountDataProvider')]
    #[TestDox('test isBitrix24UserAdmin method')]
    final public function testIsMasterAccount(
        Uuid $uuid,
        int $bitrix24UserId,
        bool $isBitrix24UserAdmin,
        bool $isMasterAccount,
        string $memberId,
        string $domainUrl,
        AuthToken $authToken,
        int $applicationVersion,
        Scope $applicationScope
    ): void {
        $bitrix24Account = $this->createBitrix24AccountImplementation(
            $uuid,
            $bitrix24UserId,
            $isBitrix24UserAdmin,
            $isMasterAccount,
            $memberId,
            $domainUrl,
            $authToken,
            $applicationVersion,
            $applicationScope
        );
        $this->assertEquals($isMasterAccount, $bitrix24Account->isMasterAccount());
    }

    #[Test]
    #[DataProvider('bitrix24AccountDataProvider')]
    #[TestDox('test getMemberId method')]
    final public function testGetMemberId(
        Uuid $uuid,
        int $bitrix24UserId,
        bool $isBitrix24UserAdmin,
        bool $isMasterAccount,
        string $memberId,
        string $domainUrl,
        AuthToken $authToken,
        int $applicationVersion,
        Scope $applicationScope
    ): void {
        $bitrix24Account = $this->createBitrix24AccountImplementation(
            $uuid,
            $bitrix24UserId,
            $isBitrix24UserAdmin,
            $isMasterAccount,
            $memberId,
            $domainUrl,
            $authToken,
            $applicationVersion,
            $applicationScope
        );
        $this->assertEquals($memberId, $bitrix24Account->getMemberId());
    }

    #[Test]
    #[DataProvider('bitrix24AccountDataProvider')]
    #[TestDox('test getDomainUrl method')]
    final public function testGetDomainUrl(
        Uuid $uuid,
        int $bitrix24UserId,
        bool $isBitrix24UserAdmin,
        bool $isMasterAccount,
        string $memberId,
        string $domainUrl,
        AuthToken $authToken,
        int $applicationVersion,
        Scope $applicationScope
    ): void {
        $bitrix24Account = $this->createBitrix24AccountImplementation(
            $uuid,
            $bitrix24UserId,
            $isBitrix24UserAdmin,
            $isMasterAccount,
            $memberId,
            $domainUrl,
            $authToken,
            $applicationVersion,
            $applicationScope
        );
        $this->assertEquals($domainUrl, $bitrix24Account->getDomainUrl());
    }

    #[Test]
    #[DataProvider('bitrix24AccountDataProvider')]
    #[TestDox('test getStatus method')]
    final public function testGetStatus(
        Uuid $uuid,
        int $bitrix24UserId,
        bool $isBitrix24UserAdmin,
        bool $isMasterAccount,
        string $memberId,
        string $domainUrl,
        AuthToken $authToken,
        int $applicationVersion,
        Scope $applicationScope
    ): void {
        $bitrix24Account = $this->createBitrix24AccountImplementation(
            $uuid,
            $bitrix24UserId,
            $isBitrix24UserAdmin,
            $isMasterAccount,
            $memberId,
            $domainUrl,
            $authToken,
            $applicationVersion,
            $applicationScope
        );
        $this->assertEquals(Bitrix24AccountStatus::new, $bitrix24Account->getStatus());
    }

    #[Test]
    #[DataProvider('bitrix24AccountDataProvider')]
    #[TestDox('test getAuthToken method')]
    final public function testGetAuthToken(
        Uuid $uuid,
        int $bitrix24UserId,
        bool $isBitrix24UserAdmin,
        bool $isMasterAccount,
        string $memberId,
        string $domainUrl,
        AuthToken $authToken,
        int $applicationVersion,
        Scope $applicationScope
    ): void {
        $bitrix24Account = $this->createBitrix24AccountImplementation(
            $uuid,
            $bitrix24UserId,
            $isBitrix24UserAdmin,
            $isMasterAccount,
            $memberId,
            $domainUrl,
            $authToken,
            $applicationVersion,
            $applicationScope
        );
        $this->assertEquals($authToken, $bitrix24Account->getAuthToken());
    }

    #[Test]
    #[DataProvider('bitrix24AccountDataProvider')]
    #[TestDox('test renewAuthToken method')]
    final public function testRenewAuthToken(
        Uuid $uuid,
        int $bitrix24UserId,
        bool $isBitrix24UserAdmin,
        bool $isMasterAccount,
        string $memberId,
        string $domainUrl,
        AuthToken $authToken,
        int $applicationVersion,
        Scope $applicationScope
    ): void {
        $bitrix24Account = $this->createBitrix24AccountImplementation(
            $uuid,
            $bitrix24UserId,
            $isBitrix24UserAdmin,
            $isMasterAccount,
            $memberId,
            $domainUrl,
            $authToken,
            $applicationVersion,
            $applicationScope
        );
        $newAuthToken = new AuthToken('access_token-2', 'refresh_token=2', 1609459202);
        $applicationStatus = ApplicationStatus::subscription();

        $renewedAuthToken = new RenewedAuthToken(
            $newAuthToken,
            $memberId,
            'https://bitrix24.com/client',
            'https://bitrix24.com/server',
            $applicationStatus,
            $domainUrl
        );
        $bitrix24Account->renewAuthToken($renewedAuthToken);


        $this->assertEquals($newAuthToken, $bitrix24Account->getAuthToken());
    }

    #[Test]
    #[DataProvider('bitrix24AccountDataProvider')]
    #[TestDox('test getApplicationVersion method')]
    final public function testGetApplicationVersion(
        Uuid $uuid,
        int $bitrix24UserId,
        bool $isBitrix24UserAdmin,
        bool $isMasterAccount,
        string $memberId,
        string $domainUrl,
        AuthToken $authToken,
        int $applicationVersion,
        Scope $applicationScope
    ): void {
        $bitrix24Account = $this->createBitrix24AccountImplementation(
            $uuid,
            $bitrix24UserId,
            $isBitrix24UserAdmin,
            $isMasterAccount,
            $memberId,
            $domainUrl,
            $authToken,
            $applicationVersion,
            $applicationScope
        );
        $this->assertEquals($applicationVersion, $bitrix24Account->getApplicationVersion());
    }


    #[Test]
    #[DataProvider('bitrix24AccountDataProvider')]
    #[TestDox('test getApplicationScope method')]
    final public function testGetApplicationScope(
        Uuid $uuid,
        int $bitrix24UserId,
        bool $isBitrix24UserAdmin,
        bool $isMasterAccount,
        string $memberId,
        string $domainUrl,
        AuthToken $authToken,
        int $applicationVersion,
        Scope $applicationScope
    ): void {
        $bitrix24Account = $this->createBitrix24AccountImplementation(
            $uuid,
            $bitrix24UserId,
            $isBitrix24UserAdmin,
            $isMasterAccount,
            $memberId,
            $domainUrl,
            $authToken,
            $applicationVersion,
            $applicationScope
        );
        $this->assertEquals($applicationScope, $bitrix24Account->getApplicationScope());
    }

    #[Test]
    #[DataProvider('bitrix24AccountDataProvider')]
    #[TestDox('test changeDomainUrl method')]
    final public function testChangeDomainUrl(
        Uuid $uuid,
        int $bitrix24UserId,
        bool $isBitrix24UserAdmin,
        bool $isMasterAccount,
        string $memberId,
        string $domainUrl,
        AuthToken $authToken,
        int $applicationVersion,
        Scope $applicationScope
    ): void {
        $newDomainUrl = 'new-bitrix24.com';
        $bitrix24Account = $this->createBitrix24AccountImplementation(
            $uuid,
            $bitrix24UserId,
            $isBitrix24UserAdmin,
            $isMasterAccount,
            $memberId,
            $domainUrl,
            $authToken,
            $applicationVersion,
            $applicationScope
        );
        $bitrix24Account->changeDomainUrl($newDomainUrl);
        $this->assertEquals($newDomainUrl, $bitrix24Account->getDomainUrl());
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Test]
    #[DataProvider('newBitrix24AccountDataProvider')]
    #[TestDox('test applicationInstalled method')]
    final public function testApplicationInstalled(
        Uuid $uuid,
        int $bitrix24UserId,
        bool $isBitrix24UserAdmin,
        bool $isMasterAccount,
        string $memberId,
        string $domainUrl,
        AuthToken $authToken,
        int $applicationVersion,
        Scope $applicationScope,
        ?string $applicationToken,
        ?Throwable $throwable
    ): void {
        if ($throwable instanceof \Throwable) {
            $this->expectException($throwable::class);
        }

        $bitrix24Account = $this->createBitrix24AccountImplementation(
            $uuid,
            $bitrix24UserId,
            $isBitrix24UserAdmin,
            $isMasterAccount,
            $memberId,
            $domainUrl,
            $authToken,
            $applicationVersion,
            $applicationScope
        );
        $bitrix24Account->applicationInstalled($applicationToken);

        $this->assertEquals($bitrix24Account->getStatus(), Bitrix24AccountStatus::active);
        if ($applicationToken !== null) {
            $this->assertTrue(
                $bitrix24Account->isApplicationTokenValid($applicationToken),
                sprintf('returned false when application token is valid - %s', $applicationToken)
            );
        }
    }

    #[Test]
    #[DataProvider('newBitrix24AccountDataProvider')]
    #[TestDox('test applicationUninstalled method')]
    final public function testApplicationUninstalled(
        Uuid $uuid,
        int $bitrix24UserId,
        bool $isBitrix24UserAdmin,
        bool $isMasterAccount,
        string $memberId,
        string $domainUrl,
        AuthToken $authToken,
        int $applicationVersion,
        Scope $applicationScope,
        ?string $applicationToken,
        ?Throwable $throwable
    ): void {
        if ($throwable instanceof \Throwable) {
            $this->expectException($throwable::class);
        }

        $bitrix24Account = $this->createBitrix24AccountImplementation(
            $uuid,
            $bitrix24UserId,
            $isBitrix24UserAdmin,
            $isMasterAccount,
            $memberId,
            $domainUrl,
            $authToken,
            $applicationVersion,
            $applicationScope
        );
        $bitrix24Account->applicationInstalled($applicationToken);
        $bitrix24Account->applicationUninstalled($applicationToken);
        $this->assertEquals(Bitrix24AccountStatus::deleted, $bitrix24Account->getStatus());
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Test]
    #[DataProvider('bitrix24AccountWithStatusNewDataProvider')]
    #[TestDox('test isApplicationTokenValid method')]
    final public function testIsApplicationTokenValid(
        Uuid $uuid,
        int $bitrix24UserId,
        bool $isBitrix24UserAdmin,
        bool $isMasterAccount,
        string $memberId,
        string $domainUrl,
        AuthToken $authToken,
        int $applicationVersion,
        Scope $applicationScope,
        string $applicationToken,
    ): void {
        $bitrix24Account = $this->createBitrix24AccountImplementation(
            $uuid,
            $bitrix24UserId,
            $isBitrix24UserAdmin,
            $isMasterAccount,
            $memberId,
            $domainUrl,
            $authToken,
            $applicationVersion,
            $applicationScope
        );
        $this->assertFalse($bitrix24Account->isApplicationTokenValid($applicationToken));
        $bitrix24Account->applicationInstalled($applicationToken);
        $this->assertTrue($bitrix24Account->isApplicationTokenValid($applicationToken));
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Test]
    #[DataProvider('bitrix24AccountWithStatusNewDataProvider')]
    #[TestDox('test getUpdatedAt method')]
    final public function testGetUpdatedAt(
        Uuid $uuid,
        int $bitrix24UserId,
        bool $isBitrix24UserAdmin,
        bool $isMasterAccount,
        string $memberId,
        string $domainUrl,
        AuthToken $authToken,
        int $applicationVersion,
        Scope $applicationScope,
        string $applicationToken,
    ): void {
        $bitrix24Account = $this->createBitrix24AccountImplementation(
            $uuid,
            $bitrix24UserId,
            $isBitrix24UserAdmin,
            $isMasterAccount,
            $memberId,
            $domainUrl,
            $authToken,
            $applicationVersion,
            $applicationScope
        );


        $bitrix24Account->applicationInstalled($applicationToken);
        $this->assertFalse($bitrix24Account->getUpdatedAt()->equalTo($bitrix24Account->getCreatedAt()));
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Test]
    #[DataProvider('bitrix24AccountWithStatusNewDataProvider')]
    #[TestDox('test updateApplicationVersion method')]
    final public function testUpdateApplicationVersion(
        Uuid $uuid,
        int $bitrix24UserId,
        bool $isBitrix24UserAdmin,
        bool $isMasterAccount,
        string $memberId,
        string $domainUrl,
        AuthToken $authToken,
        int $applicationVersion,
        Scope $applicationScope,
        string $applicationToken,
        int $newApplicationVersion,
        Scope $newApplicationScope,
        ?Throwable $throwable
    ): void {
        if ($throwable instanceof \Throwable) {
            $this->expectException($throwable::class);
        }

        $bitrix24Account = $this->createBitrix24AccountImplementation(
            $uuid,
            $bitrix24UserId,
            $isBitrix24UserAdmin,
            $isMasterAccount,
            $memberId,
            $domainUrl,
            $authToken,
            $applicationVersion,
            $applicationScope
        );
        $bitrix24Account->applicationInstalled($applicationToken);
        $bitrix24Account->updateApplicationVersion($authToken, $bitrix24UserId, $newApplicationVersion, $newApplicationScope);
        $this->assertEquals($newApplicationVersion, $bitrix24Account->getApplicationVersion());
        $this->assertTrue($newApplicationScope->equal($bitrix24Account->getApplicationScope()));
        $this->assertEquals($authToken, $bitrix24Account->getAuthToken());
        $this->assertEquals($bitrix24UserId, $bitrix24Account->getBitrix24UserId());
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Test]
    #[DataProvider('newBitrix24AccountDataProvider')]
    #[TestDox('test markAsBlocked and getComment methods')]
    final public function testMarkAsBlocked(
        Uuid $uuid,
        int $bitrix24UserId,
        bool $isBitrix24UserAdmin,
        bool $isMasterAccount,
        string $memberId,
        string $domainUrl,
        AuthToken $authToken,
        int $applicationVersion,
        Scope $applicationScope,
        ?string $applicationToken,
        ?Throwable $throwable
    ): void {
        if ($throwable instanceof \Throwable) {
            $this->expectException($throwable::class);
        }

        $bitrix24Account = $this->createBitrix24AccountImplementation(
            $uuid,
            $bitrix24UserId,
            $isBitrix24UserAdmin,
            $isMasterAccount,
            $memberId,
            $domainUrl,
            $authToken,
            $applicationVersion,
            $applicationScope
        );
        $bitrix24Account->applicationInstalled($applicationToken);

        $comment = 'block account just for fun';
        $bitrix24Account->markAsBlocked($comment);
        $this->assertEquals(Bitrix24AccountStatus::blocked, $bitrix24Account->getStatus());
        $this->assertEquals($comment, $bitrix24Account->getComment());
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Test]
    #[DataProvider('newBitrix24AccountDataProvider')]
    #[TestDox('test markAsActive and getComment methods')]
    final public function testMarkAsActive(
        Uuid $uuid,
        int $bitrix24UserId,
        bool $isBitrix24UserAdmin,
        bool $isMasterAccount,
        string $memberId,
        string $domainUrl,
        AuthToken $authToken,
        int $applicationVersion,
        Scope $applicationScope,
        ?string $applicationToken,
        ?Throwable $throwable
    ): void {
        if ($throwable instanceof \Throwable) {
            $this->expectException($throwable::class);
        }

        $bitrix24Account = $this->createBitrix24AccountImplementation(
            $uuid,
            $bitrix24UserId,
            $isBitrix24UserAdmin,
            $isMasterAccount,
            $memberId,
            $domainUrl,
            $authToken,
            $applicationVersion,
            $applicationScope
        );
        $bitrix24Account->applicationInstalled($applicationToken);

        $comment = 'block account just for fun';
        $bitrix24Account->markAsBlocked($comment);
        $this->assertEquals(Bitrix24AccountStatus::blocked, $bitrix24Account->getStatus());
        $this->assertEquals($comment, $bitrix24Account->getComment());
        $comment = 'activate account';
        $bitrix24Account->markAsActive($comment);
        $this->assertEquals(Bitrix24AccountStatus::active, $bitrix24Account->getStatus());
        $this->assertEquals($comment, $bitrix24Account->getComment());
    }

    /**
     * @throws UnknownScopeCodeException
     */
    public static function bitrix24AccountWithStatusNewDataProvider(): Generator
    {
        yield 'valid-update' => [
            Uuid::v7(),
            12345,
            true,
            true,
            'member123',
            'https://example.com',
            new AuthToken('access_token', 'refresh_token', 1609459200),
            1,
            new Scope(['crm', 'task']),
            'application_token',
            2,
            new Scope(['crm', 'task', 'telephony']),
            null
        ];
        yield 'valid-update-same-scope' => [
            Uuid::v7(),
            12345,
            true,
            true,
            'member123',
            'https://example.com',
            new AuthToken('access_token', 'refresh_token', 1609459200),
            1,
            new Scope(['crm', 'task']),
            'application_token',
            2,
            new Scope(['task', 'crm']),
            null
        ];
        yield 'valid-downgrade-scope' => [
            Uuid::v7(),
            12345,
            true,
            true,
            'member123',
            'https://example.com',
            new AuthToken('access_token', 'refresh_token', 1609459200),
            1,
            new Scope([]),
            'application_token',
            2,
            new Scope(['task', 'crm']),
            null
        ];
        yield 'invalid-version' => [
            Uuid::v7(),
            12345,
            true,
            true,
            'member123',
            'https://example.com',
            new AuthToken('access_token', 'refresh_token', 1609459200),
            1,
            new Scope(['crm', 'task']),
            'application_token',
            1,
            new Scope(['crm', 'task', 'telephony']),
            new InvalidArgumentException()
        ];
    }

    public static function bitrix24AccountForUninstallDataProvider(): Generator
    {
        yield 'empty-application-token' => [
            Uuid::v7(),
            12345,
            true,
            true,
            'member123',
            'https://example.com',
            new AuthToken('access_token', 'refresh_token', 1609459200),
            1,
            new Scope(['crm', 'task']),
            '',
            new InvalidArgumentException()
        ];

        yield 'account-status-new' => [
            Uuid::v7(),
            12345,
            true,
            true,
            'member123',
            'https://example.com',
            new AuthToken('access_token', 'refresh_token', 1609459200),
            1,
            new Scope(['crm', 'task']),
            'application_token_value',
            null
        ];
        yield 'account-status-active' => [
            Uuid::v7(),
            12345,
            true,
            true,
            'member123',
            'https://example.com',
            new AuthToken('access_token', 'refresh_token', 1609459200),
            1,
            new Scope(['crm', 'task']),
            'application_token_value',
            new InvalidArgumentException()

        ];
        yield 'account-status-blocked' => [
            Uuid::v7(),
            12345,
            true,
            true,
            'member123',
            'https://example.com',
            new AuthToken('access_token', 'refresh_token', 1609459200),
            1,
            new Scope(['crm', 'task']),
            'application_token_value',
            new InvalidArgumentException()
        ];
        yield 'account-status-deleted' => [
            Uuid::v7(),
            12345,
            true,
            true,
            'member123',
            'https://example.com',
            new AuthToken('access_token', 'refresh_token', 1609459200),
            1,
            new Scope(['crm', 'task']),
            'application_token_value',
            new InvalidArgumentException()
        ];
    }

    public static function newBitrix24AccountDataProvider(): Generator
    {
        yield 'account-status-new' => [
            Uuid::v7(),
            12345,
            true,
            true,
            'member123',
            'https://example.com',
            new AuthToken('access_token', 'refresh_token', 1609459200),
            1,
            new Scope(['crm', 'task']),
            'application_token_value',
            null
        ];

        yield 'account-status-new-without-application-token' => [
            Uuid::v7(),
            12345,
            true,
            true,
            'member123',
            'https://example.com',
            new AuthToken('access_token', 'refresh_token', 1609459200),
            1,
            new Scope(['crm', 'task']),
            null,
            null
        ];
    }

    /**
     * @throws UnknownScopeCodeException
     */
    public static function bitrix24AccountDataProvider(): Generator
    {
        yield 'account-status-new' => [
            Uuid::v7(),
            12345,
            true,
            true,
            'member123',
            'https://example.com',
            new AuthToken('access_token', 'refresh_token', 1609459200),
            1,
            new Scope(['crm', 'task'])
        ];
        yield 'account-status-active' => [
            Uuid::v7(),
            12345,
            true,
            true,
            'member123',
            'https://example.com',
            new AuthToken('access_token', 'refresh_token', 1609459200),
            1,
            new Scope(['crm', 'task'])
        ];
        yield 'account-not-master-account' => [
            Uuid::v7(),
            12345,
            true,
            false,
            'member123',
            'https://example.com',
            new AuthToken('access_token', 'refresh_token', 1609459200),
            1,
            new Scope(['crm', 'task'])
        ];
    }
}