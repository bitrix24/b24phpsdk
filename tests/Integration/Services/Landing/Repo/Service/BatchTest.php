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

namespace Bitrix24\SDK\Tests\Integration\Services\Landing\Repo\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Landing\Repo\Result\RepoItemResult;
use Bitrix24\SDK\Services\Landing\Repo\Service\Batch;
use Bitrix24\SDK\Services\Landing\Repo\Service\Repo;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private Repo $repoService;

    /** @var string[] */
    private array $createdBlockCodes = [];

    #[\Override]
    protected function setUp(): void
    {
        $this->repoService = Fabric::getServiceBuilder()->getLandingScope()->repo();
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->createdBlockCodes as $createdBlockCode) {
            try {
                $this->repoService->unregister($createdBlockCode);
            } catch (\Exception) {
                // Ignore if block doesn't exist
            }
        }
    }

    /**
     * @return array<string, int> registered block ids keyed by block code
     * @throws BaseException
     */
    private function registerBlocks(int $count): array
    {
        $suffix = uniqid();
        $blocks = [];
        for ($i = 1; $i <= $count; $i++) {
            $blocks[] = [
                'code' => sprintf('sdk_batch_block_%d_%s', $i, $suffix),
                'fields' => [
                    'NAME' => sprintf('SDK Batch Block %d %s', $i, $suffix),
                    'DESCRIPTION' => 'SDK batch test block',
                    'SECTIONS' => 'text',
                    'PREVIEW' => 'https://example.com/preview.png',
                    'CONTENT' => '<div class="landing-block-node-text">SDK batch test block</div>',
                    'ACTIVE' => 'Y',
                ],
                'manifest' => [
                    'block' => ['name' => 'SDK Batch Block', 'section' => ['text']],
                    'nodes' => ['.landing-block-node-text' => ['name' => 'Text', 'type' => 'text']],
                ],
            ];
        }

        $registered = [];
        foreach ($this->repoService->batch->register($blocks) as $cnt => $addedItemBatchResult) {
            $registered[$blocks[$cnt]['code']] = $addedItemBatchResult->getId();
        }

        $this->createdBlockCodes = array_merge($this->createdBlockCodes, array_keys($registered));

        return $registered;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('batch register adds multiple blocks to the repository')]
    public function testBatchRegister(): void
    {
        $registered = $this->registerBlocks(3);

        self::assertCount(3, $registered);
        foreach ($registered as $id) {
            self::assertGreaterThan(0, $id);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('batch list returns registered blocks')]
    public function testBatchList(): void
    {
        $registered = $this->registerBlocks(3);
        $ids = array_values($registered);

        $foundIds = [];
        foreach ($this->repoService->batch->list(['ID', 'NAME'], ['ID' => $ids]) as $repoItemResult) {
            self::assertInstanceOf(RepoItemResult::class, $repoItemResult);
            $foundIds[] = (int)$repoItemResult->ID;
        }

        sort($foundIds);
        self::assertSame($ids, $foundIds);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('batch unregister removes multiple blocks from the repository')]
    public function testBatchUnregister(): void
    {
        $registered = $this->registerBlocks(3);
        $codes = array_keys($registered);

        $cnt = 0;
        foreach ($this->repoService->batch->unregister($codes) as $deletedItemBatchResult) {
            $cnt++;
            self::assertTrue($deletedItemBatchResult->isSuccess());
        }

        self::assertSame(count($codes), $cnt);
        self::assertSame([], $this->repoService->getList(['ID'], ['ID' => array_values($registered)])->getRepoItems());
        $this->createdBlockCodes = [];
    }

    /**
     * @throws BaseException
     */
    #[TestDox('batch register throws exception when required keys are missing')]
    public function testBatchRegisterWithoutRequiredKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);

        /** @phpstan-ignore-next-line intentionally invalid input */
        foreach ($this->repoService->batch->register([['fields' => ['NAME' => 'No code']]]) as $addedItemBatchResult) {
            // iterate to trigger execution
        }
    }
}
