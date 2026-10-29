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

namespace Bitrix24\SDK\Tests\Integration\Core\OperatingLimit;

use Bitrix24\SDK\Core\Batch;
use Bitrix24\SDK\Core\CoreBuilder;
use Bitrix24\SDK\Core\Credentials\Credentials;
use Bitrix24\SDK\Core\Credentials\WebhookUrl;
use Bitrix24\SDK\Core\Exceptions\OperationTimeLimitExceededException;
use Bitrix24\SDK\Core\OperatingLimit\OperatingLimitConfig;
use Bitrix24\SDK\Events\OperatingThresholdCrossedEvent;
use Bitrix24\SDK\Services\CRM\Contact\Service\Batch as ContactBatch;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Mass contact creation through batch to observe how operating time grows.
 *
 * Contacts count is taken from INTEGRATION_TEST_OPERATING_CONTACTS_COUNT, default 100.
 */
#[Group('operating-limit')]
class OperatingLimitContactsBatchAddTest extends TestCase
{
    /**
     * @var list<OperatingThresholdCrossedEvent>
     */
    private array $events = [];

    /**
     * @var list<int>
     */
    private array $createdContactIds = [];

    private ContactBatch $contactBatch;

    protected function setUp(): void
    {
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(OperatingThresholdCrossedEvent::class, function (OperatingThresholdCrossedEvent $operatingThresholdCrossedEvent): void {
            $this->events[] = $operatingThresholdCrossedEvent;
        });

        $core = (new CoreBuilder())
            ->withLogger(Fabric::getLogger())
            ->withEventDispatcher($eventDispatcher)
            // fine-grained thresholds: every percent of the limit, to trace operating growth
            ->withOperatingLimitConfig(new OperatingLimitConfig(480.0, range(1, 99)))
            ->withCredentials(Credentials::createFromWebhook(
                new WebhookUrl($_ENV['BITRIX24_PHP_SDK_PLAYGROUND_WEBHOOK'] ?? $_ENV['BITRIX24_WEBHOOK'])
            ))
            ->build();

        $this->contactBatch = new ContactBatch(new Batch($core, Fabric::getLogger()), Fabric::getLogger());
    }

    protected function tearDown(): void
    {
        foreach (array_chunk($this->createdContactIds, 500) as $chunk) {
            iterator_count($this->contactBatch->delete($chunk));
        }
    }

    #[Test]
    public function testMassContactAddReportsOperatingGrowth(): void
    {
        $count = (int)($_ENV['INTEGRATION_TEST_OPERATING_CONTACTS_COUNT'] ?? 100);
        $contacts = [];
        for ($i = 0; $i < $count; $i++) {
            $contacts[] = ['NAME' => sprintf('operating-limit-%d-%d', time(), $i)];
        }

        $startedAt = microtime(true);
        $limitException = null;
        try {
            foreach ($this->contactBatch->add($contacts) as $addedItemBatchResult) {
                $this->createdContactIds[] = $addedItemBatchResult->getId();
            }
        } catch (OperationTimeLimitExceededException $operationTimeLimitExceededException) {
            $limitException = $operationTimeLimitExceededException;
        }

        $elapsed = microtime(true) - $startedAt;

        $report = [sprintf(
            'contacts requested: %d, created: %d, wall time: %.1f s, threshold events: %d',
            $count,
            count($this->createdContactIds),
            $elapsed,
            count($this->events)
        )];
        foreach ($this->events as $event) {
            $report[] = sprintf(
                '  %s: %2d%% operating=%.2f s reset_at=%s',
                $event->getApiMethod(),
                $event->getThresholdPercent(),
                $event->getOperating(),
                $event->getOperatingResetAt()?->format('H:i:s') ?? 'null'
            );
        }

        if ($limitException instanceof OperationTimeLimitExceededException) {
            $report[] = sprintf(
                'OPERATION_TIME_LIMIT: method=%s command=%s reset_at=%s',
                $limitException->apiMethod ?? 'null',
                $limitException->batchCommandId ?? 'null',
                $limitException->operatingResetAt ?? 'null'
            );
        }

        fwrite(STDOUT, PHP_EOL . implode(PHP_EOL, $report) . PHP_EOL);

        $this->assertNull($limitException, 'operating time limit exceeded before all contacts were created');
        $this->assertCount($count, $this->createdContactIds);
    }
}
