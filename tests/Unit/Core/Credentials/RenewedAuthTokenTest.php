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

namespace Bitrix24\SDK\Tests\Unit\Core\Credentials;

use Bitrix24\SDK\Application\ApplicationStatus;
use Bitrix24\SDK\Application\Local\Entity\LocalAppAuth;
use Bitrix24\SDK\Application\Local\Repository\LocalAppAuthRepositoryInterface;
use Bitrix24\SDK\Core\Contracts\ApiClientInterface;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\AuthToken;
use Bitrix24\SDK\Core\Credentials\Credentials;
use Bitrix24\SDK\Core\Credentials\RenewedAuthToken;
use Bitrix24\SDK\Core\Response\DTO\RenewedAuthToken as LegacyRenewedAuthToken;
use Error;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Component\Process\Process;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[CoversClass(LegacyRenewedAuthToken::class)]
class RenewedAuthTokenTest extends TestCase
{
    /**
     * @param class-string<LegacyRenewedAuthToken> $className
     */
    #[DataProvider('tokenClassProvider')]
    public function testConstructorPreservesTokenAndMetadata(string $className): void
    {
        $authToken = new AuthToken('access-token', 'refresh-token', 1900000000, 3600);
        $applicationStatus = ApplicationStatus::subscription();

        $token = new $className(
            authToken: $authToken,
            memberId: 'member-id',
            clientEndpoint: 'https://portal.bitrix24.com/rest/',
            serverEndpoint: 'https://oauth.bitrix.info/rest/',
            applicationStatus: $applicationStatus,
            domain: 'portal.bitrix24.com'
        );

        self::assertSame(LegacyRenewedAuthToken::class, $token::class);
        self::assertInstanceOf($className, $token);
        self::assertSame($authToken, $token->authToken);
        self::assertSame(3600, $token->authToken->expiresIn);
        self::assertSame($applicationStatus, $token->applicationStatus);
        $this->assertTokenMetadata($token);
    }

    /**
     * @param class-string<LegacyRenewedAuthToken> $className
     */
    #[DataProvider('tokenClassProvider')]
    public function testFactoryPreservesRuntimeIdentityWithTokenAndMetadata(string $className): void
    {
        $token = $className::initFromArray($this->tokenPayload());

        self::assertSame(LegacyRenewedAuthToken::class, $token::class);
        self::assertInstanceOf($className, $token);
        self::assertSame('access-token', $token->authToken->accessToken);
        self::assertSame('refresh-token', $token->authToken->refreshToken);
        self::assertSame(1900000000, $token->authToken->expires);
        $this->assertTokenMetadata($token);
    }

    /**
     * @param class-string<LegacyRenewedAuthToken> $className
     */
    #[DataProvider('tokenClassProvider')]
    public function testPropertiesRemainReadonly(string $className): void
    {
        $token = $className::initFromArray($this->tokenPayload());

        $this->expectException(Error::class);
        $this->expectExceptionMessage('Cannot modify readonly property');

        new ReflectionProperty($token, 'memberId')->setValue($token, 'changed-member-id');
    }

    public function testCredentialsTokenPassesLegacyStorageContract(): void
    {
        $token = RenewedAuthToken::initFromArray($this->tokenPayload());
        $repository = new class () implements LocalAppAuthRepositoryInterface {
            public ?LegacyRenewedAuthToken $savedToken = null;

            public function getAuth(): LocalAppAuth
            {
                throw new LogicException('Not used by this test');
            }

            public function getApplicationToken(): ?string
            {
                return null;
            }

            public function saveRenewedToken(LegacyRenewedAuthToken $renewedAuthToken): void
            {
                $this->savedToken = $renewedAuthToken;
            }

            public function save(LocalAppAuth $localAppAuth): void
            {
                throw new LogicException('Not used by this test');
            }
        };

        $repository->saveRenewedToken($token);

        self::assertInstanceOf(LegacyRenewedAuthToken::class, $token);
        self::assertSame($token, $repository->savedToken);
    }

    public function testLegacyApiClientImplementationKeepsPublishedReturnType(): void
    {
        $token = LegacyRenewedAuthToken::initFromArray($this->tokenPayload());
        $apiClient = new readonly class ($token) implements ApiClientInterface {
            public function __construct(private LegacyRenewedAuthToken $token)
            {
            }

            public function getResponse(string $apiMethod, array $parameters = [], ApiVersion $apiVersion = ApiVersion::v1): ResponseInterface
            {
                throw new LogicException('Not used by this test');
            }

            public function getNewAuthToken(): LegacyRenewedAuthToken
            {
                return $this->token;
            }

            public function getCredentials(): Credentials
            {
                throw new LogicException('Not used by this test');
            }
        };

        self::assertSame($token, $apiClient->getNewAuthToken());
        self::assertSame(LegacyRenewedAuthToken::class, $apiClient->getNewAuthToken()::class);
    }

