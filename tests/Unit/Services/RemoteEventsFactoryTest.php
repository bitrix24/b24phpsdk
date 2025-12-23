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

namespace Bitrix24\SDK\Tests\Unit\Services;

use Bitrix24\SDK\Application\Contracts\Bitrix24Accounts\Entity\Bitrix24AccountInterface;
use Bitrix24\SDK\Application\Requests\Events\OnApplicationInstall\OnApplicationInstall;
use Bitrix24\SDK\Core\Contracts\Events\EventInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\WrongSecuritySignatureException;
use Bitrix24\SDK\Core\Requests\Events\UnsupportedRemoteEvent;
use Bitrix24\SDK\Services\CRM\Contact\Events\OnCrmContactAdd\OnCrmContactAdd;
use Bitrix24\SDK\Services\RemoteEventsFactory;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(RemoteEventsFactory::class)]
class RemoteEventsFactoryTest extends TestCase
{
    private RemoteEventsFactory $factory;

    #[\Override]
    protected function setUp(): void
    {
        $this->factory = RemoteEventsFactory::init(new NullLogger());
    }

    // ==================== Tests for create() method ====================

    #[Test]
    #[TestDox('create() should successfully create a CRM Contact Add event from valid request')]
    public function testCreateCrmContactAddEvent(): void
    {
        $rawRequest = $this->buildRawRequest([
            'event' => 'ONCRMCONTACTADD',
            'event_handler_id' => '196',
            'data' => ['FIELDS' => ['ID' => '264442']],
            'ts' => '1762089975',
            'auth' => [
                'access_token' => '076a0769007d83b20058f18a0000000100000694f171445612e564746d04afebba973e',
                'expires' => '1762093575',
                'expires_in' => '3600',
                'scope' => 'crm,placement,user_brief,pull,userfieldconfig',
                'domain' => 'bitrix24-php-sdk-playground.bitrix24.ru',
                'server_endpoint' => 'https://oauth.bitrix24.tech/rest/',
                'status' => 'L',
                'client_endpoint' => 'https://bitrix24-php-sdk-playground.bitrix24.com/rest/',
                'member_id' => '010b6886ebc205e43ae65000ee00addb',
                'user_id' => '1',
                'application_token' => 'e24831714bb347622e3ef25af61525cf',
            ],
        ]);

        $request = $this->createRequest($rawRequest);
        $event = $this->factory->create($request);

        $this->assertInstanceOf(OnCrmContactAdd::class, $event);
        $this->assertEquals('ONCRMCONTACTADD', $event->getEventCode());
        $this->assertEquals('264442', $event->getEventPayload()['data']['FIELDS']['ID']);
        $this->assertEquals('e24831714bb347622e3ef25af61525cf', $event->getAuth()->application_token);
    }

    #[Test]
    #[TestDox('create() should successfully create an OnApplicationInstall event from valid request')]
    public function testCreateApplicationInstallEvent(): void
    {
        $rawRequest = $this->buildRawRequest([
            'event' => 'ONAPPINSTALL',
            'event_handler_id' => '1',
            'data' => [
                'VERSION' => '1',
                'LANGUAGE_ID' => 'en',
            ],
            'ts' => '1762089975',
            'auth' => [
                'access_token' => 'test_access_token',
                'expires' => '1762093575',
                'expires_in' => '3600',
                'scope' => 'crm,placement,user_brief',
                'domain' => 'test.bitrix24.com',
                'server_endpoint' => 'https://oauth.bitrix.info/rest/',
                'status' => 'L',
                'client_endpoint' => 'https://test.bitrix24.com/rest/',
                'member_id' => 'test_member_id',
                'user_id' => '1',
                'application_token' => 'test_app_token',
            ],
        ]);

        $request = $this->createRequest($rawRequest);
        $event = $this->factory->create($request);

        $this->assertInstanceOf(OnApplicationInstall::class, $event);
        $this->assertEquals('ONAPPINSTALL', $event->getEventCode());
    }

    #[Test]
    #[TestDox('create() should return UnsupportedRemoteEvent for unknown event codes')]
    public function testCreateUnsupportedEvent(): void
    {
        $rawRequest = $this->buildRawRequest([
            'event' => 'UNSUPPORTEDEVENT',
            'event_handler_id' => '1',
            'data' => [],
            'ts' => '1762089975',
            'auth' => [
                'access_token' => 'test_access_token',
                'expires' => '1762093575',
                'expires_in' => '3600',
                'scope' => 'crm',
                'domain' => 'test.bitrix24.com',
                'server_endpoint' => 'https://oauth.bitrix.info/rest/',
                'status' => 'L',
                'client_endpoint' => 'https://test.bitrix24.com/rest/',
                'member_id' => 'test_member_id',
                'user_id' => '1',
                'application_token' => 'test_app_token',
            ],
        ]);

        $request = $this->createRequest($rawRequest);
        $event = $this->factory->create($request);

        $this->assertInstanceOf(UnsupportedRemoteEvent::class, $event);
        $this->assertEquals('UNSUPPORTEDEVENT', $event->getEventCode());
    }

