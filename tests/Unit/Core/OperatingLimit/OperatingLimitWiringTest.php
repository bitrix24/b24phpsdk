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

namespace Bitrix24\SDK\Tests\Unit\Core\OperatingLimit;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\CoreBuilder;
use Bitrix24\SDK\Core\Credentials\Credentials;
use Bitrix24\SDK\Core\Credentials\WebhookUrl;
use Bitrix24\SDK\Core\Exceptions\OperationTimeLimitExceededException;
use Bitrix24\SDK\Core\OperatingLimit\OperatingLimitConfig;
use Bitrix24\SDK\Events\OperatingThresholdCrossedEvent;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Services\ServiceBuilderFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class OperatingLimitWiringTest extends TestCase
{
    /**
     * @var list<OperatingThresholdCrossedEvent>
     */
    private array $events = [];

    private EventDispatcher $dispatcher;

    protected function setUp(): void
    {
        $this->events = [];
        $this->dispatcher = new EventDispatcher();
        $this->dispatcher->addListener(OperatingThresholdCrossedEvent::class, function (OperatingThresholdCrossedEvent $operatingThresholdCrossedEvent): void {
            $this->events[] = $operatingThresholdCrossedEvent;
        });
    }

    #[Test]
    public function testSingleCallDispatchesEvents(): void
    {
        $core = $this->core([$this->ok(['result' => ['ID' => 1], 'time' => $this->time(25, 1000)])]);

        $response = $core->call('crm.contact.get', ['id' => 1]);
        $this->assertSame([], $this->thresholds(), 'response parsing must stay lazy');

        $response->getResponseData();
        $this->assertSame([10, 20], $this->thresholds());
    }

    #[Test]
    public function testResponseObservedOnlyOnce(): void
    {
        $core = $this->core([$this->ok(['result' => ['ID' => 1], 'time' => $this->time(25, 1000)])]);

        $response = $core->call('crm.contact.get', ['id' => 1]);
        $response->getResponseData();
        $response->getResponseData();

        $this->assertSame([10, 20], $this->thresholds());
    }

    #[Test]
    public function testBatchPartialOperationTimeLimitKeepsType(): void
    {
        $core = $this->core([$this->ok([
            'result' => [
                'result' => ['a' => true],
                'result_error' => ['b' => ['error' => 'OPERATION_TIME_LIMIT', 'error_description' => 'Method is blocked due to operation time limit.']],
                'result_total' => [],
                'result_next' => [],
                'result_time' => ['a' => $this->time(30, 1000)],
            ],
            'time' => $this->time(null, null),
        ])]);

        $response = $core->call('batch', ['halt' => 0, 'cmd' => ['a' => 'crm.deal.update?ID=1', 'b' => 'crm.deal.update?ID=2']]);
        try {
            $response->getResponseData();
            $this->fail('OperationTimeLimitExceededException expected');
        } catch (OperationTimeLimitExceededException $operationTimeLimitExceededException) {
            $this->assertSame(OperationTimeLimitExceededException::class, $operationTimeLimitExceededException::class);
            $this->assertSame('crm.deal.update', $operationTimeLimitExceededException->apiMethod);
            $this->assertSame('b', $operationTimeLimitExceededException->batchCommandId);
            $this->assertSame(1000, $operationTimeLimitExceededException->operatingResetAt);
        }

        $this->assertSame([10, 20, 30], $this->thresholds());
    }

    #[Test]
    public function testSingleCall429Enriched(): void
    {
        $core = $this->core([
            $this->ok(['result' => true, 'time' => $this->time(50, 2000)]),
            new MockResponse(
                (string)json_encode(['error' => 'OPERATION_TIME_LIMIT', 'error_description' => 'Method is blocked due to operation time limit.']),
                ['http_code' => 429]
            ),
        ]);

        $core->call('crm.deal.update', ['id' => 1])->getResponseData();
        try {
            $core->call('crm.deal.update', ['id' => 2]);
            $this->fail('OperationTimeLimitExceededException expected');
        } catch (OperationTimeLimitExceededException $operationTimeLimitExceededException) {
            $this->assertSame(OperationTimeLimitExceededException::class, $operationTimeLimitExceededException::class);
            $this->assertSame('crm.deal.update', $operationTimeLimitExceededException->apiMethod);
            $this->assertNull($operationTimeLimitExceededException->batchCommandId);
            $this->assertSame(2000, $operationTimeLimitExceededException->operatingResetAt);
        }
    }

    #[Test]
    public function testNoListenerNoBehaviourChange(): void
    {
        $core = (new CoreBuilder())
            ->withCredentials($this->credentials())
            ->withHttpClient(new MockHttpClient([$this->ok(['result' => ['ID' => 7], 'time' => $this->time(470, 1000)])]))
            ->build();

        $this->assertSame(['ID' => 7], $core->call('crm.contact.get', ['id' => 7])->getResponseData()->getResult());
    }

    #[Test]
    public function testServiceBuilderFactoryAcceptsConfig(): void
    {
        $withConfig = new ServiceBuilderFactory($this->dispatcher, new NullLogger(), new OperatingLimitConfig(420.0));
        $withoutConfig = new ServiceBuilderFactory($this->dispatcher, new NullLogger());

        $this->assertInstanceOf(ServiceBuilder::class, $withConfig->initFromWebhook('https://test.bitrix24.com/rest/1/xxxxxxxx/'));
        $this->assertInstanceOf(ServiceBuilder::class, $withoutConfig->initFromWebhook('https://test.bitrix24.com/rest/1/xxxxxxxx/'));
    }

    /**
     * @param list<MockResponse> $responses
     */
    private function core(array $responses): CoreInterface
    {
        return (new CoreBuilder())
            ->withCredentials($this->credentials())
            ->withHttpClient(new MockHttpClient($responses))
            ->withEventDispatcher($this->dispatcher)
            ->withOperatingLimitConfig(new OperatingLimitConfig(100.0))
            ->build();
    }

    private function credentials(): Credentials
    {
        return Credentials::createFromWebhook(new WebhookUrl('https://test.bitrix24.com/rest/1/xxxxxxxx/'));
    }

    /**
     * @param array<string, mixed> $body
     */
    private function ok(array $body): MockResponse
    {
        return new MockResponse((string)json_encode($body), ['http_code' => 200]);
    }

    /**
     * @return list<int>
     */
    private function thresholds(): array
    {
        return array_map(static fn(OperatingThresholdCrossedEvent $operatingThresholdCrossedEvent): int => $operatingThresholdCrossedEvent->getThresholdPercent(), $this->events);
    }

    /**
     * @return array<string, mixed>
     */
    private function time(?int $operating, ?int $resetAt): array
    {
        $time = [
            'start' => 1,
            'finish' => 2,
            'duration' => 1,
            'processing' => 0,
            'date_start' => '2026-01-01T00:00:00+00:00',
            'date_finish' => '2026-01-01T00:00:01+00:00',
        ];
        if ($operating !== null) {
            $time['operating'] = $operating;
        }

        if ($resetAt !== null) {
            $time['operating_reset_at'] = $resetAt;
        }

        return $time;
    }
}
