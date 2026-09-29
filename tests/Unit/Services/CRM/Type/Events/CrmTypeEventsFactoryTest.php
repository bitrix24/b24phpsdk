<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\CRM\Type\Events;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\CRM\Type\Events\CrmTypeEventsFactory;
use Bitrix24\SDK\Services\CRM\Type\Events\OnCrmTypeUserFieldAdd\OnCrmTypeUserFieldAdd;
use Bitrix24\SDK\Services\CRM\Type\Events\OnCrmTypeUserFieldDelete\OnCrmTypeUserFieldDelete;
use Bitrix24\SDK\Services\CRM\Type\Events\OnCrmTypeUserFieldSetEnumValues\OnCrmTypeUserFieldSetEnumValues;
use Bitrix24\SDK\Services\CRM\Type\Events\OnCrmTypeUserFieldUpdate\OnCrmTypeUserFieldUpdate;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(CrmTypeEventsFactory::class)]
class CrmTypeEventsFactoryTest extends TestCase
{
    private CrmTypeEventsFactory $factory;

    #[\Override]
    protected function setUp(): void
    {
        $this->factory = new CrmTypeEventsFactory();
    }

    #[Test]
    #[TestDox('isSupport() recognizes all onCrmType user field event codes')]
    #[DataProvider('supportedEventCodeProvider')]
    public function testIsSupport(string $eventCode, string $expectedClass): void
    {
        self::assertNotEmpty($expectedClass);
        self::assertTrue($this->factory->isSupport($eventCode));
        self::assertTrue($this->factory->isSupport(strtolower($eventCode)));
    }

    #[Test]
    #[TestDox('isSupport() rejects unrelated event codes')]
    public function testIsSupportRejectsUnknownCode(): void
    {
        self::assertFalse($this->factory->isSupport('ONCRMCOMPANYUSERFIELDADD'));
    }

    #[Test]
    #[TestDox('create() builds OnCrmTypeUserFieldAdd and exposes payload fields')]
    public function testCreateBuildsOnCrmTypeUserFieldAdd(): void
    {
        $event = $this->factory->create($this->createRequest(OnCrmTypeUserFieldAdd::CODE));

        self::assertInstanceOf(OnCrmTypeUserFieldAdd::class, $event);
        self::assertSame(OnCrmTypeUserFieldAdd::CODE, $event->getEventCode());

        $onCrmTypeUserFieldAddPayload = $event->getPayload();
        self::assertSame('6977', $onCrmTypeUserFieldAddPayload->id);
        self::assertSame('CRM_13', $onCrmTypeUserFieldAddPayload->entityId);
        self::assertSame('UF_CRM_13_1742999523', $onCrmTypeUserFieldAddPayload->fieldName);
    }

    #[Test]
    #[TestDox('create() builds OnCrmTypeUserFieldUpdate and exposes payload fields')]
    public function testCreateBuildsOnCrmTypeUserFieldUpdate(): void
    {
        $event = $this->factory->create($this->createRequest(OnCrmTypeUserFieldUpdate::CODE));

        self::assertInstanceOf(OnCrmTypeUserFieldUpdate::class, $event);
        self::assertSame(OnCrmTypeUserFieldUpdate::CODE, $event->getEventCode());

        $onCrmTypeUserFieldUpdatePayload = $event->getPayload();
        self::assertSame('6977', $onCrmTypeUserFieldUpdatePayload->id);
        self::assertSame('CRM_13', $onCrmTypeUserFieldUpdatePayload->entityId);
        self::assertSame('UF_CRM_13_1742999523', $onCrmTypeUserFieldUpdatePayload->fieldName);
    }

    #[Test]
    #[TestDox('create() builds OnCrmTypeUserFieldDelete and exposes payload fields')]
    public function testCreateBuildsOnCrmTypeUserFieldDelete(): void
    {
        $event = $this->factory->create($this->createRequest(OnCrmTypeUserFieldDelete::CODE));

        self::assertInstanceOf(OnCrmTypeUserFieldDelete::class, $event);
        self::assertSame(OnCrmTypeUserFieldDelete::CODE, $event->getEventCode());

        $onCrmTypeUserFieldDeletePayload = $event->getPayload();
        self::assertSame('6977', $onCrmTypeUserFieldDeletePayload->id);
        self::assertSame('CRM_13', $onCrmTypeUserFieldDeletePayload->entityId);
        self::assertSame('UF_CRM_13_1742999523', $onCrmTypeUserFieldDeletePayload->fieldName);
    }

    #[Test]
    #[TestDox('create() builds OnCrmTypeUserFieldSetEnumValues and exposes payload fields')]
    public function testCreateBuildsOnCrmTypeUserFieldSetEnumValues(): void
    {
        $event = $this->factory->create($this->createRequest(OnCrmTypeUserFieldSetEnumValues::CODE));

        self::assertInstanceOf(OnCrmTypeUserFieldSetEnumValues::class, $event);
        self::assertSame(OnCrmTypeUserFieldSetEnumValues::CODE, $event->getEventCode());

        $onCrmTypeUserFieldSetEnumValuesPayload = $event->getPayload();
        self::assertSame('6977', $onCrmTypeUserFieldSetEnumValuesPayload->id);
        self::assertSame('CRM_13', $onCrmTypeUserFieldSetEnumValuesPayload->entityId);
        self::assertSame('UF_CRM_13_1742999523', $onCrmTypeUserFieldSetEnumValuesPayload->fieldName);
    }

    #[Test]
    #[TestDox('create() throws for an event code it does not support')]
    public function testCreateThrowsForUnsupportedEvent(): void
    {
        $request = $this->createRequest('ONCRMCOMPANYUSERFIELDADD');

        $this->expectException(InvalidArgumentException::class);
        $this->factory->create($request);
    }

    public static function supportedEventCodeProvider(): Generator
    {
        yield 'add' => [OnCrmTypeUserFieldAdd::CODE, OnCrmTypeUserFieldAdd::class];
        yield 'update' => [OnCrmTypeUserFieldUpdate::CODE, OnCrmTypeUserFieldUpdate::class];
        yield 'delete' => [OnCrmTypeUserFieldDelete::CODE, OnCrmTypeUserFieldDelete::class];
        yield 'setEnumValues' => [OnCrmTypeUserFieldSetEnumValues::CODE, OnCrmTypeUserFieldSetEnumValues::class];
    }

    private function createRequest(string $eventCode): Request
    {
        $payload = [
            'event' => $eventCode,
            'event_handler_id' => '713',
            'data' => [
                'id' => '6977',
                'entityId' => 'CRM_13',
                'fieldName' => 'UF_CRM_13_1742999523',
            ],
            'ts' => '1742999523',
            'auth' => [
                'application_token' => 'test_app_token',
            ],
        ];

        $rawRequest = http_build_query($payload);
        parse_str($rawRequest, $requestContent);

        $request = new Request([], $requestContent, [], [], [], [], $rawRequest);
        $request->setMethod('POST');

        return $request;
    }
}