    public function testLegacyFactoryOverrideKeepsPublishedReturnType(): void
    {
        $token = new readonly class (
            new AuthToken('access-token', 'refresh-token', 1900000000),
            'member-id',
            'https://portal.bitrix24.com/rest/',
            'https://oauth.bitrix.info/rest/',
            ApplicationStatus::subscription(),
            'portal.bitrix24.com'
        ) extends LegacyRenewedAuthToken {
            #[\Override]
            public static function initFromArray(array $response): self
            {
                return new self(
                    AuthToken::initFromArray($response),
                    (string) $response['member_id'],
                    (string) $response['client_endpoint'],
                    (string) $response['server_endpoint'],
                    ApplicationStatus::initFromString((string) $response['status']),
                    (string) $response['domain']
                );
            }
        };

        self::assertSame($token::class, $token::initFromArray($this->tokenPayload())::class);
    }

    public function testInheritedLegacyFactoryDoesNotCallCustomSubclassConstructor(): void
    {
        $token = new readonly class ('custom-member-id') extends LegacyRenewedAuthToken {
            public function __construct(string $memberId)
            {
                parent::__construct(
                    new AuthToken('access-token', 'refresh-token', 1900000000),
                    $memberId,
                    'https://portal.bitrix24.com/rest/',
                    'https://oauth.bitrix.info/rest/',
                    ApplicationStatus::subscription(),
                    'portal.bitrix24.com'
                );
            }
        };

        $renewedToken = $token::initFromArray($this->tokenPayload());

        self::assertSame('custom-member-id', $token->memberId);
        self::assertSame(LegacyRenewedAuthToken::class, $renewedToken::class);
        self::assertSame('member-id', $renewedToken->memberId);
    }

    public function testAliasPreservesSerializationIdentity(): void
    {
        $token = RenewedAuthToken::initFromArray($this->tokenPayload());
        $legacyToken = LegacyRenewedAuthToken::initFromArray($this->tokenPayload());

        self::assertSame(serialize($legacyToken), serialize($token));
        $restoredToken = unserialize(serialize($token));
        self::assertInstanceOf(LegacyRenewedAuthToken::class, $restoredToken);
        self::assertInstanceOf(RenewedAuthToken::class, $restoredToken);
        self::assertSame(LegacyRenewedAuthToken::class, $restoredToken::class);
    }

    #[DataProvider('freshProcessTokenClassProvider')]
    public function testAliasIsAvailableInFreshProcessWithoutEagerAutoload(string $tokenClass): void
    {
        $script = <<<'PHP'
require 'vendor/autoload.php';
$token = new $argv[1](
    new \Bitrix24\SDK\Core\Credentials\AuthToken('access-token', 'refresh-token', 1900000000),
    'member-id',
    'https://portal.bitrix24.com/rest/',
    'https://oauth.bitrix.info/rest/',
    \Bitrix24\SDK\Application\ApplicationStatus::subscription(),
    'portal.bitrix24.com'
);
$acceptNew = static fn (\Bitrix24\SDK\Core\Credentials\RenewedAuthToken $token): \Bitrix24\SDK\Core\Credentials\RenewedAuthToken => $token;
$acceptLegacy = static fn (\Bitrix24\SDK\Core\Response\DTO\RenewedAuthToken $token): \Bitrix24\SDK\Core\Response\DTO\RenewedAuthToken => $token;
if ($acceptNew($token) !== $token || $acceptLegacy($token) !== $token) {
    exit(1);
}
echo $token::class;
PHP;
        $process = new Process([PHP_BINARY, '-r', $script, '--', $tokenClass], dirname(__DIR__, 4));
        $process->run();

        self::assertSame(0, $process->getExitCode(), $process->getErrorOutput() . $process->getOutput());
        self::assertSame(LegacyRenewedAuthToken::class, $process->getOutput());
    }

    /**
     * @return array<string, array{class-string}>
     */
    public static function freshProcessTokenClassProvider(): array
    {
        return [
            'legacy name loaded first' => [LegacyRenewedAuthToken::class],
            'credentials name loaded first' => [RenewedAuthToken::class],
        ];
    }

    /**
     * @return array<string, array{class-string<LegacyRenewedAuthToken>}>
     */
    public static function tokenClassProvider(): array
    {
        return [
            'legacy DTO' => [LegacyRenewedAuthToken::class],
            'credentials' => [RenewedAuthToken::class],
        ];
    }

    private function assertTokenMetadata(LegacyRenewedAuthToken $token): void
    {
        self::assertSame('member-id', $token->memberId);
        self::assertSame('portal.bitrix24.com', $token->domain);
        self::assertTrue($token->applicationStatus->isSubscription());
        self::assertSame('https://portal.bitrix24.com/rest/', $token->clientEndpoint);
        self::assertSame('https://oauth.bitrix.info/rest/', $token->serverEndpoint);
        self::assertSame($token->clientEndpoint, $token->getEndpoints()->getClientUrl());
        self::assertSame($token->serverEndpoint, $token->getEndpoints()->getAuthServerUrl());
    }

    /**
     * @return array<string, int|string>
     */
    private function tokenPayload(): array
    {
        return [
            'access_token' => 'access-token',
            'refresh_token' => 'refresh-token',
            'expires' => 1900000000,
            'member_id' => 'member-id',
            'client_endpoint' => 'https://portal.bitrix24.com/rest/',
            'server_endpoint' => 'https://oauth.bitrix.info/rest/',
            'status' => 'S',
            'domain' => 'portal.bitrix24.com',
        ];
    }
}
