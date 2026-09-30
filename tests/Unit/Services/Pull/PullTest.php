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

namespace Bitrix24\SDK\Tests\Unit\Services\Pull;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Contracts\BulkItemsReaderInterface;
use Bitrix24\SDK\Core\Response\DTO\Pagination;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Bitrix24\SDK\Core\Response\DTO\Time;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Pull\Channel\Service\Channel;
use Bitrix24\SDK\Services\Pull\Configuration\Service\Configuration;
use Bitrix24\SDK\Services\Pull\Application\Service\Application;
use Bitrix24\SDK\Services\Pull\Watch\Service\Watch;
use Bitrix24\SDK\Services\Pull\PullServiceBuilder;
use Bitrix24\SDK\Services\ServiceBuilder;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class PullTest extends TestCase
{
    public function testLegacyChannelResultsAndCasts(): void
    {
        $raw = ['user_id' => '12','public_id' => 'synthetic-channel','signature' => 'synthetic-signature','start' => '2026-01-01T00:00:00+00:00','end' => '2026-01-01T12:00:00+00:00'];
        $core = $this->createStub(CoreInterface::class);
        $core->method('call')->willReturnOnConsecutiveCalls($this->response($raw), $this->response([12 => $raw,34 => array_replace($raw, ['user_id' => 34])]), $this->response([]));
        $channel = new Channel($core, new NullLogger());
        $item = $channel->get()->getChannel();
        self::assertSame(12, $item->user_id);
        self::assertSame('synthetic-channel', $item->public_id);
        self::assertSame('synthetic-signature', $item->signature);
        self::assertInstanceOf(CarbonImmutable::class, $item->start);
        self::assertInstanceOf(CarbonImmutable::class, $item->end);
        $items = $channel->list([12,34])->getChannels();
        self::assertSame([12,34], array_keys($items));
        self::assertSame(34, $items[34]->user_id);
        self::assertSame([], $channel->list([])->getChannels());
    }

    public function testConfigurationPreservesDeploymentSpecificFields(): void
    {
        $raw = ['server' => ['websocket_enabled' => false],'jwt' => 'synthetic','clientId' => 'synthetic','extra' => 0];
        $core = $this->createStub(CoreInterface::class);
        $core->method('call')->willReturn($this->response($raw));
        self::assertSame($raw, new Configuration($core, new NullLogger())->get()->getConfiguration());
        self::assertSame($raw, new Application($core, new NullLogger())->config()->getConfiguration());
    }

    public function testWatchReturnsMixedListAndMutationResultsUseLegacyBoolean(): void
    {
        $core = $this->createStub(CoreInterface::class);
        $core->method('call')->willReturnOnConsecutiveCalls($this->response([12,'13']), $this->response([true]), $this->response([false]));
        self::assertSame([12,'13'], new Watch($core, new NullLogger())->extend(['tag'])->getTags());
        self::assertTrue(new Application($core, new NullLogger())->event('refresh')->isSuccess());
        self::assertFalse(new Application($core, new NullLogger())->push('12', 'hello')->isSuccess());
    }

    public function testScopeAndServicesAreCached(): void
    {
        $serviceBuilder = new ServiceBuilder($this->createStub(CoreInterface::class), $this->createStub(BatchOperationsInterface::class), $this->createStub(BulkItemsReaderInterface::class), new NullLogger());
        $pull = $serviceBuilder->getPullScope();
        self::assertInstanceOf(PullServiceBuilder::class, $pull);
        self::assertSame($pull, $serviceBuilder->getPullScope());
        foreach (['channel' => Channel::class,'configuration' => Configuration::class,'application' => Application::class,'watch' => Watch::class] as $accessor => $class) {
            self::assertInstanceOf($class, $pull->$accessor());
            self::assertSame($pull->$accessor(), $pull->$accessor());
        }
    }

    private function response(array $result): Response
    {
        $response = $this->createStub(Response::class);
        $response->method('getResponseData')->willReturn(new ResponseData($result, Time::initWithZeroValues(), new Pagination(null, null)));
        return $response;
    }
}