    #[Test]
    #[TestDox('create() should throw InvalidArgumentException when event key is missing')]
    public function testCreateThrowsExceptionWhenEventKeyMissing(): void
    {
        $rawRequest = $this->buildRawRequest([
            'event_handler_id' => '1',
            'data' => [],
            'ts' => '1762089975',
        ]);

        $request = $this->createRequest($rawRequest);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('event request is not valid');
        $this->factory->create($request);
    }

    #[Test]
    #[TestDox('create() should handle event code case variations')]
    #[DataProvider('eventCodeCaseProvider')]
    public function testCreateHandlesEventCodeCase(string $eventCode, string $expectedClass): void
    {
        $rawRequest = $this->buildRawRequest([
            'event' => $eventCode,
            'event_handler_id' => '1',
            'data' => ['FIELDS' => ['ID' => '1']],
            'ts' => '1762089975',
            'auth' => [
                'access_token' => 'test_access_token',
                'expires' => '1762093575',
                'expires_in' => '3600',
                'scope' => 'crm',
                'domain' => 'test.bitrix24.com',
                'server_endpoint' => 'https://oauth.bitrix.info/rest/',
                'status' => 'L',
                'client_endpoint' => 'https://test.bitrix24.com/rest/',
                'member_id' => 'test_member_id',
                'user_id' => '1',
                'application_token' => 'test_app_token',
            ],
        ]);

        $request = $this->createRequest($rawRequest);
        $event = $this->factory->create($request);

        $this->assertInstanceOf($expectedClass, $event);
    }

    // ==================== Tests for validate() method ====================

    #[Test]
    #[TestDox('validate() should pass when application tokens match')]
    public function testValidatePassesWithMatchingToken(): void
    {
        $applicationToken = 'e24831714bb347622e3ef25af61525cf';
        $rawRequest = $this->buildRawRequest([
            'event' => 'ONCRMCONTACTADD',
            'event_handler_id' => '196',
            'data' => ['FIELDS' => ['ID' => '264442']],
            'ts' => '1762089975',
            'auth' => [
                'access_token' => '076a0769007d83b20058f18a0000000100000694f171445612e564746d04afebba973e',
                'expires' => '1762093575',
                'expires_in' => '3600',
                'scope' => 'crm,placement,user_brief,pull,userfieldconfig',
                'domain' => 'bitrix24-php-sdk-playground.bitrix24.ru',
                'server_endpoint' => 'https://oauth.bitrix24.tech/rest/',
                'status' => 'L',
                'client_endpoint' => 'https://bitrix24-php-sdk-playground.bitrix24.com/rest/',
                'member_id' => '010b6886ebc205e43ae65000ee00addb',
                'user_id' => '1',
                'application_token' => $applicationToken,
            ],
        ]);

        $request = $this->createRequest($rawRequest);
        $event = $this->factory->create($request);

        // Create mock account that validates the token as correct
        $accountMock = $this->createMock(Bitrix24AccountInterface::class);
        $accountMock->method('isApplicationTokenValid')
            ->with($applicationToken)
            ->willReturn(true);

        // Should not throw any exception
        $this->factory->validate($accountMock, $event);
        $this->assertTrue(true); // If we reach here, validation passed
    }

    #[Test]
    #[TestDox('validate() should throw WrongSecuritySignatureException when tokens do not match')]
    public function testValidateThrowsExceptionWithMismatchedToken(): void
    {
        $eventToken = 'different_application_token_67890';

        $rawRequest = $this->buildRawRequest([
            'event' => 'ONCRMCONTACTADD',
            'event_handler_id' => '196',
            'data' => ['FIELDS' => ['ID' => '264442']],
            'ts' => '1762089975',
            'auth' => [
                'access_token' => 'test_access_token',
                'expires' => '1762093575',
                'expires_in' => '3600',
                'scope' => 'crm',
                'domain' => 'test.bitrix24.com',
                'server_endpoint' => 'https://oauth.bitrix.info/rest/',
                'status' => 'L',
                'client_endpoint' => 'https://test.bitrix24.com/rest/',
                'member_id' => 'test_member_id',
                'user_id' => '1',
                'application_token' => $eventToken,
            ],
        ]);

        $request = $this->createRequest($rawRequest);
        $event = $this->factory->create($request);

        // Create mock account that validates the token as incorrect
        $accountMock = $this->createMock(Bitrix24AccountInterface::class);
        $accountMock->method('isApplicationTokenValid')
            ->with($eventToken)
            ->willReturn(false);

        $this->expectException(WrongSecuritySignatureException::class);
        $this->expectExceptionMessage('Wrong security signature for event ONCRMCONTACTADD');
        $this->factory->validate($accountMock, $event);
    }

