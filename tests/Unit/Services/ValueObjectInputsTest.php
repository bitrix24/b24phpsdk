<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Contracts\LangCodes;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\ValueObjects\LocalizedString;
use Bitrix24\SDK\Core\ValueObjects\Url;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\AI\Engine\EngineCategory;
use Bitrix24\SDK\Services\AI\Engine\EngineSettings;
use Bitrix24\SDK\Services\AI\Engine\Service\Engine;
use Bitrix24\SDK\Services\IMBot\Bot\Service\Bot;
use Bitrix24\SDK\Services\Main\Common\EventHandlerMetadata;
use Bitrix24\SDK\Services\Main\Service\Event;
use Bitrix24\SDK\Services\Main\Result\EventHandlerItemResult;
use Bitrix24\SDK\Services\Main\Service\EventType;
use Bitrix24\SDK\Services\Messageservice\Sender\Service\Sender;
use Bitrix24\SDK\Services\Placement\Service\Placement;
use Bitrix24\SDK\Services\Placement\Service\UserFieldType;
use Bitrix24\SDK\Services\Telephony\Voximplant\InfoCall\Service\InfoCall;
use Bitrix24\SDK\Services\Workflows\Activity\Service\Activity;
use Bitrix24\SDK\Services\Workflows\Common\WorkflowDocumentType;
use Bitrix24\SDK\Services\Workflows\Robot\Service\Robot;
use Bitrix24\SDK\Services\Workflows\ValueObjects\ActivityCode;
use Bitrix24\SDK\Services\Workflows\ValueObjects\RobotCode;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBatch;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Activity::class)]
#[CoversClass(Robot::class)]
#[CoversClass(Event::class)]
#[CoversClass(EventHandlerMetadata::class)]
#[CoversClass(Placement::class)]
#[CoversClass(UserFieldType::class)]
#[CoversClass(Sender::class)]
#[CoversClass(Engine::class)]
#[CoversClass(Bot::class)]
#[CoversClass(InfoCall::class)]
class ValueObjectInputsTest extends TestCase
{
    private const string URL = 'https://example.com/handler';

    #[DataProvider('callbackCases')]
    public function testCallbackObjectsAndStringsHaveIdenticalPayloads(string $class, string $method, array $arguments, string|int $urlArgument, string $endpoint, array $payload): void
    {
        foreach ([self::URL, new Url(self::URL)] as $url) {
            $arguments[$urlArgument] = $url;
            $core = $this->createMock(CoreInterface::class);
            $core->expects($this->once())->method('call')->with($endpoint, $payload)
                ->willReturn((new NullCore())->call($endpoint));
            $this->service($class, $core)->$method(...$arguments);
        }
    }

    #[DataProvider('callbackCases')]
    public function testInvalidCallbackStringsFailBeforeTransport(string $class, string $method, array $arguments, string|int $urlArgument, string $endpoint, array $payload): void
    {
        $arguments[$urlArgument] = 'not an absolute URL';
        $core = $this->createMock(CoreInterface::class);
        $core->expects($this->never())->method('call');
        $this->expectException(InvalidArgumentException::class);
        $this->service($class, $core)->$method(...$arguments);
    }

