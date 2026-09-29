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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\StatusLang\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sale\Status\Service\Status;
use Bitrix24\SDK\Services\Sale\StatusLang\Service\Batch;
use Bitrix24\SDK\Services\Sale\StatusLang\Service\StatusLang;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
#[CoversClass(\Bitrix24\SDK\Services\Sale\StatusLang\Batch::class)]
class BatchTest extends TestCase
{
    private StatusLang $statusLangService;

    private Status $statusService;

    private ?string $statusId = null;

    private string $statusLanguageId;

    /**
     * @var string[]
     */
    private array $languageIds = [];

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $saleServiceBuilder = Fabric::getServiceBuilder()->getSaleScope();
        $this->statusLangService = $saleServiceBuilder->statusLang();
        $this->statusService = $saleServiceBuilder->status();

        foreach ($this->statusLangService->getListLangs()->getLanguages() as $language) {
            $this->languageIds[] = (string)$language['lid'];
        }

        if (count($this->languageIds) < 2) {
            $this->markTestSkipped('At least two languages are required for testing');
        }

        $existingIds = [];
        foreach ($this->statusService->list(['id'])->getStatuses() as $statusItemResult) {
            $existingIds[] = (string)$statusItemResult->id;
        }

        // sale.status.delete does not delete status localizations, so skip identifiers with localizations left
        foreach (range('A', 'Z') as $letter) {
            $candidateId = 'Q' . $letter;
            if (!in_array($candidateId, $existingIds, true)
                && $this->statusLangService->list([], ['statusId' => $candidateId])->getStatusLangs() === []) {
                $this->statusId = $candidateId;
                break;
            }
        }

        if ($this->statusId === null) {
            $this->markTestSkipped('No free status identifiers available for testing');
        }

        // the status is created with the first language, the other languages are added in batch mode
        $this->statusLanguageId = array_shift($this->languageIds);
        $this->statusService->add([
            'id' => $this->statusId,
            'type' => 'O',
            'sort' => 900,
            'notify' => 'N',
            'name' => [$this->statusLanguageId => 'Batch status ' . $this->statusId],
        ]);
    }

    #[\Override]
    protected function tearDown(): void
    {
        if ($this->statusId === null) {
            return;
        }

        $names = [$this->statusLanguageId => 'Batch status ' . $this->statusId];
        foreach ($this->languageIds as $languageId) {
            $names[$languageId] = 'Batch status ' . $languageId;
        }

        foreach ($names as $languageId => $name) {
            try {
                $this->statusLangService->deleteByFilter(['statusId' => $this->statusId, 'lid' => $languageId, 'name' => $name]);
            } catch (\Throwable) {
                // localization was not created
            }
        }

        try {
            $this->statusService->delete($this->statusId);
        } catch (\Throwable) {
            // ignore cleanup errors
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('Batch::add, Batch::list status localizations')]
    public function testAddList(): void
    {
        $items = [];
        foreach ($this->languageIds as $languageId) {
            $items[] = [
                'statusId' => $this->statusId,
                'lid' => $languageId,
                'name' => 'Batch status ' . $languageId,
                'description' => 'Batch description ' . $languageId,
            ];
        }

        $addedCount = 0;
        foreach ($this->statusLangService->batch->add($items) as $addedItemResult) {
            $this->assertTrue($addedItemResult->isSuccess());
            $addedCount++;
        }

        $this->assertSame(count($this->languageIds), $addedCount);

        $listed = [];
        foreach ($this->statusLangService->batch->list([], ['statusId' => $this->statusId]) as $item) {
            $listed[(string)$item->lid] = $item->name;
        }

        foreach ($this->languageIds as $languageId) {
            $this->assertArrayHasKey($languageId, $listed);
            $this->assertSame('Batch status ' . $languageId, $listed[$languageId]);
        }
    }
}
