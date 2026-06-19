<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\MailService\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\MailService\Service\Batch;
use Bitrix24\SDK\Services\MailService\Service\MailService;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private MailService $mailService;

    #[\Override]
    protected function setUp(): void
    {
        $this->mailService = Factory::getServiceBuilder()->getMailServiceScope()->mailService();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('batch add creates multiple mail services')]
    public function testBatchAdd(): void
    {
        $items = [];
        for ($i = 1; $i <= 3; $i++) {
            $items[] = [
                'NAME' => 'SDK Batch Test MailService ' . $i,
                'ACTIVE' => 'Y',
                'SERVER' => 'imap.example' . $i . '.com',
                'PORT' => 993,
                'ENCRYPTION' => 'Y',
            ];
        }

        $ids = [];
        $cnt = 0;
        foreach ($this->mailService->batch->add($items) as $result) {
            $cnt++;
            $ids[] = $result->getId();
        }

        self::assertSame(count($items), $cnt);

        // cleanup
        foreach ($this->mailService->batch->delete($ids) as $deleteResult) {
            // iterate to trigger execution
        }
    }

    /**
     * @throws BaseException
     */
    #[TestDox('batch delete removes multiple mail services')]
    public function testBatchDelete(): void
    {
        $items = [];
        for ($i = 1; $i <= 3; $i++) {
            $items[] = [
                'NAME' => 'SDK Batch Delete Test ' . $i,
                'ACTIVE' => 'Y',
                'ENCRYPTION' => 'N',
            ];
        }

        $ids = [];
        foreach ($this->mailService->batch->add($items) as $result) {
            $ids[] = $result->getId();
        }

        $cnt = 0;
        foreach ($this->mailService->batch->delete($ids) as $deleteResult) {
            $cnt++;
            self::assertTrue($deleteResult->isSuccess());
        }

        self::assertSame(count($items), $cnt);
    }

    /**
     * @throws BaseException
     */
    #[TestDox('batch update modifies multiple mail services')]
    public function testBatchUpdate(): void
    {
        $items = [];
        for ($i = 1; $i <= 3; $i++) {
            $items[] = [
                'NAME' => 'SDK Batch Update Test ' . $i,
                'ACTIVE' => 'Y',
                'ENCRYPTION' => 'N',
            ];
        }

        $ids = [];
        foreach ($this->mailService->batch->add($items) as $result) {
            $ids[] = $result->getId();
        }

        $updates = [];
        foreach ($ids as $id) {
            $updates[$id] = [
                'NAME' => 'SDK Batch Updated ' . $id,
            ];
        }

        $cnt = 0;
        foreach ($this->mailService->batch->update($updates) as $updateResult) {
            $cnt++;
            self::assertTrue($updateResult->isSuccess());
        }

        self::assertSame(count($updates), $cnt);

        // cleanup
        foreach ($this->mailService->batch->delete($ids) as $deleteResult) {
            // iterate to trigger execution
        }
    }
}