    public static function callbackCases(): iterable
    {
        yield 'event bind' => [Event::class, 'bind', ['eventCode' => 'ONCRMDEALADD', 'handlerUrl' => self::URL, 'userId' => 7, 'eventType' => EventType::offline, 'authConnector' => 'sync'], 'handlerUrl', 'event.bind', ['event' => 'ONCRMDEALADD', 'handler' => self::URL, 'event_type' => 'offline', 'auth_type' => 7, 'auth_connector' => 'sync']];
        yield 'event unbind' => [Event::class, 'unbind', ['ONCRMDEALADD', self::URL, 7], 1, 'event.unbind', ['event' => 'ONCRMDEALADD', 'handler' => self::URL, 'event_type' => 'online', 'auth_type' => 7]];
        yield 'placement bind' => [Placement::class, 'bind', ['IM_SIDEBAR', self::URL, ['en' => ['TITLE' => 'Widget']]], 1, 'placement.bind', ['PLACEMENT' => 'IM_SIDEBAR', 'HANDLER' => self::URL, 'LANG_ALL' => ['en' => ['TITLE' => 'Widget']], 'OPTIONS' => [], 'USER_ID' => null]];
        yield 'placement unbind' => [Placement::class, 'unbind', ['IM_SIDEBAR', self::URL], 1, 'placement.unbind', ['PLACEMENT' => 'IM_SIDEBAR', 'HANDLER' => self::URL]];
        foreach (['add', 'update'] as $method) {
            yield 'user field '.$method => [UserFieldType::class, $method, ['custom', self::URL, 'Title', 'Description'], 1, 'userfieldtype.'.$method, ['USER_TYPE_ID' => 'custom', 'HANDLER' => self::URL, 'TITLE' => 'Title', 'DESCRIPTION' => 'Description']];
        }

        yield 'sender add' => [Sender::class, 'add', ['sender', 'SMS', self::URL, 'Name', 'Description'], 2, 'messageservice.sender.add', ['CODE' => 'sender', 'TYPE' => 'SMS', 'HANDLER' => self::URL, 'NAME' => 'Name', 'DESCRIPTION' => 'Description']];
        yield 'sender update' => [Sender::class, 'update', ['sender', self::URL, [], ''], 1, 'messageservice.sender.update', ['CODE' => 'sender', 'HANDLER' => self::URL, 'NAME' => [], 'DESCRIPTION' => '']];
        $engineSettings = new EngineSettings('model');
        yield 'engine register' => [Engine::class, 'register', ['Engine', 'engine', EngineCategory::text, self::URL, $engineSettings], 3, 'ai.engine.register', ['name' => 'Engine', 'code' => 'engine', 'category' => 'text', 'completions_url' => self::URL, 'settings' => $engineSettings->toArray()]];
        yield 'bot register' => [Bot::class, 'register', ['code' => 'bot', 'properties' => ['name' => 'Bot'], 'webhookUrl' => self::URL], 'webhookUrl', 'imbot.v2.Bot.register', ['fields' => ['code' => 'bot', 'properties' => ['name' => 'Bot'], 'type' => 'bot', 'eventMode' => 'fetch', 'isHidden' => false, 'isReactionsEnabled' => true, 'isSupportOpenline' => false, 'webhookUrl' => self::URL]]];
        yield 'info call' => [InfoCall::class, 'startWithSound', ['line', '+10000000000', self::URL], 2, 'voximplant.infocall.startwithsound', ['FROM_LINE' => 'line', 'TO_NUMBER' => '+10000000000', 'URL' => self::URL]];
        yield 'activity add' => [Activity::class, 'add', ['activity', self::URL, 7, ['en' => 'Name'], [], false, [], false, [], WorkflowDocumentType::buildForLead(), []], 1, 'bizproc.activity.add', ['CODE' => 'activity', 'HANDLER' => self::URL, 'AUTH_USER_ID' => 7, 'NAME' => ['en' => 'Name'], 'DESCRIPTION' => [], 'USE_SUBSCRIPTION' => 'N', 'PROPERTIES' => [], 'USE_PLACEMENT' => 'N', 'RETURN_PROPERTIES' => [], 'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentLead', 'LEAD'], 'FILTER' => []]];
        yield 'activity update' => [Activity::class, 'update', ['activity', self::URL, null, null, null, null, null, null, null, null, null], 1, 'bizproc.activity.update', ['CODE' => 'activity', 'FIELDS' => ['HANDLER' => self::URL]]];
        yield 'robot add' => [Robot::class, 'add', ['robot', self::URL, 7, ['en' => 'Name'], false, [], false, []], 1, 'bizproc.robot.add', ['CODE' => 'robot', 'HANDLER' => self::URL, 'AUTH_USER_ID' => 7, 'NAME' => ['en' => 'Name'], 'USE_SUBSCRIPTION' => 'N', 'PROPERTIES' => [], 'USE_PLACEMENT' => 'N', 'RETURN_PROPERTIES' => []]];
        yield 'robot update' => [Robot::class, 'update', ['robot', self::URL], 1, 'bizproc.robot.update', ['CODE' => 'robot', 'FIELDS' => ['HANDLER' => self::URL]]];
    }

    #[DataProvider('workflowCases')]
    public function testCodeAndLocalizationObjectsHaveIdenticalPayloads(string $class, string $method, array $primitiveArguments, array $objectArguments, string $endpoint, array $payload): void
    {
        foreach ([$primitiveArguments, $objectArguments] as $arguments) {
            $core = $this->createMock(CoreInterface::class);
            $core->expects($this->once())->method('call')->with($endpoint, $payload)
                ->willReturn((new NullCore())->call($endpoint));
            $this->service($class, $core)->$method(...$arguments);
        }
    }

    public static function workflowCases(): iterable
    {
        $name = new LocalizedString(LangCodes::EN, 'Name');
        $description = new LocalizedString(LangCodes::EN, 'Description');
        yield 'activity add' => [Activity::class, 'add', ['activity', self::URL, 7, $name->toArray(), $description->toArray(), false, [], false, [], WorkflowDocumentType::buildForLead(), []], [new ActivityCode('activity'), new Url(self::URL), 7, $name, $description, false, [], false, [], WorkflowDocumentType::buildForLead(), []], 'bizproc.activity.add', ['CODE' => 'activity', 'HANDLER' => self::URL, 'AUTH_USER_ID' => 7, 'NAME' => ['en' => 'Name'], 'DESCRIPTION' => ['en' => 'Description'], 'USE_SUBSCRIPTION' => 'N', 'PROPERTIES' => [], 'USE_PLACEMENT' => 'N', 'RETURN_PROPERTIES' => [], 'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentLead', 'LEAD'], 'FILTER' => []]];
        yield 'activity update' => [Activity::class, 'update', ['activity', null, null, $name->toArray(), $description->toArray(), null, [], false, [], null, []], [new ActivityCode('activity'), null, null, $name, $description, null, [], false, [], null, []], 'bizproc.activity.update', ['CODE' => 'activity', 'FIELDS' => ['NAME' => ['en' => 'Name'], 'DESCRIPTION' => ['en' => 'Description'], 'PROPERTIES' => [], 'USE_PLACEMENT' => 'N', 'RETURN_PROPERTIES' => [], 'FILTER' => []]]];
        yield 'activity delete' => [Activity::class, 'delete', ['activity'], [new ActivityCode('activity')], 'bizproc.activity.delete', ['CODE' => 'activity']];
        yield 'robot delete' => [Robot::class, 'delete', ['robot'], [new RobotCode('robot')], 'bizproc.robot.delete', ['CODE' => 'robot']];
        yield 'robot add' => [Robot::class, 'add', ['robot', self::URL, 7, $name->toArray(), false, [], false, [], $description->toArray()], [new RobotCode('robot'), new Url(self::URL), 7, $name, false, [], false, [], $description], 'bizproc.robot.add', ['CODE' => 'robot', 'HANDLER' => self::URL, 'AUTH_USER_ID' => 7, 'NAME' => ['en' => 'Name'], 'USE_SUBSCRIPTION' => 'N', 'PROPERTIES' => [], 'USE_PLACEMENT' => 'N', 'RETURN_PROPERTIES' => [], 'DESCRIPTION' => ['en' => 'Description']]];
        yield 'robot update' => [Robot::class, 'update', ['robot', null, null, $name->toArray(), false, [], false, []], [new RobotCode('robot'), null, null, $name, false, [], false, []], 'bizproc.robot.update', ['CODE' => 'robot', 'FIELDS' => ['NAME' => ['en' => 'Name'], 'USE_SUBSCRIPTION' => 'N', 'PROPERTIES' => [], 'USE_PLACEMENT' => 'N', 'RETURN_PROPERTIES' => []]]];
        yield 'sender add' => [Sender::class, 'add', ['sender', 'SMS', self::URL, $name->toArray(), $description->toArray()], ['sender', 'SMS', new Url(self::URL), $name, $description], 'messageservice.sender.add', ['CODE' => 'sender', 'TYPE' => 'SMS', 'HANDLER' => self::URL, 'NAME' => ['en' => 'Name'], 'DESCRIPTION' => ['en' => 'Description']]];
        yield 'sender update' => [Sender::class, 'update', ['sender', null, $name->toArray(), $description->toArray()], ['sender', null, $name, $description], 'messageservice.sender.update', ['CODE' => 'sender', 'NAME' => ['en' => 'Name'], 'DESCRIPTION' => ['en' => 'Description']]];
    }

    #[DataProvider('sentinelCases')]
    public function testNullAndEmptyInputsPreservePayloads(string $class, string $method, array $arguments, string $endpoint, array $payload): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects($this->once())->method('call')->with($endpoint, $payload)->willReturn((new NullCore())->call($endpoint));
        $this->service($class, $core)->$method(...$arguments);
    }

    public static function sentinelCases(): iterable
    {
        yield 'placement null' => [Placement::class, 'unbind', ['IM_SIDEBAR'], 'placement.unbind', ['PLACEMENT' => 'IM_SIDEBAR', 'HANDLER' => null]];
        yield 'placement empty' => [Placement::class, 'unbind', ['IM_SIDEBAR', ''], 'placement.unbind', ['PLACEMENT' => 'IM_SIDEBAR', 'HANDLER' => '']];
        yield 'sender null' => [Sender::class, 'update', ['sender'], 'messageservice.sender.update', ['CODE' => 'sender']];
        yield 'sender empty' => [Sender::class, 'update', ['sender', null, '', []], 'messageservice.sender.update', ['CODE' => 'sender', 'NAME' => '', 'DESCRIPTION' => []]];
        yield 'activity empty localization' => [Activity::class, 'update', ['activity', null, null, [], [], null, null, null, null, null, null], 'bizproc.activity.update', ['CODE' => 'activity', 'FIELDS' => ['NAME' => [], 'DESCRIPTION' => []]]];
        yield 'robot empty localization' => [Robot::class, 'update', ['robot', null, null, []], 'bizproc.robot.update', ['CODE' => 'robot', 'FIELDS' => ['NAME' => []]]];
        yield 'bot null webhook' => [Bot::class, 'register', ['bot', []], 'imbot.v2.Bot.register', ['fields' => ['code' => 'bot', 'properties' => [], 'type' => 'bot', 'eventMode' => 'fetch', 'isHidden' => false, 'isReactionsEnabled' => true, 'isSupportOpenline' => false]]];
    }

    #[DataProvider('invalidWorkflowCodeCases')]
    public function testInvalidWorkflowCodesFailBeforeTransport(string $class, string $method, array $arguments): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects($this->never())->method('call');
        $this->expectException(InvalidArgumentException::class);
        $this->service($class, $core)->$method(...$arguments);
    }

    public static function invalidWorkflowCodeCases(): iterable
    {
        yield 'activity add' => [Activity::class, 'add', ['invalid code!', self::URL, 7, [], [], false, [], false, [], WorkflowDocumentType::buildForLead(), []]];
        yield 'activity update' => [Activity::class, 'update', ['invalid code!', self::URL, null, null, null, null, null, null, null, null, null]];
        yield 'activity delete' => [Activity::class, 'delete', ['invalid code!']];
        yield 'robot add' => [Robot::class, 'add', ['invalid code!', self::URL, 7, [], false, [], false, []]];
        yield 'robot update' => [Robot::class, 'update', ['invalid code!', self::URL]];
        yield 'robot delete' => [Robot::class, 'delete', ['invalid code!']];
    }

    public function testEventMetadataStoresPrimitiveUrlForBothInputForms(): void
    {
        foreach ([self::URL, new Url(self::URL)] as $url) {
            $metadata = new EventHandlerMetadata('ONCRMDEALADD', $url, 7);
            self::assertSame(self::URL, $metadata->handlerUrl);
            self::assertTrue($metadata->isInstalled(new EventHandlerItemResult(['event' => 'oncrmdealadd', 'handler' => self::URL])));
        }
    }

    public function testEventMetadataPreservesLegacyStringsUntilEffectiveEventValidation(): void
    {
        foreach (['', 'placeholder'] as $handler) {
            $metadata = new EventHandlerMetadata('ONCRMDEALADD', $handler, 7);
            self::assertSame($handler, $metadata->handlerUrl);
        }
    }

    /**
     * @param class-string<AbstractService> $class
     */
    private function service(string $class, CoreInterface $core): AbstractService
    {
        $nullLogger = new NullLogger();
        return match ($class) {
            Activity::class => new Activity(new \Bitrix24\SDK\Services\Workflows\Activity\Service\Batch(new NullBatch(), $nullLogger), $core, $nullLogger),
            Robot::class => new Robot(new \Bitrix24\SDK\Services\Workflows\Template\Service\Batch(new NullBatch(), $nullLogger), $core, $nullLogger),
            InfoCall::class => new InfoCall(new \Bitrix24\SDK\Services\Telephony\Voximplant\InfoCall\Service\Batch(new NullBatch(), $nullLogger), $core, $nullLogger),
            default => new $class($core, $nullLogger),
        };
    }
}