    #[Test]
    #[TestDox('validate() should skip validation for OnApplicationInstall events')]
    public function testValidateSkipsCheckForApplicationInstallEvent(): void
    {
        $rawRequest = $this->buildRawRequest([
            'event' => 'ONAPPINSTALL',
            'event_handler_id' => '1',
            'data' => [
                'VERSION' => '1',
                'LANGUAGE_ID' => 'en',
            ],
            'ts' => '1762089975',
            'auth' => [
                'access_token' => 'test_access_token',
                'expires' => '1762093575',
                'expires_in' => '3600',
                'scope' => 'crm,placement,user_brief',
                'domain' => 'test.bitrix24.com',
                'server_endpoint' => 'https://oauth.bitrix.info/rest/',
                'status' => 'L',
                'client_endpoint' => 'https://test.bitrix24.com/rest/',
                'member_id' => 'test_member_id',
                'user_id' => '1',
                'application_token' => 'event_app_token',
            ],
        ]);

        $request = $this->createRequest($rawRequest);
        $event = $this->factory->create($request);

        // Create mock account - should not be called for OnApplicationInstall
        $accountMock = $this->createMock(Bitrix24AccountInterface::class);
        $accountMock->expects($this->never())
            ->method('isApplicationTokenValid');

        // Should not throw exception and should not check token
        $this->factory->validate($accountMock, $event);
        $this->assertTrue(true); // If we reach here, validation was skipped
    }

    #[Test]
    #[TestDox('validate() should handle various event types with correct tokens')]
    #[DataProvider('validTokenProvider')]
    public function testValidateWithVariousEventTypes(string $eventCode, string $applicationToken): void
    {
        $rawRequest = $this->buildRawRequest([
            'event' => $eventCode,
            'event_handler_id' => '1',
            'data' => ['FIELDS' => ['ID' => '1']],
            'ts' => '1762089975',
            'auth' => [
                'access_token' => 'test_access_token',
                'expires' => '1762093575',
                'expires_in' => '3600',
                'scope' => 'crm',
                'domain' => 'test.bitrix24.com',
                'server_endpoint' => 'https://oauth.bitrix.info/rest/',
                'status' => 'L',
                'client_endpoint' => 'https://test.bitrix24.com/rest/',
                'member_id' => 'test_member_id',
                'user_id' => '1',
                'application_token' => $applicationToken,
            ],
        ]);

        $request = $this->createRequest($rawRequest);
        $event = $this->factory->create($request);

        // Create mock account that validates the token as correct
        $accountMock = $this->createMock(Bitrix24AccountInterface::class);
        $accountMock->method('isApplicationTokenValid')
            ->with($applicationToken)
            ->willReturn(true);

        // Should not throw exception
        $this->factory->validate($accountMock, $event);
        $this->assertTrue(true);
    }

    // ==================== Data Providers ====================

    public static function eventCodeCaseProvider(): Generator
    {
        yield 'uppercase ONCRMCONTACTADD' => [
            'ONCRMCONTACTADD',
            OnCrmContactAdd::class,
        ];

        yield 'uppercase ONAPPINSTALL' => [
            'ONAPPINSTALL',
            OnApplicationInstall::class,
        ];

        yield 'unknown event code' => [
            'UNKNOWNEVENT',
            UnsupportedRemoteEvent::class,
        ];
    }

    public static function validTokenProvider(): Generator
    {
        $token1 = 'e24831714bb347622e3ef25af61525cf';
        $token2 = '3c6c9248ec54af6bea1159b43ee0ab32';
        $token3 = 'test_application_token_12345';

        yield 'CRM Contact Add with token 1' => ['ONCRMCONTACTADD', $token1];
        yield 'CRM Contact Update with token 2' => ['ONCRMCONTACTUPDATE', $token2];
        yield 'CRM Contact Delete with token 3' => ['ONCRMCONTACTDELETE', $token3];
        yield 'CRM Company Add with token 1' => ['ONCRMCOMPANYADD', $token1];
    }

    // ==================== Helper Methods ====================
    /**
     * Builds a URL-encoded request string from an array of parameters.
     *
     * @param array<string, mixed> $params
     */
    private function buildRawRequest(array $params): string
    {
        return http_build_query($params);
    }

    /**
     * Creates a Symfony Request object from raw request content.
     */
    private function createRequest(string $rawRequest): Request
    {
        // Parse the raw request string into an array for POST parameters
        parse_str($rawRequest, $requestContent);

        $request = new Request(
            [],              // GET parameters
            $requestContent, // POST parameters (parsed from raw request)
            [],              // attributes
            [],              // cookies
            [],              // files
            [],              // server
            $rawRequest      // raw content
        );
        $request->setMethod('POST');

        return $request;
    }
}
