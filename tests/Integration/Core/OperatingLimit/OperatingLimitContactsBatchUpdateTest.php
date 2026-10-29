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
use Bitrix24\SDK\Core\OperatingLimit\OperatingLimitSleepListener;
use Bitrix24\SDK\Events\OperatingThresholdCrossedEvent;
use Bitrix24\SDK\Services\CRM\Contact\Service\Batch as ContactBatch;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Mass contact update through batch to observe how operating time grows.
 *
 * Reuses contacts named «operating-limit-*» left by previous runs and creates missing ones.
 * INTEGRATION_TEST_OPERATING_CONTACTS_COUNT — contacts count, default 100;
 * INTEGRATION_TEST_OPERATING_UPDATE_ROUNDS — how many times every contact is updated, default 1;
 * INTEGRATION_TEST_OPERATING_CLEANUP — delete contacts after the test, default 1;
 * INTEGRATION_TEST_OPERATING_SHARD / INTEGRATION_TEST_OPERATING_SHARDS — slice of contacts for parallel runs, default 0 / 1;
 * INTEGRATION_TEST_OPERATING_SLEEP_FROM — attach OperatingLimitSleepListener from this threshold percent, default 0 (off).
 */
#[Group('operating-limit')]
class OperatingLimitContactsBatchUpdateTest extends TestCase
{
    private const string NAME_PREFIX = 'operating-limit-';

    /**
     * @var list<OperatingThresholdCrossedEvent>
     */
    private array $events = [];

    /**
     * @var list<int>
     */
    private array $contactIds = [];

    /**
     * @var list<int>
     */
    private array $sleeps = [];

    private ContactBatch $contactBatch;

    protected function setUp(): void
    {
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(OperatingThresholdCrossedEvent::class, function (OperatingThresholdCrossedEvent $operatingThresholdCrossedEvent): void {
            $this->events[] = $operatingThresholdCrossedEvent;
        });

        $sleepFrom = (int)($_ENV['INTEGRATION_TEST_OPERATING_SLEEP_FROM'] ?? 0);
        if ($sleepFrom > 0) {
            $eventDispatcher->addListener(OperatingThresholdCrossedEvent::class, new OperatingLimitSleepListener(
                $sleepFrom,
                1,
                Fabric::getLogger(),
                function (int $seconds): void {
                    $this->sleeps[] = $seconds;
                    sleep($seconds);
                }
            ));
        }

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
        if ((int)($_ENV['INTEGRATION_TEST_OPERATING_CLEANUP'] ?? 1) === 0) {
            return;
        }

        foreach (array_chunk($this->contactIds, 500) as $chunk) {
            iterator_count($this->contactBatch->delete($chunk));
        }
    }

    #[Test]
    public function testMassContactUpdateReportsOperatingGrowth(): void
    {
        $count = (int)($_ENV['INTEGRATION_TEST_OPERATING_CONTACTS_COUNT'] ?? 100);
        $rounds = (int)($_ENV['INTEGRATION_TEST_OPERATING_UPDATE_ROUNDS'] ?? 1);

        // parallel runs take disjoint slices of the same contacts
        $shard = (int)($_ENV['INTEGRATION_TEST_OPERATING_SHARD'] ?? 0);
        $shards = (int)($_ENV['INTEGRATION_TEST_OPERATING_SHARDS'] ?? 1);
        $existingIds = [];
        foreach ($this->contactBatch->list(['ID' => 'ASC'], ['%NAME' => self::NAME_PREFIX], ['ID'], $count * $shards) as $contactItemResult) {
            $existingIds[] = (int)$contactItemResult->ID;
        }

        $this->contactIds = array_slice($existingIds, $shard * $count, $count);

        $contacts = [];
        for ($i = count($this->contactIds); $i < $count; $i++) {
            $contacts[] = ['NAME' => sprintf('%s%d-%d', self::NAME_PREFIX, time(), $i)];
        }

        foreach ($this->contactBatch->add($contacts) as $addedItemBatchResult) {
            $this->contactIds[] = $addedItemBatchResult->getId();
        }

        $this->events = [];
        $startedAt = microtime(true);
        $updated = 0;
        $limitException = null;
        try {
            for ($round = 1; $round <= $rounds; $round++) {
                $items = [];
                foreach ($this->contactIds as $contactId) {
                    $items[$contactId] = [
                        'fields' => [
                            'POST' => sprintf('round %d', $round),
                            'COMMENTS' => sprintf('operating limit update round %d at %s', $round, date(DATE_ATOM)),
                            'PHONE' => [['VALUE' => sprintf('+7999%07d', $contactId % 10_000_000), 'VALUE_TYPE' => 'WORK']],
                            'EMAIL' => [['VALUE' => sprintf('operating-%d-%d@example.com', $contactId, $round), 'VALUE_TYPE' => 'WORK']],
                        ],
                    ];
                }

                foreach ($this->contactBatch->update($items) as $ignored) {
                    $updated++;
                }
            }
        } catch (OperationTimeLimitExceededException $operationTimeLimitExceededException) {
            $limitException = $operationTimeLimitExceededException;
        }

        $elapsed = microtime(true) - $startedAt;

        $maxThreshold = [];
        foreach ($this->events as $event) {
            $maxThreshold[$event->getApiMethod()] = max($maxThreshold[$event->getApiMethod()] ?? 0, $event->getThresholdPercent());
        }

        $report = [sprintf(
            'contacts: %d, rounds: %d, updated: %d, wall time: %.1f s, threshold events: %d, sleeps: %d (%d s total)',
            count($this->contactIds),
            $rounds,
            $updated,
            $elapsed,
            count($this->events),
            count($this->sleeps),
            array_sum($this->sleeps)
        )];
        foreach ($maxThreshold as $apiMethod => $threshold) {
            $report[] = sprintf('  max threshold %s: %d%%', $apiMethod, $threshold);
        }

        $last = $this->events === [] ? null : $this->events[array_key_last($this->events)];
        if ($last instanceof OperatingThresholdCrossedEvent) {
            $report[] = sprintf(
                '  last event %s: %d%% operating=%.2f s reset_at=%s',
                $last->getApiMethod(),
                $last->getThresholdPercent(),
                $last->getOperating(),
                $last->getOperatingResetAt()?->format('H:i:s') ?? 'null'
            );
        }

        if ($limitException instanceof OperationTimeLimitExceededException) {
            $report[] = sprintf(
                'OPERATION_TIME_LIMIT after %d updates: method=%s command=%s reset_at=%s message=%s',
                $updated,
                $limitException->apiMethod ?? 'null',
                $limitException->batchCommandId ?? 'null',
                $limitException->operatingResetAt ?? 'null',
                $limitException->getMessage()
            );
        }

        fwrite(STDOUT, PHP_EOL . implode(PHP_EOL, $report) . PHP_EOL);

        $this->assertNull($limitException, 'operating time limit exceeded before all contacts were updated');
        $this->assertSame(count($this->contactIds) * $rounds, $updated);
    }
}
