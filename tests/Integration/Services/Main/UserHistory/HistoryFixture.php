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

namespace Bitrix24\SDK\Tests\Integration\Services\Main\UserHistory;

use Bitrix24\SDK\Core\Contracts\SortOrder;
use Bitrix24\SDK\Services\Main\MainServiceBuilder;
use Bitrix24\SDK\Services\Main\UserHistory\Result\UserHistoryItemResult;
use Bitrix24\SDK\Services\Main\UserHistory\Service\UserHistorySelectBuilder;
use Bitrix24\SDK\Services\Main\UserHistoryChange\Result\UserHistoryChangeItemResult;
use Bitrix24\SDK\Services\Main\UserHistoryChange\Service\UserHistoryChangeSelectBuilder;
use Bitrix24\SDK\Services\ServiceBuilderFactory;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;

trait HistoryFixture
{
    private ?MainServiceBuilder $historyMainScope = null;
    private ?int $historyUserId = null;

    protected function mainScope(): MainServiceBuilder
    {
        // User history can contain personal data; never log live payloads from these tests.
        return $this->historyMainScope ??= (new ServiceBuilderFactory(new EventDispatcher(), new NullLogger()))
            ->initFromWebhook($_ENV['BITRIX24_PHP_SDK_PLAYGROUND_WEBHOOK'] ?? $_ENV['BITRIX24_WEBHOOK'])
            ->getMainScope();
    }

    protected function userId(): int
    {
        return $this->historyUserId ??= (int)$this->mainScope()->main()->getCurrentUserProfile()->getUserProfile()->ID;
    }

    protected function historyItem(): UserHistoryItemResult
    {
        $items = $this->mainScope()->userHistory()->list(
            $this->userId(),
            (new UserHistorySelectBuilder())->allSystemFields(),
            order: ['id' => SortOrder::Descending],
            pagination: ['limit' => 1]
        )->getUserHistoryItems();
        if ($items === []) {
            $this->markTestSkipped('No user history is available for the current webhook user.');
        }

        return $items[0];
    }

    protected function historyChangeItem(): UserHistoryChangeItemResult
    {
        $history = $this->mainScope()->userHistory()->list(
            $this->userId(),
            ['id'],
            order: ['id' => SortOrder::Descending],
            pagination: ['limit' => 20]
        )->getUserHistoryItems();
        foreach ($history as $item) {
            $changes = $this->mainScope()->userHistoryChange()->list(
                (int)$item->id,
                (new UserHistoryChangeSelectBuilder())->allSystemFields(),
                pagination: ['limit' => 1]
            )->getUserHistoryChanges();
            if ($changes !== []) {
                return $changes[0];
            }
        }

        $this->markTestSkipped('No field changes found in the latest 20 history entries for the current webhook user.');
    }
}
