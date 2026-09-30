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

namespace Bitrix24\SDK\Tests\Integration\Services\SonetGroup;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\CoreBuilder;
use Bitrix24\SDK\Core\Credentials\Credentials;
use Bitrix24\SDK\Core\Credentials\WebhookUrl;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Services\SonetGroup\Service\SonetGroup;
use Psr\Log\NullLogger;

trait SonetGroupFixture
{
    private SonetGroup $sonetGroupService;

    private CoreInterface $core;

    /** @var array<int, true> */
    private array $createdGroupIds = [];

    protected function setUp(): void
    {
        $nullLogger = new NullLogger();
        $this->core = (new CoreBuilder())->withLogger($nullLogger)->withCredentials(
            Credentials::createFromWebhook(new WebhookUrl($_ENV['BITRIX24_PHP_SDK_PLAYGROUND_WEBHOOK'] ?? $_ENV['BITRIX24_WEBHOOK']))
        )->build();
        $this->sonetGroupService = new SonetGroup($this->core, $nullLogger);
    }

    /** @param array<string, mixed> $fields */
    private function createTestGroup(array $fields = []): AddedItemResult
    {
        $result = $this->sonetGroupService->create(array_merge([
            'NAME' => 'SDK SonetGroup ' . bin2hex(random_bytes(8)),
            'OPENED' => 'N',
            'INITIATE_PERMS' => 'K',
            'SPAM_PERMS' => 'K',
        ], $fields, ['VISIBLE' => 'N']));
        $this->createdGroupIds[$result->getId()] = true;

        return $result;
    }

    private function deleteTestGroup(int $id): void
    {
        self::assertArrayHasKey($id, $this->createdGroupIds, 'Only this test instance may delete its own fixtures.');
        self::assertTrue($this->sonetGroupService->delete($id)->isSuccess());
        unset($this->createdGroupIds[$id]);
    }

    protected function tearDown(): void
    {
        $failedIds = [];
        foreach (array_keys($this->createdGroupIds) as $id) {
            try {
                $this->deleteTestGroup($id);
            } catch (\Throwable) {
                $failedIds[] = $id;
            }
        }

        self::assertSame([], $failedIds, 'Could not delete fixture group IDs: ' . implode(', ', $failedIds));
    }
}
